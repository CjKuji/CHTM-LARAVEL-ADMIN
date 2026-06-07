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

        if (User::query()->where('email_hash', User::hashEmail($email))->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['email' => 'Email already in use.'])->withInput();
        }

        $user->fill($request->validated());
        
        // FIXED: Enforce deterministic blind indexing updates whenever account emails change
        $user->email_hash = User::hashEmail($email);
        $user->save();

        return redirect()->route('profile')->with('status', 'Profile updated.');
    }
}