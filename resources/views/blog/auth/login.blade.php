@extends('layouts.blog')

@section('title', 'Connexion | Blog')

@section('content')
    <section class="journal-auth-page journal-container">
        <div class="journal-auth-copy">
            <p class="journal-kicker">Bienvenue à nouveau</p>
            <h1>Retrouver le fil.</h1>
            <p>Connectez-vous pour écrire, commenter et garder une trace des histoires qui vous accompagnent.</p>
        </div>
        <form action="{{ route('blog.login.store') }}" method="POST" class="journal-form journal-auth-form">
            @csrf
            <div class="journal-field">
                <label for="email">Adresse e-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
            </div>
            <div class="journal-field">
                <label for="mot_de_passe">Mot de passe</label>
                <input id="mot_de_passe" name="mot_de_passe" type="password" autocomplete="current-password" required>
            </div>
            <button type="submit" class="journal-button">Se connecter</button>
            <p class="journal-form-footnote">Pas encore de compte ? <a href="{{ route('blog.register') }}" class="journal-text-link">Créer un compte</a></p>
        </form>
    </section>
@endsection
