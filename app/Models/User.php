<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'nip', // Sudah ada di proyek Anda
        'password',
        'role', // Sudah ada di proyek Anda ('guru' atau 'siswa')
        'address', // Tambahkan jika belum ada di migrasi Anda
        'profile_photo_path', // Tambahkan jika belum ada di migrasi Anda
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Formulir yang dibuat oleh pengguna (jika guru).
     */
    public function forms(): HasMany // Nama relasi ini sudah ada di proyek Anda
    {
        return $this->hasMany(Form::class, 'user_id');
    }

    /**
     * Respons formulir yang dikirim oleh pengguna (jika siswa).
     */
    public function formResponses(): HasMany
    {
        return $this->hasMany(FormResponse::class, 'student_id');
    }

    /**
     * Notifikasi untuk pengguna ini.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    /**
     * Formulir yang difavoritkan oleh pengguna ini.
     */
    public function favoriteForms(): BelongsToMany
    {
        return $this->belongsToMany(Form::class, 'favorite_forms', 'user_id', 'form_id');
    }
}