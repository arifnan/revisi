<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FormResponseResource extends JsonResource
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
            'student_id' => $this->student_id,
            'photo_url' => $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_location_valid' => (bool) $this->is_location_valid,
            'submitted_at' => $this->submitted_at ? $this->submitted_at->toIso8601String() : null,
            'form' => new FormResource($this->whenLoaded('form')), // Form yang diisi
            'student' => new UserResource($this->whenLoaded('student')), // Siswa yang mengisi
            'answers' => AnswerResource::collection($this->whenLoaded('answers')), // Anda perlu membuat AnswerResource
        ];
    }
}