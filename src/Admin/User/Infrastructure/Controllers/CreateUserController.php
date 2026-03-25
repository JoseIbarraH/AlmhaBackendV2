<?php

declare(strict_types = 1);

namespace Src\Admin\User\Infrastructure\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Src\Admin\User\Application\CreateUserUseCase;
use Src\Admin\User\Application\GetUserByCriteriaUseCase;
use Src\Admin\User\Infrastructure\Repositories\EloquentUserRepository;

final class CreateUserController
{
    private $repository;

    public function __construct(EloquentUserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(Request $request)
    {
        $userName = $request->input('name');
        $userEmail = $request->input('email');
        $userEmailVerifiedDate = null;
        $userPassword = Hash::make($request->input('password'));
        $userRememberToken = null;
        $isActive = $request->input('is_active', true);

        $createUserUseCase = new CreateUserUseCase($this->repository);
        $createUserUseCase->__invoke(
            $userName,
            $userEmail,
            $userEmailVerifiedDate,
            $userPassword,
            $userRememberToken,
            $isActive
        );

        $getUserByCriteriaUseCase = new GetUserByCriteriaUseCase($this->repository);
        $matchedUsers = $getUserByCriteriaUseCase->execute(null, $userName, $userEmail);
        $newUser = count($matchedUsers) > 0 ? $matchedUsers[0] : null;

        return response()->json(new UserResource($newUser), 201);
    }
}
