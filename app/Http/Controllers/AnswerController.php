<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Choice;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnswerController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        // Validate basic input
        $validated = $request->validate([
            'question_id' => 'required|integer|exists:questions,id',
            'choice_id' => 'nullable|integer|exists:choices,id',
            'answer_text' => 'nullable|string',
        ]);

        $question = Question::with('choices')->findOrFail($validated['question_id']);

        // Ensure question is active in at least one of the student's classes
        $activeQuestionIds = \App\Models\ClassModel::whereNotNull('active_question_id')
            ->whereHas('students', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->pluck('active_question_id')
            ->unique();
        if (!$activeQuestionIds->contains($question->id)) {
            abort(403, 'Deze vraag is niet actief voor jouw klas(sen).');
        }

        // Enforce one answer per student per question (no edits)
        $exists = Answer::where('question_id', $question->id)->where('user_id', $user->id)->exists();
        if ($exists) {
            return back()->withErrors(['already_answered' => 'Je hebt deze vraag beantwoord!.'])->withInput();
        }
        $answer = new Answer([
            'question_id' => $question->id,
            'user_id' => $user->id,
        ]);

        if ($question->type === 'multiple_choice') {
            // Must select a valid choice belonging to this question
            $choiceId = $validated['choice_id'] ?? null;
            if (!$choiceId) {
                return back()->withErrors(['choice_id' => 'Kies een optie'])->withInput();
            }
            $choice = Choice::where('id', $choiceId)->where('question_id', $question->id)->first();
            if (!$choice) {
                return back()->withErrors(['choice_id' => 'Ongeldige optie'])->withInput();
            }
            $answer->choice_id = $choice->id;
            $answer->answer_text = null;
        } else {
            // Open question requires text
            $text = trim($validated['answer_text'] ?? '');
            if ($text === '') {
                return back()->withErrors(['answer_text' => 'Antwoord is verplicht'])->withInput();
            }
            $answer->answer_text = $text;
            $answer->choice_id = null;
        }

        $answer->save();
        return back()->with('status', 'Antwoord opgeslagen');
    }
}
