<?php

declare(strict_types=1);

namespace Tests\Unit\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Src\Admin\User\Domain\ValueObjects\UserEmail;

class UserEmailTest extends TestCase
{
    public function test_creates_valid_email(): void
    {
        $email = new UserEmail('user@example.com');

        $this->assertSame('user@example.com', $email->value());
    }

    public function test_throws_on_invalid_email_format(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UserEmail('not-an-email');
    }

    public function test_throws_on_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UserEmail('');
    }

    public function test_throws_on_missing_domain(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UserEmail('user@');
    }

    public function test_throws_on_missing_at_symbol(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UserEmail('userexample.com');
    }

    public function test_accepts_email_with_subdomain(): void
    {
        $email = new UserEmail('user@mail.example.com');

        $this->assertSame('user@mail.example.com', $email->value());
    }
}
