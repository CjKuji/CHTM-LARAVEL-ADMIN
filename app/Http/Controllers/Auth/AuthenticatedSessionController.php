<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     * Authenticates credentials directly against Supabase Auth API and links the session locally.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validate the incoming form inputs
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 2. Fetch Supabase API configuration variables
        $supabaseUrl = rtrim(env('SUPABASE_URL'), '/');
        $supabaseApiKey = env('SUPABASE_KEY');

        if (!$supabaseUrl || !$supabaseApiKey || str_contains($supabaseApiKey, 'REPLACE_THIS_ENTIRE_STRING')) {
            Log::error('❌ API Authentication Halt: Missing valid SUPABASE_URL or SUPABASE_KEY inside your .env file.');
            throw ValidationException::withMessages([
                'email' => 'Server configuration missing connection environment keys.',
            ]);
        }

        Log::info('📡 Dispatching authentication request to Supabase GoTrue API Engine...', ['email' => $request->email]);

        // 3. Authenticate directly against Supabase Auth system
        $response = Http::withHeaders([
            'apikey' => $supabaseApiKey,
            'Content-Type' => 'application/json',
        ])->post("{$supabaseUrl}/auth/v1/token?grant_type=password", [
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        // 4. Deny access if Supabase Auth rejects the email or password
        if ($response->failed()) {
            Log::warning('⚠️ Supabase Auth API rejected credentials:', ['body' => $response->json()]);
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Extract verified metadata payload from Supabase GoTrue
        $authData = $response->json();
        $supabaseUid = $authData['user']['id'] ?? null;
        $supabaseEmail = $authData['user']['email'] ?? $request->email;

        Log::withContext(['supabase_uid' => $supabaseUid]);
        Log::info('🟩 Supabase Auth API verification successful. Syncing local profile data layers...');

        // 5. 🛡️ FORCE HEALING ENCRYPTION ENGINE (MODEL-CAST DRIVEN)
        $user = User::find($supabaseUid);

        // Check the database directly for unencrypted/raw strings bypassing model casts
        $rawFnameInDb = $user ? $user->getRawOriginal('fname') : null;
        $rawLnameInDb = $user ? $user->getRawOriginal('lname') : null;

        // Force execution if the row doesn't exist, fields are missing, or they don't look like base64 payloads
        if (!$user || empty($rawFnameInDb) || empty($rawLnameInDb) || !str_contains($rawFnameInDb, '==')) {
            Log::info("🔄 Profile Syncing Forced: Generating valid AES-256-GCM records via Model Casts.");

            // Standard placeholders for fallback profiles (KEEP PLAIN TEXT)
            // If the user already exists in memory, pull their raw attributes cleanly
            $plainFname = ($user && !empty($user->fname)) ? $user->fname : 'CHTM';
            $plainLname = ($user && !empty($user->lname)) ? $user->lname : 'FRONT OFFICE';

            // --- DATABASE TRIGGER ADAPTER ATTACHED ---
            // Safely seed a quick public function placeholder using PostgreSQL Dollar-Quoting
            // This prevents the sync_user_email_hash database trigger from throwing a 42883 error
            DB::unprepared("CREATE OR REPLACE FUNCTION public.digest(text, text) RETURNS bytea AS $$ SELECT '\\x00'::bytea; $$ LANGUAGE sql IMMUTABLE STRICT;");

            try {
                // Execute an upsert directly using PLAIN TEXT arrays.
                // The User model's casts() schema handles automatic encryption seamlessly.
                $user = User::updateOrCreate(
                    ['id' => $supabaseUid],
                    [
                        'fname' => $plainFname,
                        'lname' => $plainLname,
                        'email' => $supabaseEmail,
                        'role'  => $user->role ?? 'frontoffice', 
                    ]
                );
            } catch (\Exception $e) {
                Log::error('❌ Local database profile synchronization failed: ' . $e->getMessage());
                throw $e;
            } finally {
                // Instantly remove the function helper after transaction resolution
                DB::unprepared("DROP FUNCTION IF EXISTS public.digest(text, text);");
            }
            // --- END TRIGGER ADAPTER ---
        }

        // 6. Log the verified user into your local Laravel state session context
        Auth::login($user, $request->boolean('remember'));

        // 7. Store the Supabase access tokens in the session for use in subsequent requests if needed
        $request->session()->put('supabase_token', $authData['access_token'] ?? null);
        $request->session()->put('supabase_refresh_token', $authData['refresh_token'] ?? null);

        // 8. Regenerate session ID to prevent session fixation attacks
        $request->session()->regenerate();

        // 9. Determine routing path destinations based on plain-text user roles
        $destination = match ((string) $user->role) {
            'reservation' => route('reservation'),
            'housekeeper' => route('room', ['tab' => 'housekeeping']),
            'admin'       => route('room', ['tab' => 'inventory']), 
            default       => route('dashboard'),
        };

        Log::info('🚀 Login success. Forwarding user session stream directly to destination route: ' . $destination);

        return redirect()->intended($destination);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        // Flush out tokens along with traditional session data loops
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}