<?php

namespace App\Services\Room;

use App\Models\HousekeepingTask;
use App\Models\HousekeepingTaskItem;
use App\Models\HousekeepingTemplate;
use App\Models\HousekeepingTemplateItem;
use App\Models\Room;
use App\Models\RoomType;
use App\Support\AuditLogger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

final class RoomService
{
    public function getRooms(): Collection
    {
        return Room::query()
            ->with(['roomType.amenities', 'images', 'housekeepingTasks' => fn ($q) => $q->latest()])
            ->orderBy('room_number')
            ->get();
    }

    public function getRoomTypes(): Collection
    {
        return RoomType::query()->with('amenities')->orderBy('name')->get();
    }

    public function getTasks(): Collection
    {
        return HousekeepingTask::query()
            ->with(['room.roomType', 'items', 'assignedTo'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function createRoom(array $data): Room
    {
        return DB::transaction(function () use ($data) {
            $room = Room::query()->create([
                'room_type_id' => $data['room_type_id'],
                'room_number' => $data['room_number'],
                'floor' => $data['floor'] ?? null,
                'status' => $data['status'] ?? 'available',
            ]);

            AuditLogger::log('rooms', $room->id, 'CREATE', null, $data, Auth::user());

            return $room;
        });
    }

    public function updateRoom(Room $room, array $data): Room
    {
        return DB::transaction(function () use ($room, $data) {
            $oldData = $room->only(['room_type_id', 'room_number', 'floor', 'status']);
            
            $room->update([
                'room_type_id' => $data['room_type_id'] ?? $room->room_type_id,
                'room_number' => $data['room_number'] ?? $room->room_number,
                'floor' => $data['floor'] ?? $room->floor,
                'status' => $data['status'] ?? $room->status,
            ]);

            AuditLogger::log('rooms', $room->id, 'UPDATE', $oldData, $data, Auth::user());

            return $room->fresh(['roomType.amenities', 'images']);
        });
    }

    public function deleteRoom(Room $room): void
    {
        DB::transaction(function () use ($room) {
            $oldData = $room->only(['id', 'room_number']);
            $room->delete();

            AuditLogger::log('rooms', $room->id, 'DELETE', $oldData, null, Auth::user());
        });
    }

    public function setRoomStatus(int $roomId, string $status): void
    {
        Room::query()->whereKey($roomId)->update(['status' => $status]);
    }

    public function updateRoomFlags(Room $room, array $flags): Room
    {
        $room->update($flags);

        return $room->fresh(['roomType']);
    }

    public function setDoNotDisturb(Room $room, bool $enabled): Room
    {
        return $this->updateRoomFlags($room, [
            'status' => $enabled ? 'do_not_disturb' : 'available',
        ]);
    }

    public function requestMakeUpRoom(Room $room, int $roomTypeId): void
    {
        DB::transaction(function () use ($room, $roomTypeId) {
            $room->update(['make_up_room' => true, 'status' => 'needs_cleaning']);

            $template = HousekeepingTemplate::query()->where('room_type_id', $roomTypeId)->first();
            if (! $template) {
                return;
            }

            $task = HousekeepingTask::query()->create([
                'room_id' => $room->id,
                'template_id' => $template->id,
                'status' => 'pending',
                'note' => 'Make up room request',
            ]);

            foreach ($template->items as $item) {
                HousekeepingTaskItem::query()->create([
                    'task_id' => $task->id,
                    'item_name' => $item->item_name,
                    'quantity' => $item->default_quantity,
                    'is_done' => false,
                ]);
            }
        });
    }

    public function clearMakeUpRoom(Room $room): void
    {
        $room->update(['make_up_room' => false]);
    }

    public function startCleaning(HousekeepingTask $task): HousekeepingTask
    {
        return DB::transaction(function () use ($task) {
            $task->update(['status' => 'in_progress', 'started_at' => now()]);
            $this->setRoomStatus($task->room_id, 'cleaning');

            return $task->fresh(['room.roomType', 'items']);
        });
    }

    public function completeCleaning(HousekeepingTask $task): HousekeepingTask
    {
        return DB::transaction(function () use ($task) {
            $task->update(['status' => 'completed', 'completed_at' => now()]);
            
            if ($task->room) {
                $task->room->update(['make_up_room' => false]);
            }
            
            $this->setRoomStatus($task->room_id, 'inspected');

            return $task->fresh(['room.roomType', 'items']);
        });
    }

    public function toggleTaskItem(int $itemId, bool $done): void
    {
        HousekeepingTaskItem::query()->whereKey($itemId)->update(['is_done' => $done]);
    }

    public function getTemplates(): Collection
    {
        return HousekeepingTemplate::query()->with('items', 'roomType')->get();
    }

    public function saveTemplate(string $name, ?int $roomTypeId, array $itemNames): HousekeepingTemplate
    {
        return DB::transaction(function () use ($name, $roomTypeId, $itemNames) {
            $template = HousekeepingTemplate::query()->create([
                'name' => $name,
                'room_type_id' => $roomTypeId,
            ]);

            foreach ($itemNames as $itemName) {
                if (trim($itemName) === '') {
                    continue;
                }
                HousekeepingTemplateItem::query()->create([
                    'template_id' => $template->id,
                    'item_name' => trim($itemName),
                    'default_quantity' => 1,
                ]);
            }

            return $template->load('items');
        });
    }
}