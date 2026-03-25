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
            'name' => $user->name()->value(),
            'email' => $user->email()->value(),
            'email_verified_date' => $user->emailVerifiedDate()->value(),
        ];
    }
}
