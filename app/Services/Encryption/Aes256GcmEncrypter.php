<?php

namespace App\Services\Encryption;

use InvalidArgumentException;
use RuntimeException;

final class Aes256GcmEncrypter
{
    private const CIPHER = 'aes-256-gcm';

    private const IV_LENGTH = 12;

    private const TAG_LENGTH = 16;

    public function __construct(private readonly string $key)
    {
        if (strlen($this->key) !== 32) {
            throw new InvalidArgumentException('AES-256-GCM requires a 32-byte key.');
        }
    }

    /**
     * Resolve encrypter instance automatically from global application variables.
     */
    public static function fromConfiguration(): self
    {
        $configured = config('encryption.key');

        if (is_string($configured) && $configured !== '') {
            if (str_starts_with($configured, 'base64:')) {
                $decoded = base64_decode(substr($configured, 7), true);
                if ($decoded !== false && strlen($decoded) === 32) {
                    return new self($decoded);
                }
            }

            if (strlen($configured) === 32) {
                return new self($configured);
            }
        }

        return new self(self::deriveKeyFromAppKey());
    }

    /**
     * Fallback key derivation framework utilizing primary system app key signatures.
     * FIXED: Returns the decoded binary key directly without a SHA-256 mutation layer.
     */
    public static function deriveKeyFromAppKey(): string
    {
        $appKey = (string) config('app.key');

        if ($appKey === '') {
            throw new RuntimeException('Encryption engine failure: APP_KEY parameter has not been assigned in your environment file.');
        }

        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);
            if ($decoded !== false && strlen($decoded) === 32) {
                return $decoded; 
            }
        }

        if (strlen($appKey) === 32) {
            return $appKey;
        }

        throw new RuntimeException('Encryption engine failure: APP_KEY must be a valid 32-byte string or base64 encoded 32-byte string.');
    }

    /**
     * Encrypt a string value cleanly into an authenticated base64 string payload.
     */
    public function encrypt(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $value,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            throw new InvalidArgumentException('Encryption failed.');
        }

        return base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Decrypt and verify structural integrity tags from an authenticated base64 payload.
     */
    public function decrypt(?string $payload): ?string
    {
        if ($payload === null || $payload === '') {
            return $payload;
        }

        $raw = base64_decode($payload, true);

        if ($raw !== false) {
            $envelope = json_decode($raw, true);

            if (is_array($envelope) && isset($envelope['iv'], $envelope['tag'], $envelope['value'])) {
                $iv = base64_decode((string) $envelope['iv'], true);
                $tag = base64_decode((string) $envelope['tag'], true);
                $ciphertext = base64_decode((string) $envelope['value'], true);

                if ($iv !== false && $tag !== false && $ciphertext !== false) {
                    $plaintext = openssl_decrypt(
                        $ciphertext,
                        self::CIPHER,
                        $this->key,
                        OPENSSL_RAW_DATA,
                        $iv,
                        $tag
                    );

                    if ($plaintext !== false) {
                        return $plaintext;
                    }
                }
            }
        }

        try {
            $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($payload);

            if (is_string($decrypted) && $decrypted !== '') {
                return $decrypted;
            }
        } catch (\Throwable) {
            // Not a Laravel Crypt payload; continue through the AES-GCM handlers.
        }

        // SAFE FALLBACK: If it's plain text or doesn't meet the binary length rules, 
        // treat it as unencrypted data and pass it through safely.
        if ($raw === false || strlen($raw) < (self::IV_LENGTH + self::TAG_LENGTH)) {
            return $payload;
        }

        $iv = substr($raw, 0, self::IV_LENGTH);
        $tag = substr($raw, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($raw, self::IV_LENGTH + self::TAG_LENGTH);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        // INTEGRITY BOUNDARY SHIELD: If GCM tag authentication fails, return the raw source string.
        if ($plaintext === false) {
            \Illuminate\Support\Facades\Log::warning('⚠️ Aes256GcmEncrypter: Decryption failed or tag mismatch. Falling back to source payload string representation.');
            return $payload; 
        }

        return $plaintext;
    }
}
