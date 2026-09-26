<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BlogController::class, 'index'])->name('blog.home');

Route::get('/connexion', [WebAuthController::class, 'showLogin'])->name('blog.login');
Route::get('/connexion/google', [WebAuthController::class, 'redirectToGoogle'])->name('blog.login.google');
Route::get('/connexion/google/callback', [WebAuthController::class, 'handleGoogleCallback'])->name('blog.login.google.callback');
Route::redirect('/login', '/connexion')->name('login');
Route::post('/connexion', [WebAuthController::class, 'login'])->name('blog.login.store');
Route::get('/inscription', [WebAuthController::class, 'showRegister'])->name('blog.register');
Route::post('/inscription', [WebAuthController::class, 'register'])->name('blog.register.store');

Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [WebAuthController::class, 'logout'])->name('blog.logout');

    Route::get('/articles/creer', [BlogController::class, 'create'])->name('blog.articles.create');
    Route::post('/articles', [BlogController::class, 'store'])->name('blog.articles.store');
    Route::get('/articles/{article}/modifier', [BlogController::class, 'edit'])->name('blog.articles.edit');
    Route::put('/articles/{article}', [BlogController::class, 'update'])->name('blog.articles.update');
    Route::delete('/articles/{article}', [BlogController::class, 'destroy'])->name('blog.articles.destroy');
    Route::post('/articles/{article}/commentaires', [BlogController::class, 'storeComment'])->name('blog.comments.store');
    Route::delete('/commentaires/{commentaire}', [BlogController::class, 'destroyComment'])->name('blog.comments.destroy');
});

Route::get('/articles/{slug}', [BlogController::class, 'show'])->name('blog.articles.show');
