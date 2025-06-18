<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'form_code' => $this->form_code, // Ini akan ada karena di-set di FormController
            'teacher_id' => $this->teacher_id,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'teacher' => new UserResource($this->whenLoaded('teacher')), // Asumsi UserResource ada
            'questions' => QuestionResource::collection($this->whenLoaded('questions')),
            'responses_count' => $this->whenLoaded('responses', fn() => $this->responses()->count()),
        ];
    }
}
