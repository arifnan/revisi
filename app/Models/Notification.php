<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'data', // JSON untuk data tambahan (misal, form_id, response_id, dll.)
        'read_at', // Timestamp kapan notifikasi dibaca
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Dapatkan pengguna yang memiliki notifikasi ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}