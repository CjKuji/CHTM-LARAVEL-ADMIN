<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConfirmablePasswordController extends Controller
{
    public function show(): RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    public function store(Request $_request): RedirectResponse
    {
        // Bypassing local database checks because passwords reside inside Supabase Auth
        return redirect()->route('dashboard');
    }
}