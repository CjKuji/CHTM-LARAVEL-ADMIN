<?php

namespace App\Support;

use Carbon\CarbonInterface;

final class DashboardPresenter
{
    public static function fmtDate(?CarbonInterface $date): string
    {
        if (! $date) {
            return '—';
        }

        return $date->timezone('Asia/Manila')->format('M j, Y');
    }

    public static function fmtTime(?CarbonInterface $date): string
    {
        if (! $date) {
            return '—';
        }

        return $date->timezone('Asia/Manila')->format('g:i A');
    }

    public static function daysUntil(?CarbonInterface $date): string
    {
        if (! $date) {
            return '—';
        }

        $d = (int) now()->timezone('Asia/Manila')->diffInDays($date, false);

        if ($d <= 0) {
            return 'Today';
        }
        if ($d === 1) {
            return 'Tomorrow';
        }

        return "In {$d}d";
    }

    public static function daysUntilColor(?CarbonInterface $date): string
    {
        if (! $date) {
            return 'bg-gray-100 text-gray-500';
        }

        $d = (int) now()->timezone('Asia/Manila')->diffInDays($date, false);

        if ($d <= 0) {
            return 'bg-amber-50 text-amber-600';
        }
        if ($d === 1) {
            return 'bg-blue-50 text-blue-600';
        }

        return 'bg-gray-100 text-gray-500';
    }

    public static function actionLabel(string $action, string $entityType): string
    {
        $e = str_replace('_', ' ', $entityType ?: 'record');
        $key = strtoupper($action);

        // FIXED: Synced CREATE / INSERT variations to prevent technical jargon leaks on dashboard UI
        return match ($key) {
            'CREATE', 'INSERT' => "New {$e} created",
            'UPDATE'           => "{$e} updated",
            'DELETE'           => "{$e} deleted",
            'CHECK_IN'         => 'Guest checked in',
            'CHECK_OUT'        => 'Guest checked out',
            'APPROVED'         => 'Booking approved',
            'REJECTED'         => 'Booking rejected',
            'CANCELLED'        => 'Booking cancelled',
            default            => "{$action} on {$e}",
        };
    }

    /**
     * @return array{bg: string, text: string, icon: string}
     */
    public static function actionStyle(string $action): array
    {
        $a = strtoupper($action);

        if (in_array($a, ['CHECK_IN', 'APPROVED'], true)) {
            return ['bg' => 'bg-teal-50', 'text' => 'text-teal-600', 'icon' => 'ti-door-enter'];
        }
        if ($a === 'CHECK_OUT') {
            return ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'icon' => 'ti-arrow-right'];
        }
        if (in_array($a, ['DELETE', 'REJECTED', 'CANCELLED'], true)) {
            return ['bg' => 'bg-rose-50', 'text' => 'text-rose-500', 'icon' => 'ti-alert-circle'];
        }
        if (in_array($a, ['CREATE', 'INSERT'], true)) {
            return ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'icon' => 'ti-circle-dot'];
        }

        return ['bg' => 'bg-gray-100', 'text' => 'text-gray-500', 'icon' => 'ti-history'];
    }

    public static function occupancyBarColor(int $pct): string
    {
        if ($pct >= 90) {
            return 'bg-rose-500';
        }
        if ($pct >= 60) {
            return 'bg-amber-400';
        }

        return 'bg-teal-500';
    }

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            'super_admin' => 'Super Admin',
            'admin'       => 'Admin',
            'reservation' => 'Reservation',
            'frontoffice' => 'Front Office',
            'housekeeper' => 'Housekeeper',
            default       => 'Staff',
        };
    }
}