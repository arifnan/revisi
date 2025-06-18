<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Teacher extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'teachers';

    protected $fillable = [
        'nip',
        'name',
        'gender',
        'email',
        'password',
        'subject',
        'address',
        'profile_photo_path'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'password' => 'hashed',
        'gender' => 'boolean',
        // --- BLOK ENKRIPSI ---
        'name' => 'encrypted',
        // 'nip' => 'encrypted',
        // 'email' => 'encrypted',
        'address' => 'encrypted',
        'subject' => 'encrypted',
        'profile_photo_path' => 'encrypted',
    ];

    public function createdForms(): HasMany
    {
        return $this->hasMany(Form::class, 'teacher_id');
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')
                    ->orderBy('created_at', 'desc');
    }

    public function favorites(): MorphToMany
    {
        return $this->morphToMany(Form::class, 'user', 'favorite_forms');
    }

    public function getRoleAttribute(): string
    {
        return 'teacher';
    }

    public function getGradeAttribute(): ?string
    {
        return null;
    }
}