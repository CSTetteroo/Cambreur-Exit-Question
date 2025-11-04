<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\ClassModel;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    // Show create form and list of own questions
    public function index()
    {
        $user = Auth::user();
        $questions = Question::withCount(['answers', 'choices'])
            ->with('choices')
            ->where('created_by', $user->id)
            ->latest()->get();
        $classes = ClassModel::with('activeQuestion')->orderBy('name')->get();

        return view('docent_questions', [
            'user' => $user,
            'questions' => $questions,
            'classes' => $classes,
        ]);
    }

    // Store new question (open or multiple_choice) with optional choices and activation on classes
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'required|in:multiple_choice,open',
            'choices' => 'array',
            'choices.*' => 'nullable|string|max:255',
            'correct_choice' => 'nullable|integer|min:0',
            'activate_class_ids' => 'array',
            'activate_class_ids.*' => 'integer|exists:classes,id',
        ]);

        // Additional validation rules for multiple choice: at least 2 non-empty, max 4
        if (($validated['type'] ?? null) === 'multiple_choice') {
            $raw = $validated['choices'] ?? [];
            $nonEmpty = array_values(array_filter($raw, fn($t) => $t !== null && trim($t) !== ''));
            if (count($nonEmpty) < 2) {
                return back()->withErrors(['choices' => 'Minimaal 2 opties zijn verplicht voor een meerkeuzevraag.'])->withInput();
            }
            if (count($nonEmpty) > 4) {
                return back()->withErrors(['choices' => 'Maximaal 4 opties zijn toegestaan.'])->withInput();
            }
            // Require a correct option to be selected and valid
            if (!isset($validated['correct_choice'])) {
                return back()->withErrors(['correct_choice' => 'Kies het juiste antwoord voor een meerkeuzevraag.'])->withInput();
            }
            $idx = (int)$validated['correct_choice'];
            if (!array_key_exists($idx, $raw) || ($raw[$idx] === null || trim($raw[$idx]) === '')) {
                return back()->withErrors(['correct_choice' => 'Geselecteerde juiste optie is leeg of ongeldig.'])->withInput();
            }
        }

        $question = new Question();
        $question->content = $validated['content'];
        $question->type = $validated['type'];
        $question->created_by = Auth::id();
        $question->save();

        if ($question->type === 'multiple_choice' && !empty($validated['choices'])) {
            // Assign labels A, B, C... but evaluate correctness against original indices from the form
            $label = 'A';
            foreach ($validated['choices'] as $idx => $text) {
                if ($text === null || trim($text) === '') {
                    continue;
                }
                Choice::create([
                    'question_id' => $question->id,
                    'label' => $label,
                    'text' => $text,
                    'is_correct' => isset($validated['correct_choice']) && intval($validated['correct_choice']) === intval($idx),
                ]);
                $label++;
            }
        }

        // Optionally activate this question for selected classes, with overwrite warning
        $warning = null;
        if (!empty($validated['activate_class_ids'])) {
            $overwritten = ClassModel::whereIn('id', $validated['activate_class_ids'])
                ->whereNotNull('active_question_id')
                ->pluck('name')->toArray();
            ClassModel::whereIn('id', $validated['activate_class_ids'])
                ->update(['active_question_id' => $question->id]);
            if (!empty($overwritten)) {
                $warning = 'Let op: bestaande actieve vragen zijn overschreven voor: '.implode(', ', $overwritten);
            }
        }

        return redirect()->route('docent.questions.index')
            ->with('status', 'Vraag aangemaakt')
            ->with('warning', $warning);
    }

    // Mark correct choice for a multiple choice question
    public function setCorrect(Request $request, Question $question)
    {
        $this->authorizeQuestion($question);
        if ($question->type !== 'multiple_choice') {
            return back()->withErrors(['correct' => 'Alleen bij meerkeuzevragen.']);
        }
        $data = $request->validate([
            'choice_id' => 'required|integer|exists:choices,id',
        ]);
        // Ensure the choice belongs to this question
        $choice = Choice::where('id', $data['choice_id'])->where('question_id', $question->id)->firstOrFail();
        // Reset all to false, then set one to true
        Choice::where('question_id', $question->id)->update(['is_correct' => false]);
        $choice->is_correct = true;
        $choice->save();
        return back()->with('status', 'Juiste antwoord opgeslagen');
    }

    // Results overview for a question (optionally filter by class)
    public function results(Request $request, Question $question)
    {
        $this->authorizeQuestion($question);
        $classId = $request->query('class_id');

        // Base answers query for the question
        $answersQuery = \App\Models\Answer::with(['user', 'choice'])
            ->where('question_id', $question->id);

        if ($classId) {
            // Only answers from users in the selected class
            $answersQuery->whereHas('user.classes', function ($q) use ($classId) {
                $q->where('classes.id', $classId);
            });
        }

        $answers = $answersQuery->latest()->get();
        $classes = ClassModel::orderBy('name')->get();

        // Build table rows per user: if class filter set, include all students in that class; else include all students
        $students = $classId
            ? optional(ClassModel::with('students')->find($classId))->students ?? collect()
            : \App\Models\User::where('role', 'student')->orderBy('name')->get();

        $answersByUser = $answers->keyBy('user_id');
        $rows = $students->map(function ($stu) use ($answersByUser, $question) {
            $ans = $answersByUser->get($stu->id);
            $status = 'neutral';
            if ($ans) {
                if ($question->type === 'multiple_choice') {
                    $status = (optional($ans->choice)->is_correct ?? false) ? 'correct' : 'wrong';
                } else { // open question
                    if (!is_null($ans->is_correct)) {
                        $status = $ans->is_correct ? 'correct' : 'wrong';
                    }
                }
            }
            return [
                'user' => $stu,
                'answer' => $ans,
                'status' => $status,
            ];
        });

        // For multiple_choice: distribution per choice
        $distribution = null;
        if ($question->type === 'multiple_choice') {
            $distribution = $answers->groupBy('choice_id')->map(function ($group) {
                return $group->count();
            });
            // Include labels
            $labels = $question->choices()->pluck('label', 'id');
            $distribution = $distribution->mapWithKeys(function ($count, $choiceId) use ($labels) {
                $label = $labels[$choiceId] ?? '?';
                return [$label => $count];
            })->sortKeys();
        }

        return view('docent_results', [
            'question' => $question->load('choices'),
            'answers' => $answers,
            'classes' => $classes,
            'selectedClassId' => $classId,
            'distribution' => $distribution,
            'rows' => $rows,
        ]);
    }

    // Grade an open-answer response as correct/incorrect
    public function gradeOpen(Request $request, Question $question)
    {
        $this->authorizeQuestion($question);
        if ($question->type !== 'open') {
            abort(400, 'Beoordelen is alleen beschikbaar voor open vragen');
        }
        $data = $request->validate([
            'answer_id' => 'required|integer|exists:answers,id',
            'is_correct' => 'required|in:0,1',
        ]);
        $answer = \App\Models\Answer::where('id', $data['answer_id'])
            ->where('question_id', $question->id)
            ->firstOrFail();
        $answer->is_correct = (bool) ((int) $data['is_correct']);
        $answer->save();
        return back()->with('status', 'Beoordeling opgeslagen');
    }

    public function destroy(Question $question)
    {
        $this->authorizeQuestion($question);
        $question->delete();
        return back()->with('status', 'Vraag verwijderd');
    }

    // Activate existing question for selected classes
    public function activate(Request $request, Question $question)
    {
        $this->authorizeQuestion($question);
        $data = $request->validate([
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'integer|exists:classes,id',
        ]);
        $selectedIds = collect($data['class_ids'])->unique()->values();

        // Overwrite: classes being selected that already had any active question
        $overwritten = ClassModel::whereIn('id', $selectedIds)
            ->whereNotNull('active_question_id')
            ->pluck('name')->toArray();
        // Activate on selected
        ClassModel::whereIn('id', $selectedIds)->update(['active_question_id' => $question->id]);

        // Clear for classes that currently have THIS question active but are not selected anymore
        $currentlyWithThis = ClassModel::where('active_question_id', $question->id)->pluck('id');
        $toClear = $currentlyWithThis->diff($selectedIds);
        if ($toClear->isNotEmpty()) {
            ClassModel::whereIn('id', $toClear)->update(['active_question_id' => null]);
        }

        $warning = !empty($overwritten) ? ('Let op: bestaande actieve vragen zijn overschreven voor: '.implode(', ', $overwritten)) : null;
        return back()->with('status', 'Vraag geactiveerd voor geselecteerde klassen')->with('warning', $warning);
    }

    // Clear active question for a class
    public function clearActive(ClassModel $class)
    {
        $class->active_question_id = null;
        $class->save();
        return back()->with('status', 'Actieve vraag gewist voor klas');
    }

    private function authorizeQuestion(Question $question)
    {
        if ($question->created_by !== Auth::id()) {
            abort(403, 'Je mag alleen je eigen vragen beheren.');
        }
    }
}
