<?php

namespace Src\Admin\Auth\Domain\ValueObjects;

use InvalidArgumentException;

final class AuthToken
{

    private $value;

    /**
     * AuthToken constructor.
     * @param string $token
     * @throws InvalidArgumentException
     */
    public function __construct(string $token)
    {
        $this->ensureIsValidToken($token);
        $this->value = $token;
    }

    private function ensureIsValidToken(string $token): void
    {
        // 1. Validar que no sea un string vacío
        if (empty(trim($token))) {
            throw new InvalidArgumentException("El token de autenticación no puede estar vacío.");
        }

        // 2. Validar estructura JWT (header.payload.signature)
        if (substr_count($token, '.') !== 2) {
            throw new InvalidArgumentException("El formato del token JWT es inválido.");
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(AuthToken $other): bool
    {
        return $this->value === $other->value();
    }

    public function extractPayload(): array
    {
        $parts = explode('.', $this->value);
        return json_decode(base64_decode($parts[1]), true) ?: [];
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

