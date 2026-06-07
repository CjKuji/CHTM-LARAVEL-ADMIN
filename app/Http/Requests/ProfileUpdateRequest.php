<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        $inputEmail = $this->string('email')->toString();
        
        // Convert input to hash so we can accurately check our Supabase index column
        $computedHash = User::hashEmail($inputEmail);

        return [
            'fname' => ['required', 'string', 'max:255'],
            'lname' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'lowercase', 
                'email', 
                'max:255',
                // Prefixed unused variables with underscores to clear IDE warnings
                function ($_attribute, $_value, $fail) use ($computedHash, $user) {
                    $exists = DB::table('public.users')
                        ->where('email_hash', $computedHash)
                        ->where('id', '!=', $user?->id)
                        ->exists();

                    if ($exists) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
        ];
    }
}