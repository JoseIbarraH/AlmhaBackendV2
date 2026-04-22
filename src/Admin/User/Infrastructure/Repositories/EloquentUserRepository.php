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
use Src\Admin\User\Domain\ValueObjects\UserMainAdmin;
use Src\Admin\User\Domain\ValueObjects\UserVerificationToken;
use Illuminate\Support\Facades\DB;

final class EloquentUserRepository implements UserRepositoryContract
{
    public function __construct(private EloquentUserModel $eloquentUserModel) {}


    public function save(User $user): void
    {
        DB::transaction(function () use ($user) {
            $data = [
                'name' => $user->name()->value(),
                'email' => $user->email()->value(),
                'email_verified_at' => $user->emailVerifiedDate()->value(),
                'password' => $user->password()->value(),
                'remember_token' => $user->rememberToken()->value(),
                'is_active' => $user->status()->value(),
                'is_main_admin' => $user->isMainAdmin()->value(),
                'verification_token' => $user->verificationToken()->value(),
            ];

            $newUserModel = $this->eloquentUserModel->create($data);

            if (!empty($user->roles())) {
                $newUserModel->assignRole($user->roles());
            }
        });
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

        return $users->map(function (EloquentUserModel $user) {
            return new User(
                new UserName($user->name),
                new UserEmail($user->email),
                new UserEmailVerifiedDate($user->email_verified_at !== null ? (string)$user->email_verified_at : null),
                new UserPassword($user->password),
                new UserRememberToken($user->remember_token !== null ? (string)$user->remember_token : null),
                new UserStatus((bool)$user->is_active),
                new UserMainAdmin((bool)$user->is_main_admin),
                new UserVerificationToken($user->verification_token),
                $user->getRoleNames()->toArray(),
                new UserId((string)$user->id)
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
            new UserMainAdmin((bool)$user->is_main_admin),
            new UserVerificationToken($user->verification_token),
            $user->getRoleNames()->toArray(),
            new UserId((string)$user->id)
        );
    }

    public function update(User $user): void
    {
        if ($user->id() === null) {
            return;
        }

        DB::transaction(function () use ($user) {
            $eloquentUser = $this->eloquentUserModel->find($user->id()->value());
            
            if ($eloquentUser) {
                $eloquentUser->update([
                    'name' => $user->name()->value(),
                    'email' => $user->email()->value(),
                    'password' => $user->password()->value(),
                    'is_active' => $user->status()->value(),
                    'is_main_admin' => $user->isMainAdmin()->value(),
                    'email_verified_at' => $user->emailVerifiedDate()->value(),
                    'verification_token' => $user->verificationToken()->value(),
                ]);

                $eloquentUser->syncRoles($user->roles());
            }
        });
    }

    public function delete(UserId $id): void
    {
        $eloquentUser = $this->eloquentUserModel->find($id->value());
        
        if ($eloquentUser) {
            $eloquentUser->delete();
        }
    }

    public function getAll(int $page = 1, int $perPage = 15): array
    {
        $paginator = $this->eloquentUserModel->paginate($perPage, ['*'], 'page', $page);
        
        $items = collect($paginator->items())->map(function (EloquentUserModel $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => (bool)$user->is_active,
                'is_main_admin' => (bool)$user->is_main_admin,
                'email_verified_at' => $user->email_verified_at,
                'roles' => $user->getRoleNames()->toArray(),
            ];
        })->toArray();

        return [
            'items' => $items,
            'meta' => collect($paginator->toArray())->except('data')->toArray()
        ];
    }

    public function hasAdmin(): bool
    {
        return $this->eloquentUserModel->role('super_admin')->exists();
    }

    public function findByToken(string $token): ?User
    {
        $user = $this->eloquentUserModel->where('verification_token', $token)->first();

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
            new UserMainAdmin((bool)$user->is_main_admin),
            new UserVerificationToken($user->verification_token),
            $user->getRoleNames()->toArray(),
            new UserId((string)$user->id)
        );
    }
}
