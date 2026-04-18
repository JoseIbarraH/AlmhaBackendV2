<?php

namespace Src\Admin\User\Domain\Contracts;

use Src\Admin\User\Domain\Entity\User;
use Src\Admin\User\Domain\ValueObjects\UserEmail;
use Src\Admin\User\Domain\ValueObjects\UserName;
use Src\Admin\User\Domain\ValueObjects\UserId;

interface UserRepositoryContract
{
    public function save(User $user): void;
    
    public function findById(UserId $id): ?User;
    
    public function findByCriteria(?string $term, ?UserName $name, ?UserEmail $email): array;
    
    public function update(User $user): void;
    
    public function delete(UserId $id): void;
    
    public function getAll(int $page = 1, int $perPage = 15): array;

    public function hasAdmin(): bool;

    public function findByToken(string $token): ?User;
}
