<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewPasswordController extends Controller
{
    public function create(Request $_request): RedirectResponse
    {
        return redirect()->route('login');
    }

    /**
     * Handle an incoming new password request (Bypassed for Supabase).
     */
    public function store(Request $_request): RedirectResponse
    {
        return redirect()->route('login');
    }
}