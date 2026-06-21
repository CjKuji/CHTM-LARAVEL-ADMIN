<?php

namespace App\Casts;

use App\Services\Encryption\Aes256GcmEncrypter;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<string|null, string|null>
 */
class Aes256GcmEncrypted implements CastsAttributes
{
    /**
     * Cast the given value from database storage back to application state.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $stringValue = (string) $value;

        // 1. Check if it's a standard Laravel framework JSON-serialized envelope (Row 2 pattern)
        if (str_starts_with($stringValue, 'ey')) {
            try {
                $decodedJson = base64_decode($stringValue, true);
                if ($decodedJson !== false) {
                    $envelope = json_decode($decodedJson, true);
                    
                    if (is_array($envelope) && isset($envelope['iv'], $envelope['value'])) {
                        $iv = base64_decode((string)$envelope['iv'], true);
                        $ciphertext = base64_decode((string)$envelope['value'], true);
                        $tag = isset($envelope['tag']) ? base64_decode((string)$envelope['tag'], true) : false;

                        // Retrieve and unpack base64: prepended APP_KEY configurations securely
                        $appKey = config('app.key');
                        if (str_starts_with($appKey, 'base64:')) {
                            $appKey = base64_decode(substr($appKey, 7));
                        }

                        // Try standard framework AES-256-GCM format decryption
                        if ($tag !== false && $iv !== false && $ciphertext !== false) {
                            $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $appKey, OPENSSL_RAW_DATA, $iv, $tag);
                            if ($plaintext !== false) {
                                return $plaintext;
                            }
                        }

                        // Fallback: Try standard framework AES-256-CBC format decryption
                        if ($iv !== false && $ciphertext !== false) {
                            $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', $appKey, OPENSSL_RAW_DATA, $iv);
                            if ($plaintext !== false) {
                                return $plaintext;
                            }
                        }
                    }
                }
            } catch (\Throwable) {
                // Fail silently and let the custom unpacking service handle it below
            }
        }

        // 2. Default Fallback: Process using custom unpacking binary architecture (Row 1 pattern)
        try {
            return Aes256GcmEncrypter::fromConfiguration()->decrypt($stringValue);
        } catch (\Throwable) {
            // Return original raw string if decryption fails completely
            return $stringValue;
        }
    }

    /**
     * Prepare the given value for database storage.
     * * @return array<string, string|null>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null || $value === '') {
            return [$key => null];
        }

        // Match true base64 payload characteristics securely
        if (is_string($value) && preg_match('/^[a-zA-Z0-9\/+]+={0,2}$/', $value) && strlen($value) % 4 === 0) {
            // Double check if it decrypts successfully. If it does, it's already encrypted!
            try {
                Aes256GcmEncrypter::fromConfiguration()->decrypt($value);
                return [$key => $value];
            } catch (\Throwable) {
                // Processing failed; it's plain text that just looked like base64
            }
        }

        $encrypted = Aes256GcmEncrypter::fromConfiguration()->encrypt((string) $value);

        return [$key => $encrypted];
    }
}