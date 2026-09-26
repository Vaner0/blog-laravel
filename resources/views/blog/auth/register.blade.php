@extends('layouts.blog')

@section('title', 'Inscription | Blog')

@section('content')
    <section class="journal-auth-page journal-container">
        <div class="journal-auth-copy">
            <p class="journal-kicker">Le Blog, c'est aussi vous</p>
            <h1>Faire une place aux idées.</h1>
            <p>Créez votre compte pour publier vos récits et prendre part aux conversations qui comptent.</p>
        </div>
        <form action="{{ route('blog.register.store') }}" method="POST" class="journal-form journal-auth-form">
            @csrf
            <div class="journal-field">
                <label for="nom">Nom</label>
                <input id="nom" name="nom" type="text" value="{{ old('nom') }}" autocomplete="name" required>
            </div>
            <div class="journal-field">
                <label for="email">Adresse e-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
            </div>
            <div class="journal-field">
                <label for="mot_de_passe">Mot de passe</label>
                <input id="mot_de_passe" name="mot_de_passe" type="password" autocomplete="new-password" required>
            </div>
            <div class="journal-field">
                <label for="mot_de_passe_confirmation">Confirmer le mot de passe</label>
                <input id="mot_de_passe_confirmation" name="mot_de_passe_confirmation" type="password" autocomplete="new-password" required>
            </div>
            <button type="submit" class="journal-button">Créer mon compte</button>
            <p class="journal-form-footnote">Déjà membre ? <a href="{{ route('blog.login') }}" class="journal-text-link">Se connecter</a></p>
        </form>
    </section>
@endsection
