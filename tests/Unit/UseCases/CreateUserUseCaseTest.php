<?php

declare(strict_types=1);

namespace Tests\Unit\UseCases;

use Illuminate\Support\Facades\Bus;
use Mockery;
use Mockery\MockInterface;
use Src\Admin\User\Application\CreateUserUseCase;
use Src\Admin\User\Domain\Contracts\UserRepositoryContract;
use Src\Admin\User\Domain\Entity\User;
use Tests\TestCase;

class CreateUserUseCaseTest extends TestCase
{
    private MockInterface $repository;
    private CreateUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        $this->repository = Mockery::mock(UserRepositoryContract::class);
        $this->useCase = new CreateUserUseCase($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_saves_user_to_repository(): void
    {
        $this->repository
            ->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        ($this->useCase)(
            'Juan Perez',
            'juan@example.com',
            null,
            '$2y$12$hashedpassword1234567890abcdef',
            null,
            true,
            []
        );

        $this->addToAssertionCount(1);
    }

    public function test_throws_on_invalid_email(): void
    {
        $this->repository->shouldNotReceive('save');

        $this->expectException(\InvalidArgumentException::class);

        ($this->useCase)(
            'Juan Perez',
            'not-an-email',
            null,
            '$2y$12$hashedpassword1234567890abcdef',
            null,
            true,
            []
        );
    }

    public function test_throws_on_empty_name(): void
    {
        $this->repository->shouldNotReceive('save');

        $this->expectException(\InvalidArgumentException::class);

        ($this->useCase)(
            '',
            'juan@example.com',
            null,
            '$2y$12$hashedpassword1234567890abcdef',
            null,
            true,
            []
        );
    }
}
