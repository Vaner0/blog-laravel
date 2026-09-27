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

    public function show(Request $request, string $slug, ArticleService $articleService): View
    {
        $article = $articleService->voirArticle($slug);

        abort_if(
            $article->statut !== 'publie' && $request->user()?->id !== $article->user_id,
            404,
        );

        return view('blog.articles.show', [
            'article' => $article,
        ]);
    }

    public function mesArticles(Request $request, ArticleService $articleService): View
    {
        $donnees = $request->validate([
            'statut' => ['sometimes', 'in:brouillon,publie'],
        ]);
        $statut = $donnees['statut'] ?? 'brouillon';
        $user = $request->user();

        return view('blog.articles.index', [
            'articles' => $articleService->listerArticlesUtilisateur($user, $statut),
            'statut' => $statut,
            'totaux' => $articleService->compterArticlesUtilisateurParStatut($user),
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
            'image_fichier' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:10240'],
        ]);

        if ($request->hasFile('image_fichier')) {
            $image = $imageService->storeArticleImage($request->file('image_fichier'));
            $donneesArticle['image'] = $image['url'];
            $donneesArticle['image_public_id'] = $image['public_id'];
        }

        $article = $articleService->creerArticle($donneesArticle, $request->user()->id);
        $statut = $article->statut;

        return redirect()
            ->route('blog.articles.index', ['statut' => $statut])
            ->with('success', $statut === 'publie' ? 'Article publié avec succès.' : 'Brouillon enregistré.');
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
            'image_fichier' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:10240'],
        ]);

        $oldImage = $article->image;
        $oldImagePublicId = $article->image_public_id;
        $ancienStatut = $article->statut;

        if ($request->hasFile('image_fichier')) {
            $image = $imageService->storeArticleImage($request->file('image_fichier'));
            $donneesArticle['image'] = $image['url'];
            $donneesArticle['image_public_id'] = $image['public_id'];
        }

        $articleService->modifierArticle($article, $donneesArticle);

        if ($request->hasFile('image_fichier')) {
            $imageService->delete($oldImage, $oldImagePublicId);
        }

        $statut = $article->statut;
        $message = match (true) {
            $ancienStatut !== 'publie' && $statut === 'publie' => 'Article publié avec succès.',
            $statut === 'brouillon' => 'Brouillon enregistré.',
            default => 'Article mis à jour.',
        };

        return redirect()
            ->route('blog.articles.index', ['statut' => $statut])
            ->with('success', $message);
    }

    public function publier(
        Request $request,
        Article $article,
        ArticleService $articleService,
    ): RedirectResponse {
        abort_unless($article->user_id === $request->user()->id, 403);

        if ($article->statut !== 'publie') {
            $articleService->modifierArticle($article, ['statut' => 'publie']);
        }

        return redirect()
            ->route('blog.articles.index', ['statut' => 'publie'])
            ->with('success', 'Article publié avec succès.');
    }

    public function destroy(
        Request $request,
        Article $article,
        ArticleService $articleService,
        ImageService $imageService,
    ): RedirectResponse {
        abort_unless($article->user_id === $request->user()->id, 403);

        $image = $article->image;
        $imagePublicId = $article->image_public_id;
        $statut = $article->statut;
        $articleService->supprimerArticle($article);
        $imageService->delete($image, $imagePublicId);

        return redirect()
            ->route('blog.articles.index', ['statut' => $statut])
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
