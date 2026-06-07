<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt (Bypassed for Supabase).
     */
    public function __invoke(Request $_request): RedirectResponse
    {
        return redirect()->route('dashboard');
    }
}