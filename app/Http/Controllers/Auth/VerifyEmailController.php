<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified (Bypassed for Supabase).
     */
    public function __invoke(EmailVerificationRequest $_request): RedirectResponse
    {
        return redirect()->route('dashboard');
    }
}