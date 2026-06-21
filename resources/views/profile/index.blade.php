@extends('layouts.app')

@section('title', 'My Profile')
@section('topbar_title', 'My Profile')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        {{-- Profile Header Identification Segment --}}
        <div class="mb-6 flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-pink-400 to-pink-500 text-xl font-bold text-white shadow-sm">
                {{ mb_strtoupper(mb_substr($user->fname, 0, 1) . mb_substr($user->lname, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $user->fullName() }}</h1>
                <p class="text-sm font-medium text-gray-500 capitalize tracking-wide">{{ str_replace('_', ' ', $user->role) }}</p>
            </div>
        </div>

        {{-- Profile Modification Form --}}
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-gray-500">First Name</label>
                <input type="text" name="fname" value="{{ old('fname', $user->fname) }}" 
                       class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm focus:border-pink-500 focus:bg-white focus:outline-none transition-colors" required>
            </div>

            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Last Name</label>
                <input type="text" name="lname" value="{{ old('lname', $user->lname) }}" 
                       class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm focus:border-pink-500 focus:bg-white focus:outline-none transition-colors" required>
            </div>

            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                       class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm focus:border-pink-500 focus:bg-white focus:outline-none transition-colors" required>
            </div>

            <div class="pt-2">
                <button type="submit" class="rounded-xl bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-pink-600/10 hover:bg-pink-700 active:scale-95 transition-all">
                    Save Profile Updates
                </button>
            </div>
        </form>
    </div>
</div>
@endsection