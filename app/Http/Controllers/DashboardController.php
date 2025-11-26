<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\ClassModel;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user && $user->role === 'admin') {
            return redirect()->route('admin_dashboard');
        }

        // Ensure these exist for all roles to avoid compact() issues
        $answeredIds = [];
        $questions = collect();
        $myClasses = collect();

        // Students: show active question per class. Docenten: show latest own questions.
        if ($user->role === 'student') {
            $myClasses = ClassModel::with(['activeQuestion.creator', 'activeQuestion.choices'])
                ->whereHas('students', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                ->orderBy('name')
                ->get();

            $questionIds = $myClasses->pluck('active_question_id')->filter()->unique()->values();
            if ($questionIds->isNotEmpty()) {
                $answeredIds = Answer::where('user_id', $user->id)
                    ->whereIn('question_id', $questionIds)
                    ->pluck('question_id')
                    ->toArray();
            }
        } else {
            // docent: own latest questions
            $questions = Question::with('creator')
                ->where('created_by', $user->id)
                ->latest()
                ->take(50)
                ->get();
        }

        // Also pass all classes for admin/docent dashboard cards to avoid querying in the view
        $allClasses = ClassModel::orderBy('name')->get();

        return view('user_dashboard', compact('user', 'questions', 'answeredIds', 'myClasses', 'allClasses'));
    }
}
