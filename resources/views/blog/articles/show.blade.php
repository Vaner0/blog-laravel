@extends('layouts.blog')

@section('title', $article->titre . ' | Blog')

@section('content')
    <article class="journal-article journal-container">
        <div class="journal-article-header">
            <p class="journal-kicker">{{ $article->statut === 'publie' ? 'Récit publié' : 'Brouillon' }}</p>
            <h1>{{ $article->titre }}</h1>
            <div class="journal-article-meta">
                <span>Par {{ $article->auteur->name }}</span>
                <span>{{ $article->created_at->translatedFormat('j F Y') }}</span>
                @auth
                    @if (auth()->id() === $article->user_id)
                        <a href="{{ route('blog.articles.edit', $article) }}" class="journal-text-link">Modifier</a>
                        <form action="{{ route('blog.articles.destroy', $article) }}" method="POST" class="inline-form" onsubmit="return confirm('Supprimer cet article ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="journal-danger-link">Supprimer</button>
                        </form>
                    @endif
                @endauth
            </div>
        </div>

        <div class="journal-article-layout">
            <div class="journal-article-body">
                @if ($article->image)
                    <img class="journal-article-image" src="{{ $article->image }}" alt="Illustration de {{ $article->titre }}">
                @endif
                <div class="journal-prose">
                    {!! nl2br(e($article->contenu)) !!}
                </div>
            </div>

            <aside class="journal-comment-panel">
                <p class="journal-kicker">La conversation</p>
                <h2>{{ $article->commentaires->count() }} commentaire{{ $article->commentaires->count() > 1 ? 's' : '' }}</h2>

                @auth
                    <form action="{{ route('blog.comments.store', $article) }}" method="POST" class="journal-comment-form">
                        @csrf
                        <label for="contenu">Partager une réflexion</label>
                        <textarea id="contenu" name="contenu" rows="4" placeholder="Votre commentaire..." required>{{ old('contenu') }}</textarea>
                        <button type="submit" class="journal-button">Publier</button>
                    </form>
                @else
                    <p class="journal-muted">Connectez-vous pour participer à la conversation.</p>
                    <a href="{{ route('blog.login') }}" class="journal-text-link">Se connecter</a>
                @endauth

                <div class="journal-comments">
                    @forelse ($article->commentaires as $commentaire)
                        <div class="journal-comment">
                            <div class="journal-comment-topline">
                                <strong>{{ $commentaire->auteur->name }}</strong>
                                <time datetime="{{ $commentaire->created_at->toDateString() }}">{{ $commentaire->created_at->translatedFormat('j M Y') }}</time>
                            </div>
                            <p>{{ $commentaire->contenu }}</p>
                            @auth
                                @if (auth()->id() === $commentaire->user_id)
                                    <form action="{{ route('blog.comments.destroy', $commentaire) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="journal-danger-link">Supprimer</button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    @empty
                        <p class="journal-muted">Pas encore de commentaire. La conversation commence ici.</p>
                    @endforelse
                </div>
            </aside>
        </div>
    </article>
@endsection
