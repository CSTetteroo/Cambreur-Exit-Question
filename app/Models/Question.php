<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'type', 'created_by'];

    // Docent die deze vraag heeft gemaakt
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Multiple choice opties
    public function choices()
    {
        return $this->hasMany(Choice::class);
    }

    // Antwoorden van studenten
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}

