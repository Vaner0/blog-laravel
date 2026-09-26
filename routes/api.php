<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentaireController;
use Illuminate\Support\Facades\Route;

Route::post('/inscription', [AuthController::class, 'inscription'])
    ->name('auth.inscription');

Route::post('/connexion', [AuthController::class, 'connexion'])
    ->name('auth.connexion');

Route::middleware('auth:sanctum')->post('/deconnexion', [AuthController::class, 'deconnexion'])
    ->name('auth.deconnexion');

Route::get('/articles', [ArticleController::class, 'index'])
    ->name('articles.index');

Route::get('/articles/{slug}', [ArticleController::class, 'show'])
    ->name('articles.show');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/articles', [ArticleController::class, 'store'])
        ->name('articles.store');

    Route::put('/articles/{article}', [ArticleController::class, 'update'])
        ->name('articles.update');

    Route::delete('/articles/{article}', [ArticleController::class, 'destroy'])
        ->name('articles.destroy');

    Route::post('/articles/{article}/commentaires', [CommentaireController::class, 'store'])
        ->name('commentaires.store');

    Route::delete('/commentaires/{commentaire}', [CommentaireController::class, 'destroy'])
        ->name('commentaires.destroy');
});
