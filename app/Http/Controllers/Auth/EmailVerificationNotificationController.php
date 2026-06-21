<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification (Bypassed for Supabase).
     */
    public function store(Request $_request): RedirectResponse
    {
        return redirect()->route('dashboard');
    }
}