<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
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
            // 'response_id' => $this->response_id, // Mungkin tidak perlu dikirim ke client
            'question_id' => $this->question_id,
            'answer_text' => $this->answer_text,
            'question' => new QuestionResource($this->whenLoaded('question')), // Detail pertanyaan
            // 'option_id' => $this->option_id, // Jika Anda menyimpannya
        ];
    }
}