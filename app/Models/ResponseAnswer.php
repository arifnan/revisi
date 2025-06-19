<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponseAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'response_id',
        'question_id',
        'answer_text',
        'option_id',
        'file_url', // Jika ada di tabel response_answers
        'latitude', // Jika ada di tabel response_answers
        'longitude', // Jika ada di tabel response_answers
        'formatted_address' // Jika ada di tabel response_answers
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'answer_text' => 'encrypted',
    ];

    // Relasi ke Response (Jawaban ini milik respon mana)
    public function response()
    {
        return $this->belongsTo(Response::class);
    }

    // Relasi ke Question (Jawaban ini untuk pertanyaan mana)
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    // Relasi ke QuestionOption (Jika jawaban ini adalah pilihan dari opsi pertanyaan)
    public function option()
    {
        return $this->belongsTo(QuestionOption::class);
    }

    // Hapus relasi form(), student(), responseAnswers(), dan getPhotoUrlAttribute() dari sini.
    // Mereka tidak seharusnya ada di model ResponseAnswer.
}