<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


use App\Http\Controllers\UserController;
use App\Http\Controllers\ClassController;
// Controllers referenced with FQCN below
use App\Models\Question;
use Illuminate\Support\Facades\Auth;

// ADMIN ONLY ROUTES
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin_dashboard', [UserController::class, 'admin_index'])->name('admin_dashboard');

    // shite breeze profile things
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User management
    Route::post('/users/{role}', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Class management
    Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
});


// Logged-in user routes
Route::middleware('auth')->group(function () {
    // General user dashboard (students & docenten). Admins are redirected to admin dashboard.
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if ($user && $user->role === 'admin') {
            return redirect()->route('admin_dashboard');
        }
        // Ensure this exists for all roles to avoid compact() error in the view
        $answeredIds = [];
        $questions = collect();
        $myClasses = collect();
        // Students: show active question per class. Docenten: show latest own questions.
        if ($user->role === 'student') {
            $myClasses = \App\Models\ClassModel::with(['activeQuestion.creator','activeQuestion.choices'])
                ->whereHas('students', function ($q) use ($user) { $q->where('users.id', $user->id); })
                ->orderBy('name')
                ->get();
            $questionIds = $myClasses->pluck('active_question_id')->filter()->unique()->values();
            if ($questionIds->isNotEmpty()) {
                $answeredIds = \App\Models\Answer::where('user_id', $user->id)
                    ->whereIn('question_id', $questionIds)
                    ->pluck('question_id')->toArray();
            }
        } else {
            // docent: own latest questions
            $questions = Question::with('creator')
                ->where('created_by', $user->id)
                ->latest()->take(50)->get();
        }
        // Also pass all classes for admin/docent dashboard cards to avoid querying in the view
        $allClasses = \App\Models\ClassModel::orderBy('name')->get();
        return view('user_dashboard', compact('user', 'questions', 'answeredIds', 'myClasses', 'allClasses'));
    })->name('dashboard');

    // Docent-only question management
    Route::middleware(['verified','docent'])->prefix('docent')->name('docent.')->group(function(){
        Route::get('/questions', [\App\Http\Controllers\QuestionController::class, 'index'])->name('questions.index');
        Route::post('/questions', [\App\Http\Controllers\QuestionController::class, 'store'])->name('questions.store');
        Route::post('/questions/{question}/activate', [\App\Http\Controllers\QuestionController::class, 'activate'])->name('questions.activate');
        Route::post('/questions/{question}/time-is-up', [\App\Http\Controllers\QuestionController::class, 'timeIsUp'])->name('questions.time_is_up');
        Route::post('/classes/{class}/clear', [\App\Http\Controllers\QuestionController::class, 'clearActive'])->name('classes.clear');
        Route::get('/questions/{question}/results', [\App\Http\Controllers\QuestionController::class, 'results'])->name('questions.results');
        Route::post('/questions/{question}/correct', [\App\Http\Controllers\QuestionController::class, 'setCorrect'])->name('questions.setCorrect');
        Route::post('/questions/{question}/grade', [\App\Http\Controllers\QuestionController::class, 'gradeOpen'])->name('questions.grade');
        Route::delete('/questions/{question}', [\App\Http\Controllers\QuestionController::class, 'destroy'])->name('questions.destroy');
    });

    // Class detail routes (accessible to admin and docent, controller enforces role)
    Route::get('/classes/{class}', [\App\Http\Controllers\ClassController::class, 'show'])->name('classes.show');
    Route::get('/classes/{class}/students/{user}', [\App\Http\Controllers\ClassController::class, 'studentShow'])->name('classes.student.show');

    // Answers (students)
    Route::post('/answers', [\App\Http\Controllers\AnswerController::class, 'store'])->name('answers.store');

    // First-time password change
    Route::get('/first-password', [\App\Http\Controllers\Auth\FirstPasswordController::class, 'show'])->name('password.first.show');
    Route::post('/first-password', [\App\Http\Controllers\Auth\FirstPasswordController::class, 'update'])->name('password.first.update');

    // Password reset by admin/docent (controller enforces role checks)
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset_password');
});

require __DIR__.'/auth.php';
