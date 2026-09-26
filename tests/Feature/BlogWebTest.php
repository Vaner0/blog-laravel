<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_published_articles(): void
    {
        $user = User::factory()->create();
        Article::create([
            'user_id' => $user->id,
            'titre' => 'Une histoire visible',
            'contenu' => 'Un contenu publié pour la page d accueil.',
            'statut' => 'publie',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Une histoire visible')
            ->assertSee('Blog');
    }

    public function test_authenticated_user_can_create_an_article_from_the_web_form(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/articles', [
            'titre' => 'Article créé depuis le web',
            'contenu' => 'Contenu de l article créé depuis le formulaire.',
            'statut' => 'publie',
        ]);

        $response->assertRedirect(route('blog.articles.index', ['statut' => 'publie']))
            ->assertSessionHas('success', 'Article publié avec succès.');
        $this->assertDatabaseHas('articles', [
            'user_id' => $user->id,
            'slug' => 'article-cree-depuis-le-web',
            'statut' => 'publie',
        ]);
    }

    public function test_authenticated_user_can_add_a_comment_from_the_web_form(): void
    {
        $author = User::factory()->create();
        $commentAuthor = User::factory()->create();
        $article = Article::create([
            'user_id' => $author->id,
            'titre' => 'Article avec conversation',
            'contenu' => 'Contenu de l article.',
            'statut' => 'publie',
        ]);
        $this->actingAs($commentAuthor);

        $response = $this->post("/articles/{$article->id}/commentaires", [
            'contenu' => 'Une réaction depuis le web.',
        ]);

        $response->assertRedirect(route('blog.articles.show', $article->slug));
        $this->assertDatabaseHas('commentaires', [
            'article_id' => $article->id,
            'user_id' => $commentAuthor->id,
            'contenu' => 'Une réaction depuis le web.',
        ]);
    }
}
