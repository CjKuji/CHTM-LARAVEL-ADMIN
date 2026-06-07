<?php

namespace App\Services\Audit;

use App\Models\ArchivedBooking;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class AuditService
{
    public function getDateRange(string $period, int $year, ?int $month = null, ?int $quarter = null): array
    {
        // Handle JS 0-indexed month adjustments securely
        if ($period === 'daily' && $month !== null) {
            $normalizedMonth = $month + 1;
            $from = Carbon::create($year, $normalizedMonth, 1)->startOfDay();
            $to = (clone $from)->endOfMonth();

            return ['from' => $from, 'to' => $to, 'label' => $from->format('F Y')];
        }

        if ($period === 'monthly') {
            $from = Carbon::create($year, 1, 1)->startOfDay();
            $to = Carbon::create($year, 12, 31)->endOfDay();

            return ['from' => $from, 'to' => $to, 'label' => "Year {$year}"];
        }

        if ($period === 'quarterly' && $quarter !== null) {
            $startMonth = (($quarter - 1) * 3) + 1;
            $from = Carbon::create($year, $startMonth, 1)->startOfDay();
            $to = (clone $from)->addMonths(3)->subDay()->endOfDay();

            return ['from' => $from, 'to' => $to, 'label' => "Q{$quarter} {$year}"];
        }

        $from = Carbon::create($year, 1, 1)->startOfDay();
        $to = Carbon::create($year, 12, 31)->endOfDay();

        return ['from' => $from, 'to' => $to, 'label' => "Year {$year}"];
    }

    public function getSummary(Carbon $from, Carbon $to): array
    {
        // 1. Fetch scoped data within designated bounds
        $archived = ArchivedBooking::query()
            ->whereBetween('checked_out_at', [$from, $to])
            ->get();

        $active = Booking::query()
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $rooms = Room::count();
        $occupied = Room::where('status', 'occupied')->count();

        // 2. Financial calculation pipelines
        $totalRevenue = (float) $archived->sum('total_amount');
        $cash = (float) $archived->where('payment_method', 'Cash')->sum('total_amount');
        $gcash = (float) $archived->where('payment_method', 'Gcash')->sum('total_amount');

        // 3. Process top booking types elegantly
        $topType = $archived->groupBy('room_type_name')
            ->map->count()
            ->sortDesc()
            ->keys()
            ->first() ?? '—';

        $nights = $archived->sum(function (ArchivedBooking $b) {
            if (! $b->start_at || ! $b->end_at) {
                return 1;
            }
            return max(1, $b->start_at->diffInDays($b->end_at));
        });

        return [
            'total_revenue' => $totalRevenue,
            'total_bookings' => $archived->count() + $active->count(),
            'checked_out' => $archived->count(),
            'cancelled' => $active->where('status', 'cancelled')->count(),
            // FIXED: Scoped both active states to date parameters to fix calculation leaks
            'pending' => $active->where('status', 'pending')->count(),
            'approved' => $active->where('status', 'approved')->count(),
            'occupancy_rate' => $rooms > 0 ? (int) round(($occupied / $rooms) * 100) : 0,
            'avg_stay_nights' => $archived->count() > 0 ? round($nights / $archived->count(), 1) : 0,
            'top_room_type' => $topType,
            'cash_revenue' => $cash,
            'gcash_revenue' => $gcash,
        ];
    }

    public function getAuditLogs(Carbon $from, Carbon $to): Collection
    {
        return AuditLog::query()
            ->with('changer')
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->limit(100)
            ->get();
    }

    public function getArchivedInRange(Carbon $from, Carbon $to): Collection
    {
        return ArchivedBooking::query()
            ->whereBetween('checked_out_at', [$from, $to])
            ->orderByDesc('checked_out_at')
            ->get();
    }

    public function getGuestStats(Carbon $from, Carbon $to): array
    {
        $rows = $this->getArchivedInRange($from, $to);

        return [
            'total_guests' => (int) $rows->sum('guests'),
            'with_children' => $rows->where('has_child', true)->count(),
            'with_pwd' => $rows->where('has_pwd', true)->count(),
            'with_senior' => $rows->where('has_senior', true)->count(),
            'extra_beds' => (int) $rows->sum('extra_beds'),
        ];
    }
}