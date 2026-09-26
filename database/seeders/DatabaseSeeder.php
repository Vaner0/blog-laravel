<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Commentaire;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $utilisateurDemo = User::updateOrCreate(
            ['email' => 'demo@blog.test'],
            [
                'name' => 'Camille Martin',
                'password' => Hash::make('blog12345'),
                'email_verified_at' => now(),
            ],
        );

        $utilisateurLecteur = User::updateOrCreate(
            ['email' => 'lecteur@blog.test'],
            [
                'name' => 'Alex Bernard',
                'password' => Hash::make('blog12345'),
                'email_verified_at' => now(),
            ],
        );

        $articles = [
            [
                'titre' => 'Le temps retrouvé, loin du bruit',
                'contenu' => 'Dans un monde qui accélère, certains choisissent de ralentir. Rencontrer avec attention, marcher sans itinéraire et laisser une place aux détails change notre manière d habiter chaque journée.',
                'image' => 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=1400&q=85',
                'created_at' => Carbon::parse('2026-09-18'),
            ],
            [
                'titre' => 'Le cafe comme dernier salon litteraire',
                'contenu' => 'A l heure des ecrans, quelques tables, un livre et un espresso suffisent encore a creer des rencontres inattendues.',
                'image' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=900&q=85',
                'created_at' => Carbon::parse('2026-09-14'),
            ],
            [
                'titre' => 'Cultiver un jardin, meme minuscule',
                'contenu' => 'Balcon, fenetre ou cour interieure : la verdure reprend sa place dans nos vies et nous apprend a regarder autrement.',
                'image' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=900&q=85',
                'created_at' => Carbon::parse('2026-09-09'),
            ],
            [
                'titre' => 'Habiter les formes de demain',
                'contenu' => 'Quand l architecture compose avec la lumiere, les usages et le desir de mieux vivre ensemble, elle devient une experience quotidienne.',
                'image' => 'https://images.unsplash.com/photo-1511818966892-d7d671e672a2?auto=format&fit=crop&w=900&q=85',
                'created_at' => Carbon::parse('2026-09-03'),
            ],
            [
                'titre' => 'La cuisine du placard fait sa revolution',
                'contenu' => 'Moins de gaspillage, plus d inventivite : trois cuisiniers racontent leur art d improviser avec ce qui reste.',
                'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=900&q=85',
                'created_at' => Carbon::parse('2026-08-29'),
            ],
            [
                'titre' => 'Dans l atelier, le geste avant les mots',
                'contenu' => 'Visite chez des artistes qui defendent la lenteur, la matiere et le plaisir de faire de leurs mains.',
                'image' => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=900&q=85',
                'created_at' => Carbon::parse('2026-08-22'),
            ],
            [
                'titre' => 'Preparer la rentree avec douceur',
                'contenu' => 'Un brouillon reserve aux prochains recits du Blog.',
                'image' => null,
                'statut' => 'brouillon',
                'created_at' => Carbon::parse('2026-09-20'),
            ],
        ];

        $articlesCrees = [];

        foreach ($articles as $donneesArticle) {
            $article = Article::updateOrCreate(
                ['slug' => Str::slug($donneesArticle['titre'])],
                [
                    'user_id' => $utilisateurDemo->id,
                    'titre' => $donneesArticle['titre'],
                    'contenu' => $donneesArticle['contenu'],
                    'image' => $donneesArticle['image'],
                    'statut' => $donneesArticle['statut'] ?? 'publie',
                ],
            );

            $article->created_at = $donneesArticle['created_at'];
            $article->saveQuietly();
            $articlesCrees[$article->slug] = $article;
        }

        $commentaires = [
            [
                'article' => 'le-temps-retrouve-loin-du-bruit',
                'contenu' => 'Un texte qui donne envie de ralentir pour de vrai.',
            ],
            [
                'article' => 'le-cafe-comme-dernier-salon-litteraire',
                'contenu' => 'Je reconnais exactement cette atmosphere dans mon cafe de quartier.',
            ],
            [
                'article' => 'cultiver-un-jardin-meme-minuscule',
                'contenu' => 'Meme quelques herbes aromatiques changent deja un balcon.',
            ],
        ];

        foreach ($commentaires as $donneesCommentaire) {
            Commentaire::updateOrCreate(
                [
                    'article_id' => $articlesCrees[$donneesCommentaire['article']]->id,
                    'user_id' => $utilisateurLecteur->id,
                    'contenu' => $donneesCommentaire['contenu'],
                ],
            );
        }
    }
}
