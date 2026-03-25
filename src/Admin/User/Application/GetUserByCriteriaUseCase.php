<?php

declare(strict_types=1);

namespace Src\Admin\User\Application;

use Src\Admin\User\Domain\Contracts\UserRepositoryContract;
use Src\Admin\User\Domain\Entity\User;
use Src\Admin\User\Domain\ValueObjects\UserEmail;
use Src\Admin\User\Domain\ValueObjects\UserName;
use RuntimeException;

final class GetUserByCriteriaUseCase
{
    private UserRepositoryContract $repository;

    public function __construct(UserRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(?string $term, ?string $name = null, ?string $email = null): array
    {
        $userName = $name ? new UserName($name) : null;
        $userEmail = $email ? new UserEmail($email) : null;

        return $this->repository->findByCriteria($term, $userName, $userEmail);
    }
}
