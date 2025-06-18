<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'question_text',
        'question_type',
        // Ganti 'is_required' menjadi 'required' agar konsisten dengan Android & validasi
        // Pastikan nama kolom di database juga 'required' atau sesuaikan di sini
        'required',
        'requires_location' // Pastikan field ini ada di tabel jika digunakan
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        // 'options' tidak di-cast sebagai array di sini karena disimpan di tabel terpisah
        'required' => 'boolean',
        'requires_location' => 'boolean', // Jika ada dan boolean
    ];

    /**
     * Relasi: Pertanyaan milik satu formulir.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Relasi: Satu pertanyaan bisa memiliki banyak opsi jawaban.
     * Nama relasi ini sudah benar 'options' karena akan mengambil QuestionOption models.
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    /**
     * Relasi: Satu pertanyaan bisa memiliki banyak jawaban dari user.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ResponseAnswer::class);
    }
}
