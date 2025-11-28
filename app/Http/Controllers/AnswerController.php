<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Choice;
use App\Models\Question;
use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnswerController extends Controller
{
    public function store(Request $request)
    {
        // Dit slaat het antwoord van een student op.
        // Eerst checken we of de invoer klopt,
        // daarna of de vraag echt actief is voor z’n klas(sen),
        // en dan slaan we het antwoord op. Je krijgt maar één poging.
        $user = Auth::user();
        // Validate basic input
        $validated = $request->validate([
            'question_id' => 'required|integer|exists:questions,id',
            'choice_id' => 'nullable|integer|exists:choices,id',
            'answer_text' => 'nullable|string',
        ]);

        $question = Question::with('choices')->findOrFail($validated['question_id']);

        // Zeker weten dat de vraag actief is in minstens één klas van de student.
        // Oftewel: als jouw klas deze vraag niet actief heeft, kun je niet antwoorden.
        $activeQuestionIds = ClassModel::whereNotNull('active_question_id')
            ->whereHas('students', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->pluck('active_question_id')
            ->unique();
        if (!$activeQuestionIds->contains($question->id)) {
            abort(403, 'Deze vraag is niet actief voor jouw klas(sen).');
        }

        // Eén antwoord per student per vraag (geen edits).
        // Dus: al geantwoord? Dan sturen we je terug.
        $exists = Answer::where('question_id', $question->id)->where('user_id', $user->id)->exists();
        if ($exists) {
            return back()->withErrors(['already_answered' => 'Je hebt deze vraag beantwoord!.'])->withInput();
        }
        $answer = new Answer([
            'question_id' => $question->id,
            'user_id' => $user->id,
        ]);

        if ($question->type === 'multiple_choice') {
            // Je moet een geldige optie kiezen die bij deze vraag hoort.
            // Multiple choice = kies één van de gegeven opties.
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
            // Direct bepalen of het goed is op basis van de juiste optie.
            // Als de gekozen optie juist is, heb je ‘m meteen goed.
            $answer->is_correct = (bool) $choice->is_correct;
        } else {
            // Open vraag heeft tekst nodig.
            // Dus: typ iets, en niet leeg.
            $text = trim($validated['answer_text'] ?? '');
            if ($text === '') {
                return back()->withErrors(['answer_text' => 'Antwoord is verplicht'])->withInput();
            }
            $answer->answer_text = $text;
            $answer->choice_id = null;
        }

        // Klaar: opslaan en een berichtje terug.
        $answer->save();
        return back()->with('status', 'Antwoord opgeslagen');
    }
}
