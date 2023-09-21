<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SectionController;

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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    //books routes
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::get('/books/show/{book}', [BookController::class, 'show'])->name('books.show');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

    Route::get('/books/sections/{book}', [BookController::class, 'sections'])->name('books.sections');
    Route::get('/books/sub/sections/{book}/{section}', [BookController::class, 'subSections'])->name('books.subsections');

    //Section routes
    Route::post('section/store', [SectionController::class, 'store'])->name('sections.store');
    Route::post('sub/section/store', [SectionController::class, 'storeSubSection'])->name('sub.sections.store');
    Route::get('/sections/{section}', [SectionController::class, 'getSection'])->name('sections.get');
    Route::put('/sections/{section}', [SectionController::class, 'update'])->name('sections.update');

});

require __DIR__.'/auth.php';
