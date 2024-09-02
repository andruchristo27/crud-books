<?php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::resource('books', BookController::class);
Route::get('books/show/{id}', [BookController::class, 'show'])->name('books.show');
Route::post('books/store', [BookController::class, 'store'])->name('books.store');
Route::get('books/edit/{id}', [BookController::class, 'edit'])->name('books.edit');
Route::post('books/update/{id}', [BookController::class, 'update'])->name('books.update');
Route::delete('books/destroy/{id}', [BookController::class, 'destroy'])->name('books.destroy');
