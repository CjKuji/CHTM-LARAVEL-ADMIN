<?php

namespace App\Casts;

use App\Services\Encryption\Aes256GcmEncrypter;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<array<string, mixed>|null, array<string, mixed>|null>
 */
class Aes256GcmEncryptedJson implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $json = Aes256GcmEncrypter::fromConfiguration()->decrypt((string) $value);

        if ($json === null || $json === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @return array<string, string|null>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [$key => null];
        }

        $jsonPayload = json_encode($value, JSON_THROW_ON_ERROR);
        $encrypted = Aes256GcmEncrypter::fromConfiguration()->encrypt($jsonPayload);

        return [$key => $encrypted];
    }
}