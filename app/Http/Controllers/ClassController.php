<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'class_name' => 'required|string|max:255',
        ]);
        $class = new ClassModel();
        $class->name = $request->class_name;
        $class->save();
        return redirect()->back();
    }

    // Show class details: students and per-student counts of correct/wrong for questions
    public function show(ClassModel $class)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin','docent'])) {
            abort(403);
        }

        // Students in this class
        $students = $class->students()->where('role', 'student')->orderBy('name')->get();

        // Determine which questions to consider: for docents, only their questions; admins see all
        if ($user->role === 'docent') {
            $questionIds = Question::where('created_by', $user->id)->pluck('id')->toArray();
        } else {
            $questionIds = Question::pluck('id')->toArray();
        }

        // Get answer counts grouped by user and correctness
        $studentIds = $students->pluck('id')->toArray();
        $counts = [];
        if (!empty($questionIds) && !empty($studentIds)) {
            $rows = Answer::whereIn('user_id', $studentIds)
                ->whereIn('question_id', $questionIds)
                ->selectRaw('user_id, is_correct, count(*) as cnt')
                ->groupBy('user_id', 'is_correct')
                ->get();

            foreach ($rows as $r) {
                $uid = $r->user_id;
                $correct = $r->is_correct ? 1 : 0;
                $counts[$uid]['correct'] = ($counts[$uid]['correct'] ?? 0) + ($r->cnt * ($correct ? 1 : 0));
                $counts[$uid]['wrong'] = ($counts[$uid]['wrong'] ?? 0) + ($r->cnt * ($correct ? 0 : 1));
            }
        }

        return view('classes.show', [
            'class' => $class,
            'students' => $students,
            'counts' => $counts,
            'forDocent' => ($user->role === 'docent'),
        ]);
    }

    // Show a specific student in a class and list their answers (filtered by docent or all for admin)
    public function studentShow(ClassModel $class, User $user)
    {
        $current = Auth::user();
        if (!$current || !in_array($current->role, ['admin','docent'])) {
            abort(403);
        }
        // ensure the user is a student and belongs to the class
        if ($user->role !== 'student' || !$class->students->contains($user->id)) {
            abort(404);
        }

        if ($current->role === 'docent') {
            $questionIds = Question::where('created_by', $current->id)->pluck('id')->toArray();
        } else {
            $questionIds = Question::pluck('id')->toArray();
        }

        $answers = Answer::with('question', 'choice')
            ->where('user_id', $user->id)
            ->when(!empty($questionIds), fn($q) => $q->whereIn('question_id', $questionIds))
            ->latest()->get();

        $correct = $answers->where('is_correct', 1)->count();
        $wrong = $answers->where('is_correct', 0)->count();

        return view('classes.student', [
            'class' => $class,
            'student' => $user,
            'answers' => $answers,
            'correct' => $correct,
            'wrong' => $wrong,
            'forDocent' => ($current->role === 'docent'),
        ]);
    }
}
