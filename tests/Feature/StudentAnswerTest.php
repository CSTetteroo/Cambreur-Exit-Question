<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\ClassModel;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAnswerTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_answer_same_question_twice(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $class = ClassModel::create(['name' => '1A']);
        $student->classes()->sync([$class->id]);

        $docent = User::factory()->create(['role' => 'docent']);
        $q = Question::create(['content' => 'Test?', 'type' => 'open', 'created_by' => $docent->id]);
        $class->active_question_id = $q->id; $class->save();

        $this->actingAs($student)
            ->post(route('answers.store'), [
                'question_id' => $q->id,
                'answer_text' => 'Eerste',
            ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('answers', ['question_id' => $q->id, 'user_id' => $student->id, 'answer_text' => 'Eerste']);

        $this->actingAs($student)
            ->post(route('answers.store'), [
                'question_id' => $q->id,
                'answer_text' => 'Tweede',
            ])->assertSessionHasErrors();

        $this->assertEquals(1, Answer::where('question_id',$q->id)->where('user_id',$student->id)->count());
    }
}
