@extends('layouts.blog')

@section('title', 'Mes articles | Blog')

@section('content')
    <section class="journal-management journal-container">
        <div class="journal-management-heading">
            <div>
                <p class="journal-kicker">Votre espace d’écriture</p>
                <h1>Mes articles</h1>
                <p>Retrouvez vos brouillons et gérez les récits que vous avez publiés.</p>
            </div>
            <a href="{{ route('blog.articles.create') }}" class="journal-button">Écrire un article</a>
        </div>

        <nav class="journal-article-tabs" aria-label="Filtrer mes articles">
            <a href="{{ route('blog.articles.index', ['statut' => 'brouillon']) }}"
                @class(['is-active' => $statut === 'brouillon'])
                @if ($statut === 'brouillon') aria-current="page" @endif>
                Brouillons <span>{{ $totaux->get('brouillon', 0) }}</span>
            </a>
            <a href="{{ route('blog.articles.index', ['statut' => 'publie']) }}"
                @class(['is-active' => $statut === 'publie'])
                @if ($statut === 'publie') aria-current="page" @endif>
                Publiés <span>{{ $totaux->get('publie', 0) }}</span>
            </a>
        </nav>

        <div class="journal-management-list">
            @forelse ($articles as $article)
                <article class="journal-management-card">
                    @if ($article->image)
                        <img src="{{ \Illuminate\Support\Str::startsWith($article->image, ['http://', 'https://']) ? $article->image : \Illuminate\Support\Facades\Storage::disk('public')->url($article->image) }}"
                            alt="" loading="lazy">
                    @endif

                    <div class="journal-management-copy">
                        <div class="journal-management-meta">
                            <span class="journal-status {{ $statut === 'publie' ? 'journal-status-published' : 'journal-status-draft' }}">
                                {{ $statut === 'publie' ? 'Publié' : 'Brouillon' }}
                            </span>
                            <time datetime="{{ $article->updated_at->toDateString() }}">
                                Modifié le {{ $article->updated_at->translatedFormat('j F Y') }}
                            </time>
                        </div>
                        <h2><a href="{{ route('blog.articles.show', $article->slug) }}">{{ $article->titre }}</a></h2>
                        <p>{{ \Illuminate\Support\Str::limit($article->contenu, 150) }}</p>
                    </div>

                    <div class="journal-management-actions">
                        @if ($statut === 'brouillon')
                            <form action="{{ route('blog.articles.publish', $article) }}" method="POST">
                                @csrf
                                <button type="submit" class="journal-button">Publier</button>
                            </form>
                        @else
                            <a href="{{ route('blog.articles.show', $article->slug) }}" class="journal-button journal-button-quiet">Voir</a>
                        @endif
                        <a href="{{ route('blog.articles.edit', $article) }}" class="journal-control journal-control-edit">Modifier</a>
                        <form action="{{ route('blog.articles.destroy', $article) }}" method="POST" class="inline-form"
                            onsubmit="return confirm('Supprimer cet article ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="journal-control journal-control-delete">Supprimer</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="journal-empty">
                    @if ($statut === 'brouillon')
                        <p>Vous n’avez pas encore de brouillon.</p>
                        <a href="{{ route('blog.articles.create') }}" class="journal-text-link">Commencer à écrire</a>
                    @else
                        <p>Vous n’avez pas encore publié d’article.</p>
                        <a href="{{ route('blog.articles.create') }}" class="journal-text-link">Écrire votre premier récit</a>
                    @endif
                </div>
            @endforelse
        </div>

        {{ $articles->links() }}
    </section>
@endsection
