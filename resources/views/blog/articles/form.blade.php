@extends('layouts.blog')

@section('title', $pageTitle . ' | Blog')

@section('content')
    <section class="journal-form-page journal-container">
        <div class="journal-form-intro">
            <p class="journal-kicker">L'atelier</p>
            <h1>{{ $pageTitle }}</h1>
            <p>Une idée, une observation, un endroit à raconter. Prenez le temps de poser vos mots.</p>
        </div>

        <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="journal-form">
            @csrf
            @if ($formMethod !== 'POST')
                @method($formMethod)
            @endif
            @php
                $statutInitial = old('statut', $article->statut ?: 'brouillon');
            @endphp

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
                    <select id="statut" name="statut" data-article-status required>
                        <option value="brouillon" @selected($statutInitial === 'brouillon')>Brouillon</option>
                        <option value="publie" @selected($statutInitial === 'publie')>Publier maintenant</option>
                    </select>
                </div>
                <div class="journal-field">
                    <label for="image_fichier">Image de couverture <span>(optionnel)</span></label>
                    <input id="image_fichier" name="image_fichier" type="file" accept="image/jpeg,image/png,image/webp">
                    <p class="journal-field-note">JPEG, PNG ou WebP, 10 Mo maximum. L'image sera redimensionnée et compressée automatiquement.</p>
                    @if ($article->image)
                        <p class="journal-field-note">Une image est déjà associée à cet article.</p>
                    @endif
                </div>
            </div>

            <div class="journal-form-actions">
                <a href="{{ $article->exists ? route('blog.articles.index', ['statut' => $article->statut]) : route('blog.home') }}" class="journal-button journal-button-quiet">Annuler</a>
                <button type="submit" class="journal-button" data-article-submit
                    data-existing="{{ $article->exists ? 'true' : 'false' }}"
                    data-initial-status="{{ $article->statut }}">
                    {{ $article->exists && $article->statut === 'publie' ? 'Enregistrer les modifications' : ($statutInitial === 'publie' ? 'Publier l’article' : 'Enregistrer le brouillon') }}
                </button>
            </div>
        </form>
    </section>
@endsection
