<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Commentaire;
use App\Services\ArticleService;
use App\Services\CommentaireService;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(ArticleService $articleService): View
    {
        $articles = $articleService->listerArticles();

        return view('blog.home', [
            'articleVedette' => $articles->first(),
            'articlesRecents' => $articles->skip(1),
        ]);
    }

    public function show(string $slug, ArticleService $articleService): View
    {
        return view('blog.articles.show', [
            'article' => $articleService->voirArticle($slug),
        ]);
    }

    public function create(): View
    {
        return view('blog.articles.form', [
            'article' => new Article,
            'formAction' => route('blog.articles.store'),
            'formMethod' => 'POST',
            'pageTitle' => 'Écrire un article',
        ]);
    }

    public function store(
        Request $request,
        ArticleService $articleService,
        ImageService $imageService,
    ): RedirectResponse {
        $donneesArticle = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'contenu' => ['required', 'string'],
            'statut' => ['required', 'in:brouillon,publie'],
            'image' => ['nullable', 'url', 'max:2048'],
            'image_fichier' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        if ($request->hasFile('image_fichier')) {
            $donneesArticle['image'] = $imageService->storeArticleImage($request->file('image_fichier'));
        }

        $article = $articleService->creerArticle($donneesArticle, $request->user()->id);

        return redirect()
            ->route('blog.articles.show', $article->slug)
            ->with('success', 'Article publié avec succès.');
    }

    public function edit(Article $article): View
    {
        abort_unless($article->user_id === auth()->id(), 403);

        return view('blog.articles.form', [
            'article' => $article,
            'formAction' => route('blog.articles.update', $article),
            'formMethod' => 'PUT',
            'pageTitle' => 'Modifier l’article',
        ]);
    }

    public function update(
        Request $request,
        Article $article,
        ArticleService $articleService,
        ImageService $imageService,
    ): RedirectResponse {
        abort_unless($article->user_id === $request->user()->id, 403);

        $donneesArticle = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'contenu' => ['required', 'string'],
            'statut' => ['required', 'in:brouillon,publie'],
            'image' => ['nullable', 'url', 'max:2048'],
            'image_fichier' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        if ($request->hasFile('image_fichier')) {
            $oldImage = $article->image;
            $donneesArticle['image'] = $imageService->storeArticleImage($request->file('image_fichier'));
            $imageService->delete($oldImage);
        }

        $articleService->modifierArticle($article, $donneesArticle);

        return redirect()
            ->route('blog.articles.show', $article->slug)
            ->with('success', 'Article mis à jour.');
    }

    public function destroy(
        Request $request,
        Article $article,
        ArticleService $articleService,
        ImageService $imageService,
    ): RedirectResponse {
        abort_unless($article->user_id === $request->user()->id, 403);

        $imageService->delete($article->image);
        $articleService->supprimerArticle($article);

        return redirect()
            ->route('blog.home')
            ->with('success', 'Article supprimé.');
    }

    public function storeComment(
        Request $request,
        Article $article,
        CommentaireService $commentaireService,
    ): RedirectResponse {
        $donneesCommentaire = $request->validate([
            'contenu' => ['required', 'string', 'max:2000'],
        ]);

        $commentaireService->creerCommentaire(
            $donneesCommentaire,
            $request->user()->id,
            $article->id,
        );

        return redirect()
            ->route('blog.articles.show', $article->slug)
            ->with('success', 'Commentaire ajouté.');
    }

    public function destroyComment(
        Request $request,
        Commentaire $commentaire,
        CommentaireService $commentaireService,
    ): RedirectResponse {
        abort_unless($commentaire->user_id === $request->user()->id, 403);

        $articleSlug = $commentaire->article->slug;
        $commentaireService->supprimerCommentaire($commentaire);

        return redirect()
            ->route('blog.articles.show', $articleSlug)
            ->with('success', 'Commentaire supprimé.');
    }
}
