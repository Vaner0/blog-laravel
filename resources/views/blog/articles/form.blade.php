@extends('layouts.blog')

@section('title', $pageTitle . ' | Blog')

@section('content')
    <section class="journal-form-page journal-container">
        <div class="journal-form-intro">
            <p class="journal-kicker">L'atelier</p>
            <h1>{{ $pageTitle }}</h1>
            <p>Une idée, une observation, un endroit à raconter. Prenez le temps de poser vos mots.</p>
        </div>

        <form action="{{ $formAction }}" method="POST" class="journal-form">
            @csrf
            @if ($formMethod !== 'POST')
                @method($formMethod)
            @endif

            <div class="journal-field">
                <label for="titre">Titre</label>
                <input id="titre" name="titre" type="text" value="{{ old('titre', $article->titre) }}" placeholder="Le titre de votre histoire" required>
            </div>

            <div class="journal-field">
                <label for="contenu">Votre texte</label>
                <textarea id="contenu" name="contenu" rows="15" placeholder="Commencez à écrire..." required>{{ old('contenu', $article->contenu) }}</textarea>
            </div>

            <div class="journal-form-row">
                <div class="journal-field">
                    <label for="statut">Statut</label>
                    <select id="statut" name="statut" required>
                        <option value="brouillon" @selected(old('statut', $article->statut ?: 'brouillon') === 'brouillon')>Brouillon</option>
                        <option value="publie" @selected(old('statut', $article->statut ?: 'brouillon') === 'publie')>Publier maintenant</option>
                    </select>
                </div>
                <div class="journal-field">
                    <label for="image">URL de l'image <span>(optionnel)</span></label>
                    <input id="image" name="image" type="url" value="{{ old('image', $article->image) }}" placeholder="https://..."><p class="journal-field-note">Une image horizontale fonctionne le mieux.</p>
                </div>
            </div>

            <div class="journal-form-actions">
                <a href="{{ $article->exists ? route('blog.articles.show', $article->slug) : route('blog.home') }}" class="journal-button journal-button-quiet">Annuler</a>
                <button type="submit" class="journal-button">{{ $article->exists ? 'Enregistrer les changements' : 'Enregistrer l’article' }}</button>
            </div>
        </form>
    </section>
@endsection
