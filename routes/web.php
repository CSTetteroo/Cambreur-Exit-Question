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
        // Students: show active questions for their classes. Docenten: show latest own questions.
        if ($user->role === 'student') {
            $questionIds = \App\Models\ClassModel::whereNotNull('active_question_id')
                ->whereHas('students', function ($q) use ($user) { $q->where('users.id', $user->id); })
                ->pluck('active_question_id')->unique()->toArray();
            $questions = Question::with(['creator','choices'])->whereIn('id', $questionIds)->get();
            $answeredIds = \App\Models\Answer::where('user_id', $user->id)
                ->whereIn('question_id', $questionIds)
                ->pluck('question_id')->toArray();
        } else {
            // docent: own latest questions
            $questions = Question::with('creator')
                ->where('created_by', $user->id)
                ->latest()->take(50)->get();
        }
        return view('user_dashboard', compact('user', 'questions', 'answeredIds'));
    })->name('dashboard');

    // Docent-only question management
    Route::middleware(['verified','docent'])->prefix('docent')->name('docent.')->group(function(){
        Route::get('/questions', [\App\Http\Controllers\QuestionController::class, 'index'])->name('questions.index');
        Route::post('/questions', [\App\Http\Controllers\QuestionController::class, 'store'])->name('questions.store');
        Route::post('/questions/{question}/activate', [\App\Http\Controllers\QuestionController::class, 'activate'])->name('questions.activate');
        Route::post('/classes/{class}/clear', [\App\Http\Controllers\QuestionController::class, 'clearActive'])->name('classes.clear');
        Route::get('/questions/{question}/results', [\App\Http\Controllers\QuestionController::class, 'results'])->name('questions.results');
        Route::post('/questions/{question}/correct', [\App\Http\Controllers\QuestionController::class, 'setCorrect'])->name('questions.setCorrect');
        Route::delete('/questions/{question}', [\App\Http\Controllers\QuestionController::class, 'destroy'])->name('questions.destroy');
    });

    // Answers (students)
    Route::post('/answers', [\App\Http\Controllers\AnswerController::class, 'store'])->name('answers.store');
});

require __DIR__.'/auth.php';
