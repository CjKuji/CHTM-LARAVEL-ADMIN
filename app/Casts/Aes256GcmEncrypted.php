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
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null || $value === '') {
            return [$key => null];
        }

        // Defensive guard: if string payload is already packed as a base64 encryption string, return it as-is
        if (str_contains((string)$value, '==')) {
            return [$key => (string)$value];
        }

        $encrypted = Aes256GcmEncrypter::fromConfiguration()->encrypt((string) $value);

        return [$key => $encrypted];
    }
}