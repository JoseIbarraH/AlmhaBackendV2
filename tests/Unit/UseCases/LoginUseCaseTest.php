<?php

declare(strict_types=1);

namespace Tests\Unit\UseCases;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Src\Admin\Auth\Application\LoginUseCase;
use Src\Admin\Auth\Domain\Contracts\AuthenticatorContract;
use Src\Admin\Auth\Domain\Exceptions\InvalidCredentialsException;
use Src\Admin\Auth\Domain\ValueObjects\AuthToken;

class LoginUseCaseTest extends TestCase
{
    private MockInterface $authenticator;
    private LoginUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticator = Mockery::mock(AuthenticatorContract::class);
        $this->useCase = new LoginUseCase($this->authenticator);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_returns_auth_token_on_valid_credentials(): void
    {
        $fakeToken = new AuthToken('header.payload.signature');

        $this->authenticator
            ->shouldReceive('login')
            ->once()
            ->with(['email' => 'user@example.com', 'password' => 'secret'], false)
            ->andReturn($fakeToken);

        $result = $this->useCase->execute('user@example.com', 'secret');

        $this->assertSame($fakeToken, $result);
    }

    public function test_propagates_invalid_credentials_exception(): void
    {
        $this->authenticator
            ->shouldReceive('login')
            ->once()
            ->andThrow(new InvalidCredentialsException());

        $this->expectException(InvalidCredentialsException::class);

        $this->useCase->execute('wrong@example.com', 'wrong');
    }

    public function test_passes_remember_me_flag_to_authenticator(): void
    {
        $fakeToken = new AuthToken('header.payload.signature');

        $this->authenticator
            ->shouldReceive('login')
            ->once()
            ->with(['email' => 'user@example.com', 'password' => 'secret'], true)
            ->andReturn($fakeToken);

        $result = $this->useCase->execute('user@example.com', 'secret', true);

        $this->assertSame($fakeToken, $result);
    }
}
