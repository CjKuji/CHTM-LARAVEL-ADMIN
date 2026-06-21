<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegisteredUserController extends Controller
{
    /**
     * Handle the registration display redirection.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('login');
    }

    /**
     * Intercept public registration submissions.
     */
    public function store(Request $_request): RedirectResponse
    {
        return redirect()
            ->route('login')
            ->with('status', 'Registration is managed by administrators.');
    }
}