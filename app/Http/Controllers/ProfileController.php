<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.index', [
            'activeMenu' => 'profile',
            'user'       => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $email = $request->string('email')->toString();

        // Check if email hash exists elsewhere using pre-computed SHA-256 strings
        $emailHash = hash('sha256', $email);
        if (User::query()->where('email_hash', $emailHash)->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['email' => 'Email already in use.'])->withInput();
        }

        $user->fill($request->validated());
        
        // Enforce deterministic blind indexing updates whenever account emails change
        $user->email_hash = $emailHash;
        $user->save();

        return redirect()->route('profile')->with('status', 'Profile updated.');
    }
}