<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
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
            'form_id' => $this->form_id,
            'question_text' => $this->question_text,
            'question_type' => $this->question_type,
            // Mengambil 'option_text' dari relasi 'options' (QuestionOption)
            // dan mengubahnya menjadi array of strings
            'options' => $this->whenLoaded('options', function () {
                return $this->options->pluck('option_text')->toArray();
            }),
            // Ganti 'is_required' menjadi 'required' jika nama kolom di DB sudah diubah
            'required' => (bool) $this->required, // atau $this->is_required
            'requires_location' => (bool) $this->requires_location, // Jika ada
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}