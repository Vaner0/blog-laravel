<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ExceptionHandlingTest extends TestCase
{
    public function test_unexpected_web_exception_shows_a_safe_server_error_page(): void
    {
        config(['app.debug' => false]);

        Route::get('/_testing/server-error', function (): never {
            throw new RuntimeException('Sensitive internal exception details.');
        });

        $this->get('/_testing/server-error')
            ->assertServerError()
            ->assertSee('Un problème est survenu.')
            ->assertDontSee('Sensitive internal exception details.')
            ->assertDontSee('RuntimeException');
    }

    public function test_returns_500_json_when_an_unexpected_api_exception_occurs(): void
    {
        config(['app.debug' => false]);

        Route::get('/api/_testing/server-error', function (): never {
            throw new RuntimeException('Sensitive internal exception details.');
        });

        $this->getJson('/api/_testing/server-error')
            ->assertServerError()
            ->assertJsonPath('message', 'Server Error')
            ->assertDontSee('Sensitive internal exception details.');
    }

    public function test_missing_page_shows_the_not_found_page(): void
    {
        config(['app.debug' => false]);

        Route::get('/_testing/not-found', function (): never {
            abort(404);
        });

        $this->get('/_testing/not-found')
            ->assertNotFound()
            ->assertSee('Cette page est introuvable.');
    }

    public function test_unavailable_service_shows_the_retry_page(): void
    {
        config(['app.debug' => false]);

        Route::get('/_testing/unavailable', function (): never {
            abort(503);
        });

        $this->get('/_testing/unavailable')
            ->assertServiceUnavailable()
            ->assertSee('Le blog est momentanément indisponible.');
    }
}
