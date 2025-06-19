<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'teacher_id','form_code'];

    // Relasi: Setiap Formulir dibuat oleh satu Guru
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
    
    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('id');
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
