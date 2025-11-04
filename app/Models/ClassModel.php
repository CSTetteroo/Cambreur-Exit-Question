<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';
    protected $fillable = ['name', 'active_question_id'];

    // Studenten in deze klas
    public function students()
    {
        // Explicit pivot keys: class_id (this model) ↔ user_id
        return $this->belongsToMany(User::class, 'class_user', 'class_id', 'user_id');
    }

    // De actieve vraag
    public function activeQuestion()
    {
        return $this->belongsTo(Question::class, 'active_question_id');
    }
}
