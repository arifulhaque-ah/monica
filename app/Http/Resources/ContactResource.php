<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vault_id' => $this->vault_id,

            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'nickname' => $this->nickname,

            'is_favorite' => (bool) $this->is_favorite,
            'personal_note' => $this->personal_note,
            'last_updated_at' => $this->last_updated_at,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
