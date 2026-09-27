<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Page introuvable | Blog</title>
    <style>
        * { box-sizing: border-box; }
        body { display: grid; min-height: 100vh; margin: 0; padding: 1.5rem; place-items: center; background: #f7f3ec; color: #24352d; font-family: Arial, sans-serif; }
        main { width: min(100%, 34rem); text-align: center; }
        p { color: #687067; line-height: 1.6; }
        .code { color: #bd5a42; font-size: .75rem; font-weight: 700; letter-spacing: .25em; }
        h1 { margin: .8rem 0; font-family: Georgia, serif; font-size: clamp(2.5rem, 8vw, 4rem); letter-spacing: -.05em; }
        a { display: inline-block; margin-top: 1rem; padding: .8rem 1.2rem; border-radius: 999px; background: #bd5a42; color: #fffaf3; font-size: .85rem; font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>
    <main>
        <p class="code">ERREUR 404</p>
        <h1>Cette page est introuvable.</h1>
        <p>Le lien est peut-être incorrect ou cette page n’est plus disponible.</p>
        <a href="{{ url('/') }}">Revenir à l’accueil</a>
    </main>
</body>
</html>
