<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Response extends Model
{
    use HasFactory;

    protected $table = 'responses';

    protected $fillable = [
        'form_id',
        'student_id',
        'photo_path',
        'latitude',
        'longitude',
        'is_location_valid',
        'submitted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_location_valid' => 'boolean',
        'submitted_at' => 'datetime',
        // --- BLOK ENKRIPSI ---
        'latitude' => 'encrypted',
        'longitude' => 'encrypted',
        'photo_path' => 'encrypted',
    ];

    protected $appends = ['photo_url'];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
    
    public function answers(): HasMany
    {
        return $this->hasMany(ResponseAnswer::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo_path) {
            return Storage::disk('public')->url($this->photo_path);
        }
        return null;
    }
}