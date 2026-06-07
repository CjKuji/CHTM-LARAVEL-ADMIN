<?php

namespace Tests\Unit;

use App\Exceptions\DecryptionException;
use App\Services\Encryption\Aes256GcmEncrypter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class Aes256GcmEncrypterTest extends TestCase
{
    #[Test]
    public function it_encrypts_and_decrypts_round_trip(): void
    {
        $encrypter = new Aes256GcmEncrypter(random_bytes(32));

        $plain = 'Guest: Maria Santos · booking #1042';
        $cipher = $encrypter->encrypt($plain);

        $this->assertNotSame($plain, $cipher);
        $this->assertSame($plain, $encrypter->decrypt($cipher));
    }

    #[Test]
    public function tampered_payload_fails_authentication(): void
    {
        $encrypter = new Aes256GcmEncrypter(random_bytes(32));
        $cipher = $encrypter->encrypt('secret');

        $raw = base64_decode($cipher, true);
        $raw[20] = chr(ord($raw[20]) ^ 0xff);
        $tampered = base64_encode($raw);

        $this->expectException(DecryptionException::class);
        $encrypter->decrypt($tampered);
    }
}
