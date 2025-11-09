<?php

namespace Tests\Feature\Controllers;

use App\Contracts\OAuthServiceInterface;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class OAuthControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Use array cache driver for testing
        config(['cache.default' => 'array']);
    }

    protected function tearDown(): void
    {
        Cache::flush();
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_redirects_to_spotify_authorization(): void
    {
        $mockOAuthService = Mockery::mock(OAuthServiceInterface::class);
        $mockOAuthService->shouldReceive('getAuthorizationUrl')
            ->once()
            ->andReturn('https://accounts.spotify.com/authorize?client_id=test');

        $this->app->instance(OAuthServiceInterface::class, $mockOAuthService);

        $response = $this->get(route('spotify.redirect'));

        $response->assertRedirect('https://accounts.spotify.com/authorize?client_id=test');
    }

    /** @test */
    public function it_handles_callback_successfully(): void
    {
        $mockOAuthService = Mockery::mock(OAuthServiceInterface::class);
        $mockOAuthService->shouldReceive('getAccessToken')
            ->once()
            ->with('test-code')
            ->andReturn([
                'access_token' => 'test-access-token',
                'refresh_token' => 'test-refresh-token',
                'expires_in' => 3600,
            ]);

        $mockOAuthService->shouldReceive('cacheToken')
            ->once()
            ->with('test-access-token', 'test-refresh-token', 3600);

        $this->app->instance(OAuthServiceInterface::class, $mockOAuthService);

        $response = $this->get(route('spotify.callback', ['code' => 'test-code']));

        $response->assertRedirect('/admin');
        $response->assertSessionHas('success', 'Successfully connected to Spotify!');
    }

    /** @test */
    public function it_handles_callback_without_code(): void
    {
        $mockOAuthService = Mockery::mock(OAuthServiceInterface::class);
        $this->app->instance(OAuthServiceInterface::class, $mockOAuthService);

        $response = $this->get(route('spotify.callback'));

        $response->assertRedirect('/admin');
        $response->assertSessionHas('error', 'Authorization failed: No code received');
    }

    /** @test */
    public function it_handles_callback_with_error(): void
    {
        $mockOAuthService = Mockery::mock(OAuthServiceInterface::class);
        $this->app->instance(OAuthServiceInterface::class, $mockOAuthService);

        $response = $this->get(route('spotify.callback', ['error' => 'access_denied']));

        $response->assertRedirect('/admin');
        $response->assertSessionHas('error', 'Authorization failed: access_denied');
    }

    /** @test */
    public function it_handles_callback_with_exception(): void
    {
        $mockOAuthService = Mockery::mock(OAuthServiceInterface::class);
        $mockOAuthService->shouldReceive('getAccessToken')
            ->once()
            ->with('test-code')
            ->andThrow(new \Exception('API error'));

        $this->app->instance(OAuthServiceInterface::class, $mockOAuthService);

        $response = $this->get(route('spotify.callback', ['code' => 'test-code']));

        $response->assertRedirect('/admin');
        $response->assertSessionHas('error');
    }

    /** @test */
    public function it_disconnects_from_spotify(): void
    {
        $mockOAuthService = Mockery::mock(OAuthServiceInterface::class);
        $mockOAuthService->shouldReceive('clearCache')
            ->once();

        $this->app->instance(OAuthServiceInterface::class, $mockOAuthService);

        $response = $this->get(route('spotify.disconnect'));

        $response->assertRedirect('/admin');
        $response->assertSessionHas('success', 'Disconnected from Spotify');
    }
}
