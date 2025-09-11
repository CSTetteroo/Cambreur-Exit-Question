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
use App\Models\Question;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin_dashboard', [UserController::class, 'index'])->name('admin_dashboard');

    // User management
    Route::post('/users/{role}', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Class management
    Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
});

Route::middleware('auth')->group(function () {
    // General user dashboard (students & docenten). Admins are redirected to admin dashboard.
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if ($user && $user->role === 'admin') {
            return redirect()->route('admin_dashboard');
        }
        // For now: all questions. Future: filter by class or availability window.
        $questions = Question::latest()->take(50)->get();
        return view('user_dashboard', [
            'user' => $user,
            'questions' => $questions,
        ]);
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
