<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_registration_creates_a_user_and_returns_a_token(): void
    {
        $response = $this->postJson('/api/inscription', [
            'nom' => 'Alice Martin',
            'email' => 'alice@example.com',
            'mot_de_passe' => 'password123',
            'mot_de_passe_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Alice Martin',
            'email' => 'alice@example.com',
        ]);
    }

    public function test_login_returns_a_token_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'alice@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/connexion', [
            'email' => $user->email,
            'mot_de_passe' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'user',
                'token',
            ]);
    }

    public function test_logout_deletes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        $response = $this->withToken($token->plainTextToken)
            ->postJson('/api/deconnexion');

        $response->assertOk()
            ->assertJson([
                'message' => 'Déconnexion réussie.',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }
}
