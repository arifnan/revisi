<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'option_text'
    ];

    // Relasi: Opsi milik satu pertanyaan
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
