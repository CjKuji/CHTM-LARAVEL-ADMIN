<?php

namespace App\Services\Encryption;

use InvalidArgumentException;
use RuntimeException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

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
     * Decrypts data seamlessly, handling both custom binary formats 
     * and Laravel native JSON serialized framework wrappers.
     */
    public function decrypt(string $payload): ?string
    {
        if (empty($payload)) {
            return null;
        }

        // --- STRATEGY A: Handle Laravel Framework Native Envelopes (Row 2 / Google OAuth style) ---
        if (str_starts_with($payload, 'ey')) {
            try {
                $decodedJson = base64_decode($payload, true);
                if ($decodedJson !== false) {
                    $envelope = json_decode($decodedJson, true);
                    
                    if (is_array($envelope) && isset($envelope['iv'], $envelope['value'])) {
                        $iv = base64_decode((string)$envelope['iv'], true);
                        $ciphertext = base64_decode((string)$envelope['value'], true);
                        $tag = isset($envelope['tag']) ? base64_decode((string)$envelope['tag'], true) : false;

                        $appKey = self::deriveKeyFromAppKey();

                        // Try decryption using framework AES-256-GCM format
                        if ($tag !== false && $iv !== false && $ciphertext !== false) {
                            $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $appKey, OPENSSL_RAW_DATA, $iv, $tag);
                            if ($plaintext !== false) {
                                return $plaintext;
                            }
                        }

                        // Try decryption using framework AES-256-CBC format fallback
                        if ($iv !== false && $ciphertext !== false) {
                            $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', $appKey, OPENSSL_RAW_DATA, $iv);
                            if ($plaintext !== false) {
                                return $plaintext;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Fall down cleanly to Strategy B on fallback exception triggers
            }
        }

        // --- STRATEGY B: Clean execution of your original packed continuous binary logic (Row 1 style) ---
        try {
            $data = base64_decode($payload, true);
            if ($data === false || strlen($data) < (self::IV_LENGTH + self::TAG_LENGTH)) {
                return $payload;
            }

            $iv = substr($data, 0, self::IV_LENGTH);
            $tag = substr($data, self::IV_LENGTH, self::TAG_LENGTH);
            $ciphertext = substr($data, self::IV_LENGTH + self::TAG_LENGTH);

            $plaintext = openssl_decrypt(
                $ciphertext,
                self::CIPHER,
                $this->key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );

            return $plaintext !== false ? $plaintext : $payload;
        } catch (\Throwable $e) {
            return $payload;
        }
    }
}