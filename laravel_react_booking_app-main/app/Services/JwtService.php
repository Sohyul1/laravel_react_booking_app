<?php

namespace App\Services;

use App\Models\User;
use RuntimeException;

class JwtService
{
    /**
     * The secret key used to sign tokens.
     */
    protected string $secret;

    /**
     * How long an issued token stays valid, in minutes.
     */
    protected int $ttl;

    public function __construct()
    {
        $secret = config('jwt.secret');

        if (empty($secret)) {
            throw new RuntimeException(
                'JWT secret is not set. Set JWT_SECRET (or APP_KEY) in your .env file.'
            );
        }

        $this->secret = $secret;
        $this->ttl = (int) config('jwt.ttl', 60);
    }

    /**
     * Generate a signed JWT for the given user.
     */
    public function generateToken(User $user): string
    {
        $now = time();

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $payload = [
            'sub' => $user->id,
            'email' => $user->email,
            'iat' => $now,
            'exp' => $now + ($this->ttl * 60),
        ];

        $encodedHeader = $this->base64UrlEncode(json_encode($header));
        $encodedPayload = $this->base64UrlEncode(json_encode($payload));

        $signature = $this->sign("{$encodedHeader}.{$encodedPayload}");

        return "{$encodedHeader}.{$encodedPayload}.{$signature}";
    }

    /**
     * Validate a JWT and return its decoded payload.
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException if the token is malformed, tampered with, or expired.
     */
    public function validate(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new RuntimeException('Malformed token.');
        }

        [$encodedHeader, $encodedPayload, $signature] = $parts;

        $expectedSignature = $this->sign("{$encodedHeader}.{$encodedPayload}");

        if (! hash_equals($expectedSignature, $signature)) {
            throw new RuntimeException('Invalid token signature.');
        }

        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);

        if (! is_array($payload) || ! isset($payload['sub'], $payload['exp'])) {
            throw new RuntimeException('Invalid token payload.');
        }

        if (time() >= $payload['exp']) {
            throw new RuntimeException('Token has expired.');
        }

        return $payload;
    }

    /**
     * Sign a string with the app's JWT secret using HMAC-SHA256.
     */
    protected function sign(string $data): string
    {
        return $this->base64UrlEncode(hash_hmac('sha256', $data, $this->secret, true));
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64UrlDecode(string $data): string
    {
        $padded = str_pad($data, strlen($data) + (4 - strlen($data) % 4) % 4, '=');

        return base64_decode(strtr($padded, '-_', '+/'));
    }
}
