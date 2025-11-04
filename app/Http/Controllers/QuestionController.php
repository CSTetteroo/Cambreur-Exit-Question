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
            'activate_class_ids' => 'array',
            'activate_class_ids.*' => 'integer|exists:classes,id',
        ]);

        $question = new Question();
        $question->content = $validated['content'];
        $question->type = $validated['type'];
        $question->created_by = Auth::id();
        $question->save();

        if ($question->type === 'multiple_choice' && !empty($validated['choices'])) {
            // Filter out empty strings and assign labels A, B, C, ...
            $texts = array_values(array_filter($validated['choices'], fn ($t) => $t !== null && trim($t) !== ''));
            $label = 'A';
            foreach ($texts as $text) {
                Choice::create([
                    'question_id' => $question->id,
                    'label' => $label,
                    'text' => $text,
                ]);
                $label++;
            }
        }

        // Optionally activate this question for selected classes
        if (!empty($validated['activate_class_ids'])) {
            ClassModel::whereIn('id', $validated['activate_class_ids'])
                ->update(['active_question_id' => $question->id]);
        }

        return redirect()->route('docent.questions.index')->with('status', 'Vraag aangemaakt');
    }

    // Activate existing question for selected classes
    public function activate(Request $request, Question $question)
    {
        $this->authorizeQuestion($question);
        $data = $request->validate([
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'integer|exists:classes,id',
        ]);
        ClassModel::whereIn('id', $data['class_ids'])
            ->update(['active_question_id' => $question->id]);
        return back()->with('status', 'Vraag geactiveerd voor geselecteerde klassen');
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
