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
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Aes256GcmEncrypter::fromConfiguration()->decrypt((string) $value);
    }

    /**
     * @return array<string, string|null>
     */
   // Inside App\Casts\Aes256GcmEncrypted.php

public function set(Model $model, string $key, mixed $value, array $attributes): array
{
    if ($value === null || $value === '') {
        return [$key => null];
    }

    // FIXED: Instead of looking for generic "==", match true base64 payload characteristics securely
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