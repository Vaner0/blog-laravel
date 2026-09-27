<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ArticleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_draft_is_saved_and_visible_in_the_draft_list_only(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('blog.articles.store'), [
            'titre' => 'Mon brouillon',
            'contenu' => 'Un récit à terminer.',
            'statut' => 'brouillon',
        ]);

        $response->assertRedirect(route('blog.articles.index', ['statut' => 'brouillon']))
            ->assertSessionHas('success', 'Brouillon enregistré.');
        $this->assertDatabaseHas('articles', [
            'user_id' => $user->id,
            'slug' => 'mon-brouillon',
            'statut' => 'brouillon',
        ]);

        $this->get(route('blog.articles.index', ['statut' => 'brouillon']))
            ->assertOk()
            ->assertSee('Mon brouillon')
            ->assertSee('Publier');
        $this->get(route('blog.home'))->assertDontSee('Mon brouillon');
    }

    public function test_published_article_creation_redirects_to_the_authors_published_list(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('blog.articles.store'), [
            'titre' => 'Mon récit publié',
            'contenu' => 'Un récit prêt à être lu.',
            'statut' => 'publie',
        ])->assertRedirect(route('blog.articles.index', ['statut' => 'publie']))
            ->assertSessionHas('success', 'Article publié avec succès.');

        $this->assertDatabaseHas('articles', [
            'user_id' => $user->id,
            'slug' => 'mon-recit-publie',
            'statut' => 'publie',
        ]);
    }

    public function test_accepts_an_image_upload_larger_than_the_previous_php_post_limit(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->mock(ImageService::class)
            ->shouldReceive('storeArticleImage')
            ->once()
            ->andReturn([
                'url' => 'https://res.cloudinary.com/demo/image/upload/large.webp',
                'public_id' => 'blog/articles/large',
            ]);

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=', true);
        $largeImage = UploadedFile::fake()->createWithContent(
            'couverture.png',
            $png.str_repeat('0', 8_500_000 - strlen($png)),
        );

        $this->post(route('blog.articles.store'), [
            'titre' => 'Article avec une grande image',
            'contenu' => 'La requête dépasse huit mégaoctets.',
            'statut' => 'publie',
            'image_fichier' => $largeImage,
        ])->assertRedirect(route('blog.articles.index', ['statut' => 'publie']))
            ->assertSessionHas('success', 'Article publié avec succès.');

        $this->assertDatabaseHas('articles', [
            'user_id' => $user->id,
            'slug' => 'article-avec-une-grande-image',
            'image_public_id' => 'blog/articles/large',
        ]);
    }

    public function test_draft_can_be_published_from_the_personal_article_list(): void
    {
        $user = User::factory()->create();
        $article = Article::create([
            'user_id' => $user->id,
            'titre' => 'Prêt à publier',
            'contenu' => 'Contenu prêt.',
            'statut' => 'brouillon',
        ]);
        $this->actingAs($user);

        $this->post(route('blog.articles.publish', $article))
            ->assertRedirect(route('blog.articles.index', ['statut' => 'publie']))
            ->assertSessionHas('success', 'Article publié avec succès.');

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'statut' => 'publie',
        ]);
        $this->get(route('blog.home'))->assertSee('Prêt à publier');
    }

    public function test_personal_article_list_only_shows_the_signed_in_users_selected_status(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Article::create([
            'user_id' => $user->id,
            'titre' => 'Mon brouillon',
            'contenu' => 'Privé.',
            'statut' => 'brouillon',
        ]);
        Article::create([
            'user_id' => $user->id,
            'titre' => 'Mon article publié',
            'contenu' => 'Public.',
            'statut' => 'publie',
        ]);
        Article::create([
            'user_id' => $otherUser->id,
            'titre' => 'Brouillon d’un autre auteur',
            'contenu' => 'Privé.',
            'statut' => 'brouillon',
        ]);
        $this->actingAs($user);

        $this->get(route('blog.articles.index', ['statut' => 'brouillon']))
            ->assertOk()
            ->assertSee('Mon brouillon')
            ->assertSee('Brouillons')
            ->assertDontSee('Mon article publié')
            ->assertDontSee('Brouillon d’un autre auteur');
        $this->get(route('blog.articles.index', ['statut' => 'publie']))
            ->assertOk()
            ->assertSee('Mon article publié')
            ->assertSee('Publiés')
            ->assertDontSee('Brouillon d’un autre auteur');
    }

    public function test_user_cannot_publish_another_users_draft(): void
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $article = Article::create([
            'user_id' => $author->id,
            'titre' => 'Brouillon privé',
            'contenu' => 'Contenu privé.',
            'statut' => 'brouillon',
        ]);
        $this->actingAs($otherUser);

        $this->post(route('blog.articles.publish', $article))->assertForbidden();

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'statut' => 'brouillon',
        ]);
    }

    public function test_editing_a_draft_and_selecting_published_publishes_it(): void
    {
        $user = User::factory()->create();
        $article = Article::create([
            'user_id' => $user->id,
            'titre' => 'Brouillon à modifier',
            'contenu' => 'Contenu mis à jour.',
            'statut' => 'brouillon',
        ]);
        $this->actingAs($user);

        $this->put(route('blog.articles.update', $article), [
            'titre' => $article->titre,
            'contenu' => $article->contenu,
            'statut' => 'publie',
        ])->assertRedirect(route('blog.articles.index', ['statut' => 'publie']))
            ->assertSessionHas('success', 'Article publié avec succès.');

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'statut' => 'publie',
        ]);
    }

    public function test_draft_article_is_not_publicly_accessible(): void
    {
        $user = User::factory()->create();
        $article = Article::create([
            'user_id' => $user->id,
            'titre' => 'Brouillon privé',
            'contenu' => 'Contenu privé.',
            'statut' => 'brouillon',
        ]);

        $this->get(route('blog.articles.show', $article->slug))->assertNotFound();
        $this->actingAs($user)
            ->get(route('blog.articles.show', $article->slug))
            ->assertOk()
            ->assertSee('Brouillon privé');
    }

    public function test_guest_is_redirected_to_login_from_the_personal_article_list(): void
    {
        $this->get(route('blog.articles.index'))
            ->assertRedirect(route('login'));
    }

    public function test_article_form_prepares_images_for_browser_compression(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('blog.articles.create'))
            ->assertOk()
            ->assertSee('data-article-form', false)
            ->assertSee('data-article-image', false)
            ->assertSee('data-image-status', false)
            ->assertSee('compressée dans votre navigateur avant l’envoi');
    }
}
