<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_and_registration_pages(): void
    {
        $this->get('/connexion')
            ->assertOk()
            ->assertSee('Retrouver le fil.')
            ->assertSee('Continuer avec Google');

        $this->get('/connexion/google')
            ->assertRedirect();

        $this->get('/inscription')
            ->assertOk()
            ->assertSee('Faire une place aux idées.');
    }

    public function test_guest_is_redirected_to_login_from_a_protected_page(): void
    {
        $this->get('/articles/creer')
            ->assertRedirect('/login');
    }

    public function test_registration_logs_the_user_in_and_redirects_home(): void
    {
        $response = $this->post('/inscription', [
            'nom' => 'Nora Dupont',
            'email' => 'nora@example.com',
            'mot_de_passe' => 'password123',
            'mot_de_passe_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('blog.home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'nora@example.com',
            'name' => 'Nora Dupont',
        ]);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'nora@example.com',
            'password' => 'password123',
        ]);

        $response = $this->from('/connexion')->post('/connexion', [
            'email' => 'nora@example.com',
            'mot_de_passe' => 'wrong-password',
        ]);

        $response->assertRedirect('/connexion')
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
