<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PasswordResetLinkController extends Controller
{
    public function create(): RedirectResponse
    {
        return redirect()->route('login');
    }

    /**
     * Handle an incoming password reset link request (Bypassed for Supabase).
     */
    public function store(Request $_request): RedirectResponse
    {
        return redirect()->route('login');
    }
}