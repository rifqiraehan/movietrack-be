<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'token' => $this->whenNotNull($this->token),
            'is_admin' => $this->is_admin,
            'pfp' => $this->pfp,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
