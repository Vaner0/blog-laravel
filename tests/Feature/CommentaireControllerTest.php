<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Commentaire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentaireControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_comment(): void
    {
        $author = User::factory()->create();
        $commentAuthor = User::factory()->create();
        $article = Article::create([
            'user_id' => $author->id,
            'titre' => 'Article commentable',
            'contenu' => "Contenu de l'article.",
            'statut' => 'publie',
        ]);
        Sanctum::actingAs($commentAuthor);

        $response = $this->postJson("/api/articles/{$article->id}/commentaires", [
            'contenu' => 'Très bon article.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('contenu', 'Très bon article.')
            ->assertJsonPath('auteur.id', $commentAuthor->id);

        $this->assertDatabaseHas('commentaires', [
            'article_id' => $article->id,
            'user_id' => $commentAuthor->id,
            'contenu' => 'Très bon article.',
        ]);
    }

    public function test_user_cannot_delete_another_users_comment(): void
    {
        $articleAuthor = User::factory()->create();
        $commentAuthor = User::factory()->create();
        $otherUser = User::factory()->create();
        $article = Article::create([
            'user_id' => $articleAuthor->id,
            'titre' => 'Article commenté',
            'contenu' => "Contenu de l'article.",
        ]);
        $commentaire = Commentaire::create([
            'article_id' => $article->id,
            'user_id' => $commentAuthor->id,
            'contenu' => 'Commentaire à protéger.',
        ]);
        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/commentaires/{$commentaire->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('commentaires', [
            'id' => $commentaire->id,
        ]);
    }

    public function test_comment_author_can_delete_their_comment(): void
    {
        $articleAuthor = User::factory()->create();
        $commentAuthor = User::factory()->create();
        $article = Article::create([
            'user_id' => $articleAuthor->id,
            'titre' => 'Article commenté',
            'contenu' => "Contenu de l'article.",
        ]);
        $commentaire = Commentaire::create([
            'article_id' => $article->id,
            'user_id' => $commentAuthor->id,
            'contenu' => 'Commentaire à supprimer.',
        ]);
        Sanctum::actingAs($commentAuthor);

        $response = $this->deleteJson("/api/commentaires/{$commentaire->id}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Commentaire supprimé avec succès.',
            ]);
        $this->assertDatabaseMissing('commentaires', [
            'id' => $commentaire->id,
        ]);
    }
}
