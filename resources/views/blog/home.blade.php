@extends('layouts.blog')

@section('title', 'Blog | Des histoires pour faire une pause')

@section('content')
    @php
        $images = [
            'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=1400&q=85',
            'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=900&q=85',
            'https://images.unsplash.com/photo-1516979187457-637abb4f9353?auto=format&fit=crop&w=900&q=85',
            'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=900&q=85',
            'https://images.unsplash.com/photo-1511818966892-d7d671e672a2?auto=format&fit=crop&w=900&q=85',
            'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=900&q=85',
            'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=900&q=85',
        ];
    @endphp

    <section class="journal-hero journal-container">
        <div class="journal-hero-copy">
            <p class="journal-kicker">Le récit de la semaine</p>
            @if ($articleVedette)
                <h1>{{ $articleVedette->titre }}</h1>
                <p class="journal-lede">{{ \Illuminate\Support\Str::limit($articleVedette->contenu, 180) }}</p>
                <div class="journal-meta-row">
                    <a href="{{ route('blog.articles.show', $articleVedette->slug) }}" class="journal-button">Lire l'article</a>
                    <span>Par {{ $articleVedette->auteur->name }} · 8 min de lecture</span>
                </div>
            @else
                <h1>Le temps retrouvé, loin du bruit</h1>
                <p class="journal-lede">Des histoires sensibles pour regarder le monde avec un peu plus d’attention.</p>
                @guest
                    <a href="{{ route('blog.register') }}" class="journal-button">Rejoindre le Blog</a>
                @endguest
            @endif
        </div>
        <div class="journal-hero-media">
            <img src="{{ $articleVedette?->image ? (\Illuminate\Support\Str::startsWith($articleVedette->image, ['http://', 'https://']) ? $articleVedette->image : \Illuminate\Support\Facades\Storage::disk('public')->url($articleVedette->image)) : $images[0] }}" alt="Une personne lisant un journal">
            <p class="journal-image-caption">Chroniques du quotidien</p>
        </div>
    </section>

    <section id="articles" class="journal-section journal-container">
        <div class="journal-section-heading">
            <div>
                <p class="journal-kicker">À lire maintenant</p>
                <h2>Articles récents</h2>
            </div>
            <p>Des idées, des lieux et des voix pour regarder le monde autrement.</p>
        </div>

        @if ($articlesRecents->isNotEmpty())
            <div class="journal-grid">
                @foreach ($articlesRecents as $article)
                    <article class="journal-card">
                        <a href="{{ route('blog.articles.show', $article->slug) }}" class="journal-card-link">
                            <img src="{{ $article->image ? (\Illuminate\Support\Str::startsWith($article->image, ['http://', 'https://']) ? $article->image : \Illuminate\Support\Facades\Storage::disk('public')->url($article->image)) : $images[($loop->index + 1) % count($images)] }}" alt="Illustration de {{ $article->titre }}">
                            <div class="journal-card-body">
                                <p class="journal-kicker">{{ ['Voyages', 'Culture', 'Nature', 'Design', 'À table', 'Création'][$loop->index % 6] }}</p>
                                <h3>{{ $article->titre }}</h3>
                                <p>{{ \Illuminate\Support\Str::limit($article->contenu, 105) }}</p>
                                <div class="journal-card-footer">
                                    <time datetime="{{ $article->created_at->toDateString() }}">{{ $article->created_at->translatedFormat('j F Y') }}</time>
                                    <span>Lire l'article</span>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="journal-empty">
                <p>Aucun article publié pour le moment.</p>
                @auth
                    <a href="{{ route('blog.articles.create') }}" class="journal-text-link">Écrire le premier récit</a>
                @endauth
            </div>
        @endif
    </section>

    <section id="a-propos" class="journal-about">
        <div class="journal-container journal-about-inner">
            <p class="journal-kicker">Notre regard</p>
            <div>
                <h2>Des histoires qui invitent à faire une pause.</h2>
                <p>Le Blog explore les petites et grandes choses qui dessinent notre époque. Nous croyons aux récits sensibles, aux idées qui circulent et aux détails qui transforment une journée ordinaire.</p>
            </div>
        </div>
    </section>

    <section class="journal-newsletter journal-container">
        <div>
            <p class="journal-kicker journal-kicker-light">La lettre du dimanche</p>
            <h2>Un peu de lecture, directement dans votre boîte mail.</h2>
            <p>Chaque semaine, notre sélection d'articles, de découvertes et de bonnes nouvelles à savourer tranquillement.</p>
        </div>
        <form class="journal-newsletter-form" action="#" method="GET">
            <label for="newsletter-email">Votre adresse e-mail</label>
            <div>
                <input id="newsletter-email" type="email" placeholder="vous@exemple.com" aria-label="Votre adresse e-mail">
                <button type="submit" class="journal-button journal-button-gold">Je m'inscris</button>
            </div>
        </form>
    </section>
@endsection
