<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    /**
     * Update the user's password (Bypassed for Supabase).
     */
    public function update(Request $_request): RedirectResponse
    {
        return redirect()->route('dashboard');
    }
}