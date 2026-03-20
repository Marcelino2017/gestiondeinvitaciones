<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'inviter_user_id' => $this->user_id,
            'email' => $this->email,
            'role' => $this->role,
            'token' => $this->token,
            'status' => (string) $this->status->value,
            'expires_at' => $this->expires_at,
            'accepted_at' => $this->accepted_at,
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'inviter' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
