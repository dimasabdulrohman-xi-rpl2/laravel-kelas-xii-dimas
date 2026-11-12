<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\KritikController;
use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [FilmController::class, 'movieHome'])->name('home');
Route::get('/login', function (Request $request) {
    if ($request->filled('film_id')) {
        $validated = $request->validate([
            'film_id' => ['integer', 'exists:films,id'],
        ]);

        $request->session()->put(
            'url.intended',
            route('movies.show', Film::findOrFail($validated['film_id']))
        );
    }

    return redirect()->route('home')->with('auth_modal', 'login');
})->name('login');
Route::get('/register', fn () => redirect()->route('home')->with('auth_modal', 'register'))->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login.authenticate');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/films', [FilmController::class, 'adminDashboard'])->name('films.index');
    Route::get('/films/create', [FilmController::class, 'create'])->name('films.create');
    Route::post('/films', [FilmController::class, 'store'])->name('films.store');
    Route::get('/films/{film}/edit', [FilmController::class, 'edit'])->name('films.edit');
    Route::put('/films/{film}', [FilmController::class, 'update'])->name('films.update');
    Route::delete('/films/{film}', [FilmController::class, 'destroy'])->name('films.destroy');
    Route::get('/casts', [AdminController::class, 'casts'])->name('casts.index');
    Route::post('/casts', [AdminController::class, 'storeCast'])->name('casts.store');
    Route::put('/casts/{cast}', [AdminController::class, 'updateCast'])->name('casts.update');
    Route::delete('/casts/{cast}', [AdminController::class, 'destroyCast'])->name('casts.destroy');
    Route::get('/comments', [AdminController::class, 'comments'])->name('comments.index');
    Route::delete('/comments/{kritik}', [AdminController::class, 'destroyComment'])->name('comments.destroy');
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
});
Route::post('/movies/{film}/comments', [KritikController::class, 'store'])
    ->middleware('auth')
    ->name('comments.store');
Route::get('/movies', [FilmController::class, 'movies'])->name('movies');
Route::get('/movies/{film}', [FilmController::class, 'show'])->name('movies.show');
Route::get('/movies/genre/{genre}', [FilmController::class, 'moviesByGenre'])->name('genre');
