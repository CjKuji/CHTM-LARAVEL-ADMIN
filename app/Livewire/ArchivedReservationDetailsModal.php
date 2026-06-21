<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ArchivedBooking;
use App\Models\User;
use Livewire\Attributes\On;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Encryption\Aes256GcmEncrypter;

class ArchivedReservationDetailsModal extends Component
{
    public ?string $bookingId = null; 
    public ?ArchivedBooking $booking = null;

    // Staff attribute name containers
    public ?string $approvedByName = null;
    public ?string $rejectedByName = null;
    public ?string $checkedInByName = null;
    public ?string $checkedOutByName = null;

    /**
     * Hydrates the archived booking record and maps operational staff identifier values to real names.
     */
    #[On('view-archive-details')]
    public function loadArchiveBooking(string $id): void
    {
        $this->bookingId = $id;
        $this->booking = ArchivedBooking::with('user')->find($id);

        if ($this->booking) {
            
            // --- JIT CUSTOM ARCHITECTURE SELF-HEALING ENCRYPTION STEP ---
            if ($this->booking->user) {
                $user = $this->booking->user;
                $userId = (string) $user->getKey(); // ⚡ FORCE STRIP TO RAW UUID STRING REPRESENTATION
                
                // Pull directly what is sitting in the raw database field bypassing custom cast decryptions
                $dbRow = DB::table('users')->where('id', $userId)->first();
                $rawDbEmail = $dbRow ? $dbRow->email : null;

                if (is_string($rawDbEmail)) {
                    $rawDbEmail = trim($rawDbEmail);
                    
                    // Determine if data requires structural modifications
                    // If it contains an '@' symbol, it is raw plaintext and needs immediate healing
                    if (str_contains($rawDbEmail, '@')) {
                        try {
                            // 1. Setup Postgres dummy routing functions to prevent environment syntax crashes
                            DB::unprepared("CREATE OR REPLACE FUNCTION public.digest(text, text) RETURNS bytea AS $$ SELECT '\\x00'::bytea; $$ LANGUAGE sql IMMUTABLE STRICT;");

                            $plainEmail = strtolower($rawDbEmail);
                            $encrypter = Aes256GcmEncrypter::fromConfiguration();
                            
                            $encryptedValue = $encrypter->encrypt($plainEmail);
                            $hashedValue = hash('sha256', $plainEmail);

                            $payloads = [
                                'email'      => $encryptedValue,
                                'email_hash' => $hashedValue,
                                'updated_at' => now(),
                            ];

                            // Safely catch raw text first names / last names if manual entries left them naked
                            if (isset($dbRow->fname) && !str_starts_with($dbRow->fname, 'eyJ')) {
                                $payloads['fname'] = $encrypter->encrypt($dbRow->fname);
                            }
                            if (isset($dbRow->lname) && !str_starts_with($dbRow->lname, 'eyJ')) {
                                $payloads['lname'] = $encrypter->encrypt($dbRow->lname);
                            }

                            // 2. Directly update the database table down to the engine level
                            DB::table('users')->where('id', $userId)->update($payloads);

                            // 3. Sync memory variables instantly so the rendered modal gets clean information
                            $user->setAttribute('email', $plainEmail);
                            $user->setAttribute('email_hash', $hashedValue);
                            if (isset($payloads['fname'])) $user->setAttribute('fname', $dbRow->fname);
                            if (isset($payloads['lname'])) $user->setAttribute('lname', $dbRow->lname);
                            
                            $user->syncOriginal();

                        } catch (\Throwable $e) {
                            Log::error("JIT Archive Modal Healing failed for User ID {$userId}: " . $e->getMessage());
                        } finally {
                            // 4. Tear down the database placeholder helper to keep DB state isolated
                            DB::unprepared("DROP FUNCTION IF EXISTS public.digest(text, text);");
                        }
                    }
                }
            }
            // -------------------------------------------------------------

            $this->approvedByName = $this->getStaffName($this->booking->approved_by);
            $this->rejectedByName = $this->getStaffName($this->booking->rejected_by);
            $this->checkedInByName = $this->getStaffName($this->booking->checked_in_by);
            $this->checkedOutByName = $this->getStaffName($this->booking->checked_out_by);
        }
    }

    /**
     * Helper to resolve a staff reference signature (ID or UUID) to a viewable user name string.
     */
    private function getStaffName(string|int|null $staffId): ?string
    {
        if (!$staffId) {
            return null;
        }

        $user = User::find((string)$staffId);
        
        if ($user) {
            try {
                $reflection = new \ReflectionClass($user);
                if ($reflection->hasMethod('fullName')) {
                    return $user->fullName();
                }
            } catch (\Exception $e) {
                // Fallback gracefully
            }

            return $user->full_name ?? $user->name ?? "Staff Reference #{$staffId}";
        }

        return "Staff Reference #{$staffId}";
    }

    /**
     * Clears out all backend data tracks when the window drops out of focus.
     */
    public function resetModalState(): void
    {
        $this->reset([
            'bookingId', 
            'booking', 
            'approvedByName', 
            'rejectedByName', 
            'checkedInByName', 
            'checkedOutByName'
        ]);
    }

    public function render(): View
    {
        return view('modals.archived-reservation-modal');
    }
}