<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Student extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'students';

    protected $fillable = [
        'name',
        'gender',
        'email',
        'password',
        'grade',
        'address',
        'profile_photo_path'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'password' => 'hashed',
        'gender' => 'boolean',
        // --- DATA ENKRIPSI ---
        'name' => 'encrypted',
        'address' => 'encrypted',
        'grade' => 'encrypted',
        'profile_photo_path' => 'encrypted',
    ];

    public function submittedResponses(): HasMany
    {
        return $this->hasMany(Response::class, 'student_id');
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

    public function getRoleAttribute(): string { return 'student'; }
    public function getNipAttribute(): ?string { return null; }
    public function getSubjectAttribute(): ?string { return null; }
}