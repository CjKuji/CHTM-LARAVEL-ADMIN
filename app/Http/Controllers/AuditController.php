<?php

namespace App\Http\Controllers;

use App\Services\Audit\AuditService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function index(Request $request): View
    {
        $period = $request->string('period', 'monthly')->toString();
        $year = (int) $request->input('year', now()->year);
        
        // Resolved the critical January (Month 0) boundary crash vectors safely
        $currentMonth = now()->month;
        $defaultMonth = $currentMonth === 1 ? 12 : $currentMonth - 1;
        $defaultYear  = $currentMonth === 1 && ! $request->has('year') ? now()->year - 1 : $year;
        
        $month = $request->has('month') ? (int) $request->input('month') : $defaultMonth;
        $year = $defaultYear;
        
        $quarter = $request->has('quarter') ? (int) $request->input('quarter') : 1;
        $tab = $request->string('tab', 'sales')->toString();

        $range = $this->audit->getDateRange($period, $year, $month, $quarter);
        $archived = $this->audit->getArchivedInRange($range['from'], $range['to']);

        return view('audit.index', [
            'activeMenu'   => 'audit',
            'period'       => $period,
            'year'         => $year,
            'month'        => $month,
            'quarter'      => $quarter,
            'tab'          => $tab,
            'dateLabel'    => $range['label'],
            'summary'      => $this->audit->getSummary($range['from'], $range['to']),
            'guestStats'   => $this->audit->getGuestStats($range['from'], $range['to']),
            'auditLogs'    => $this->audit->getAuditLogs($range['from'], $range['to']),
            'archivedRows' => $archived,
        ]);
    }
}