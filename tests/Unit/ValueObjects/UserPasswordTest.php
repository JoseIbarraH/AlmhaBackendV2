<?php

declare(strict_types=1);

namespace Tests\Unit\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Src\Admin\User\Domain\ValueObjects\UserPassword;

class UserPasswordTest extends TestCase
{
    public function test_creates_valid_password(): void
    {
        $password = new UserPassword('secret123');

        $this->assertSame('secret123', $password->value());
    }

    public function test_accepts_minimum_length_password(): void
    {
        $password = new UserPassword('abcd');

        $this->assertSame('abcd', $password->value());
    }

    public function test_throws_on_password_shorter_than_4_chars(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UserPassword('abc');
    }

    public function test_throws_on_empty_password(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UserPassword('');
    }

    public function test_throws_on_whitespace_only_password(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UserPassword('    ');
    }

    public function test_accepts_hashed_password(): void
    {
        $hash = '$2y$12$abcdefghijklmnopqrstuuVGZzX3VJqoTnR.G3fBlMwXREnmZDoi';
        $password = new UserPassword($hash);

        $this->assertSame($hash, $password->value());
    }
}
