@extends('layouts.app')

@section('title', 'Admin Login · CHTM-RRS')

@section('content')
{{-- Isolated layout wrapper override block to guarantee full screen presentation --}}
<div class="fixed inset-0 z-[9999] bg-white overflow-y-auto">
    <div class="flex min-h-screen">
        {{-- Left Presentation Splash Panel --}}
        <div class="relative hidden lg:flex lg:w-1/2 bg-gradient-to-br from-slate-700 via-slate-600 to-slate-500 overflow-hidden">
            @if (file_exists(public_path('loginchtmbg.jpg')))
                <div class="absolute inset-0 bg-[url('{{ asset('loginchtmbg.jpg') }}')] bg-cover bg-center mix-blend-overlay opacity-40 scale-105"></div>
            @endif
            
            <div class="relative z-10 flex w-full flex-col items-center justify-center p-12 text-white">
                <div class="mb-8 flex items-center justify-center gap-4 w-full">
                    <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white p-1.5 shadow-md">
                        @if (file_exists(public_path('chtmlogo.png')))
                            <img src="{{ asset('chtmlogo.png') }}" alt="CHTM Logo" class="h-full w-full object-contain">
                        @else
                            <span class="text-lg font-bold text-pink-600">CHTM</span>
                        @endif
                    </div>

                    <div class="text-center px-2">
                        <h1 class="text-5xl font-black tracking-tight font-serif text-[#FF0080] drop-shadow-sm">CHTM-RRS</h1>
                        <p class="mt-1 text-xs font-bold tracking-widest uppercase text-gray-200">Room Reservation System</p>
                    </div>

                    <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white p-1.5 shadow-md">
                        @if (file_exists(public_path('gclogo.png')))
                            <img src="{{ asset('gclogo.png') }}" alt="GC Logo" class="h-full w-full object-contain">
                        @else
                            <span class="text-lg font-bold text-teal-700">GC</span>
                        @endif
                    </div>
                </div>

                <div class="max-w-md text-center mt-6">
                    <p class="text-lg font-medium italic leading-relaxed text-gray-100" style="text-shadow: 0 2px 4px rgba(0,0,0,0.35);">
                        "Enhancing service excellence through the College of Hospitality and Tourism Management"
                    </p>
                    <div class="mt-6 h-1 w-24 mx-auto bg-pink-600 rounded-full"></div>
                    <p class="mt-3 text-xs font-semibold uppercase tracking-widest text-pink-200">CHTM Department</p>
                </div>
            </div>
        </div>

        {{-- Right Interactive Authentication Input Panel --}}
        <div class="flex flex-1 items-center justify-center bg-white p-6 sm:p-12">
            <div class="w-full max-w-lg px-4 sm:px-8">
                <div class="mb-10">
                    <h2 class="mb-2 font-serif text-5xl font-light text-[#3D5A4C]">Admin Login</h2>
                    <div class="mb-4 h-1 w-48 bg-pink-600 rounded-full"></div>
                    <p class="text-sm font-medium text-gray-500">Sign in to securely access the management systems.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-5 flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 animate-pulse">
                        <i class="ti ti-alert-triangle text-base shrink-0"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-5 flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        <i class="ti ti-circle-check text-base shrink-0"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block font-serif text-sm font-semibold text-[#3D5A4C]">Admin Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                               class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-base text-gray-900 placeholder-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-pink-500 transition shadow-inner">
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block font-serif text-sm font-semibold text-[#3D5A4C]">Security Password</label>
                        <div class="relative w-full">
                            <input id="password" name="password" type="password" required
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50/50 pl-4 pr-12 py-3 text-base text-gray-900 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-pink-500 transition shadow-inner">
                            
                            {{-- Interactive Inline Password Visibility Controller Toggle --}}
                            <button type="button" 
                                    onclick="const input = document.getElementById('password'); const icon = this.querySelector('i'); if(input.type === 'password') { input.type = 'text'; icon.classList.remove('ti-eye'); icon.classList.add('ti-eye-off'); } else { input.type = 'password'; icon.classList.remove('ti-eye-off'); icon.classList.add('ti-eye'); }"
                                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-600 focus:outline-none transition">
                                <i class="ti ti-eye text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                            <span>Remember my session</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-[#3D5A4C] hover:bg-[#2d4339] active:scale-[0.99] mt-4 px-4 py-3.5 text-base font-semibold text-white shadow-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#3D5A4C] flex items-center justify-center gap-2">
                        <span>Sign into System</span>
                        <i class="ti ti-arrow-right text-base"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection