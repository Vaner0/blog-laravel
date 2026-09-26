<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Blog, un espace pour ralentir et regarder le monde autrement.">
    <title>@yield('title', 'Blog')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="journal-shell">
    <header class="journal-header">
        <div class="journal-container journal-nav">
            <a href="{{ route('blog.home') }}" class="journal-brand">Blog</a>
            <nav class="journal-links" aria-label="Navigation principale">
                <a href="{{ route('blog.home') }}">Accueil</a>
                <a href="{{ route('blog.home') }}#articles">Articles</a>
                <a href="{{ route('blog.home') }}#a-propos">À propos</a>
                @auth
                    <a href="{{ route('blog.articles.index') }}">Mes articles</a>
                    <a href="{{ route('blog.articles.create') }}" class="journal-nav-accent">Écrire</a>
                    <form action="{{ route('blog.logout') }}" method="POST" class="inline-form">
                        @csrf
                        <button type="submit" class="journal-link-button">Déconnexion</button>
                    </form>
                @else
                    <a href="{{ route('blog.login') }}">Connexion</a>
                @endauth
            </nav>
        </div>
    </header>

    @if (session('success'))
        <div class="journal-container journal-flash" role="status" data-flash>{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="journal-container journal-errors" role="alert">
            <strong>Vérifie les informations saisies.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer class="journal-footer">
        <div class="journal-container journal-footer-grid">
            <div>
                <a href="{{ route('blog.home') }}" class="journal-footer-brand">Blog</a>
                <p>Des récits pour prendre le temps de regarder,<br>comprendre et imaginer.</p>
            </div>
            <div>
                <p class="journal-footer-heading">Explorer</p>
                <a href="{{ route('blog.home') }}">Accueil</a>
                <a href="{{ route('blog.home') }}#articles">Articles</a>
                <a href="{{ route('blog.home') }}#a-propos">À propos</a>
            </div>
            <div>
                <p class="journal-footer-heading">Suivez-nous</p>
                <a href="#">Instagram</a>
                <a href="#">LinkedIn</a>
                <a href="#">Facebook</a>
            </div>
        </div>
    </footer>
</body>
</html>
