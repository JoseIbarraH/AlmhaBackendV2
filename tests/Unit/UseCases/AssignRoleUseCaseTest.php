<?php

declare(strict_types=1);

namespace Tests\Unit\UseCases;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Src\Admin\Role\Application\AssignRoleToUserUseCase;
use Src\Admin\Role\Domain\Contracts\RoleRepositoryContract;
use Src\Admin\Role\Domain\Exceptions\RoleNotFoundException;

class AssignRoleUseCaseTest extends TestCase
{
    private MockInterface $repository;
    private AssignRoleToUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(RoleRepositoryContract::class);
        $this->useCase = new AssignRoleToUserUseCase($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_delegates_role_assignment_to_repository(): void
    {
        $userId = 'some-uuid-value';
        $roleName = 'blog_manager';

        $this->repository
            ->shouldReceive('assignRoleToUser')
            ->once()
            ->with($userId, $roleName);

        $this->useCase->execute($userId, $roleName);

        $this->addToAssertionCount(1);
    }

    public function test_propagates_role_not_found_exception(): void
    {
        $this->repository
            ->shouldReceive('assignRoleToUser')
            ->once()
            ->andThrow(new RoleNotFoundException('nonexistent_role'));

        $this->expectException(RoleNotFoundException::class);
        $this->expectExceptionMessage("El rol 'nonexistent_role' no fue encontrado.");

        $this->useCase->execute('some-uuid', 'nonexistent_role');
    }
}
