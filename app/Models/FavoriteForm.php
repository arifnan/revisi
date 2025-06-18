<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FavoriteForm extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'favorite_forms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'form_id',
    ];

    /**
     * Menandakan jika model harus memiliki timestamps.
     *
     * @var bool
     */
    public $timestamps = false; // Umumnya tabel pivot tidak memerlukan timestamps

    /**
     * Dapatkan pengguna yang memfavoritkan formulir ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Dapatkan formulir yang difavoritkan.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}