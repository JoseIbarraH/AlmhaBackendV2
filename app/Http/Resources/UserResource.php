<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Src\Admin\User\Domain\Entity\User;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var User $user */
        $user = $this->resource;

        if (!$user instanceof User) {
            return parent::toArray($request);
        }

        return [
            'id' => $user->id()->value(),
            'name' => $user->name()->value(),
            'email' => $user->email()->value(),
            'email_verified_date' => $user->emailVerifiedDate()->value(),
            'is_main_admin' => (bool)$user->isMainAdmin()->value(),
            'roles' => $user->roles(),
        ];
    }
}
