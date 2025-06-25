<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Mendefinisikan secara manual struktur JSON yang akan dikirim
        return [
            'id' => $this->id,
            'form_id' => $this->form_id,
            'student_id' => $this->student_id,
            'photo_url' => $this->photo_url,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_location_valid' => $this->is_location_valid,
            'submitted_at' => $this->submitted_at,

            // Menggunakan Resource lain untuk relasi form dan student (best practice)
            'form' => new FormResource($this->whenLoaded('form')),
            'student' => new UserResource($this->whenLoaded('student')),

            // INILAH KUNCINYA: Secara eksplisit menambahkan data jawaban
            // 'answers' adalah nama field yang diharapkan oleh aplikasi Android Anda
            // 'responseAnswers' adalah nama relasi di Model Response Anda
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
        ];
    }
}