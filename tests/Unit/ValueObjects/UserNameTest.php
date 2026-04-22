<?php

declare(strict_types=1);

namespace Tests\Unit\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Src\Admin\User\Domain\ValueObjects\UserName;

class UserNameTest extends TestCase
{
    public function test_creates_valid_name(): void
    {
        $name = new UserName('Juan Perez');

        $this->assertSame('Juan Perez', $name->value());
    }

    public function test_throws_on_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UserName('');
    }

    public function test_throws_on_whitespace_only(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UserName('   ');
    }

    public function test_accepts_single_word_name(): void
    {
        $name = new UserName('Juan');

        $this->assertSame('Juan', $name->value());
    }

    public function test_accepts_name_with_special_characters(): void
    {
        $name = new UserName('José María García-López');

        $this->assertSame('José María García-López', $name->value());
    }
}
