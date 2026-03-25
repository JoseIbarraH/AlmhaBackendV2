<?php

declare(strict_types=1);

namespace Src\Admin\User\Infrastructure\Repositories;

use App\Models\User as EloquentUserModel;
use Src\Admin\User\Domain\Contracts\UserRepositoryContract;
use Src\Admin\User\Domain\Entity\User;
use Src\Admin\User\Domain\ValueObjects\UserEmail;
use Src\Admin\User\Domain\ValueObjects\UserEmailVerifiedDate;
use Src\Admin\User\Domain\ValueObjects\UserId;
use Src\Admin\User\Domain\ValueObjects\UserName;
use Src\Admin\User\Domain\ValueObjects\UserPassword;
use Src\Admin\User\Domain\ValueObjects\UserRememberToken;
use Src\Admin\User\Domain\ValueObjects\UserStatus;

final class EloquentUserRepository implements UserRepositoryContract
{
    private $eloquentUserModel;

    public function __construct()
    {
        $this->eloquentUserModel = new EloquentUserModel;
    }

    public function save(User $user): void
    {
        $newUser = $this->eloquentUserModel;

        $data = [
            'name' => $user->name()->value(),
            'email' => $user->email()->value(),
            'email_verified_at' => $user->emailVerifiedDate()->value(),
            'password' => $user->password()->value(),
            'remember_token' => $user->rememberToken()->value(),
            'is_active' => $user->status()->value(),
        ];

        $newUser->create($data);
    }

    public function findByCriteria(?string $term, ?UserName $name, ?UserEmail $email): array
    {
        $query = $this->eloquentUserModel->newQuery();

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', '%' . $term . '%')
                  ->orWhere('email', 'LIKE', '%' . $term . '%');
            });
        }

        if ($name) {
            $query->where('name', 'LIKE', '%' . $name->value() . '%');
        }

        if ($email) {
            $query->where('email', 'LIKE', '%' . $email->value() . '%');
        }

        $users = $query->get();

        return $users->map(function ($user) {
            return new User(
                new UserName($user->name),
                new UserEmail($user->email),
                new UserEmailVerifiedDate($user->email_verified_at !== null ? (string)$user->email_verified_at : null),
                new UserPassword($user->password),
                new UserRememberToken($user->remember_token !== null ? (string)$user->remember_token : null),
                new UserStatus((bool)$user->is_active),
                new UserId((int)$user->id)
            );
        })->toArray();
    }
    public function findById(UserId $id): ?User
    {
        $user = $this->eloquentUserModel->find($id->value());

        if (!$user) {
            return null;
        }

        return new User(
            new UserName($user->name),
            new UserEmail($user->email),
            new UserEmailVerifiedDate($user->email_verified_at !== null ? (string)$user->email_verified_at : null),
            new UserPassword($user->password),
            new UserRememberToken($user->remember_token !== null ? (string)$user->remember_token : null),
            new UserStatus((bool)$user->is_active),
            new UserId((int)$user->id)
        );
    }

    public function update(User $user): void
    {
        if ($user->id() === null) {
            return;
        }

        $eloquentUser = $this->eloquentUserModel->find($user->id()->value());
        
        if ($eloquentUser) {
            $eloquentUser->update([
                'name' => $user->name()->value(),
                'email' => $user->email()->value(),
                'password' => $user->password()->value(),
                'is_active' => $user->status()->value(),
            ]);
        }
    }

    public function delete(UserId $id): void
    {
        $eloquentUser = $this->eloquentUserModel->find($id->value());
        
        if ($eloquentUser) {
            $eloquentUser->delete();
        }
    }

    public function getAll(): array
    {
        $users = $this->eloquentUserModel->all();
        
        return $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => (bool)$user->is_active,
                'email_verified_at' => $user->email_verified_at,
            ];
        })->toArray();
    }
}
