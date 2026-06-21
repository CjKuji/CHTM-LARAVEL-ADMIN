<?php

namespace App\Http\Controllers;

use App\Models\ArchivedBooking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArchivedController extends Controller
{
    public function index(Request $request): View
    {
        // Protected against heavy N+1 query loops using eager loading arrays
        $archived = ArchivedBooking::query()
            ->with('user')
            ->orderByDesc('checked_out_at')
            ->get();

        $selectedId = $request->integer('booking');
        $selected = $selectedId ? $archived->firstWhere('id', $selectedId) : $archived->first();

        return view('archived.index', [
            'activeMenu'       => 'archived', 
            'archivedBookings' => $archived,
            'selectedBooking'  => $selected,
        ]);
    }
}