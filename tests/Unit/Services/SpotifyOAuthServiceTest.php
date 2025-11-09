<?php

namespace Tests\Unit\Services;

use App\Services\SpotifyOAuthService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SpotifyOAuthServiceTest extends TestCase
{
    protected SpotifyOAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Use array cache driver for testing
        config(['cache.default' => 'array']);

        // Create a mock HTTP client
        $mockClient = new \App\Services\ExternalClient(
            baseUrl: '',
            headers: [],
            timeout: 30
        );

        $this->service = new SpotifyOAuthService(
            client: $mockClient,
            clientId: 'test-client-id',
            clientSecret: 'test-client-secret',
            redirectUri: 'http://localhost/auth/spotify/callback',
            scopes: 'user-library-read playlist-read-private',
            authUrl: 'https://accounts.spotify.com/authorize',
            tokenUrl: 'https://accounts.spotify.com/api/token'
        );
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }

    /** @test */
    public function it_generates_authorization_url(): void
    {
        $url = $this->service->getAuthorizationUrl();

        $this->assertStringContainsString('https://accounts.spotify.com/authorize', $url);
        $this->assertStringContainsString('client_id=test-client-id', $url);
        $this->assertStringContainsString('response_type=code', $url);
        $this->assertStringContainsString('redirect_uri=', $url);
        $this->assertStringContainsString('scope=', $url);
    }

    /** @test */
    public function it_exchanges_code_for_access_token(): void
    {
        Http::fake([
            'https://accounts.spotify.com/api/token' => Http::response([
                'access_token' => 'test-access-token',
                'refresh_token' => 'test-refresh-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ], 200),
        ]);

        $result = $this->service->getAccessToken('test-code');

        $this->assertEquals('test-access-token', $result['access_token']);
        $this->assertEquals('test-refresh-token', $result['refresh_token']);
        $this->assertEquals(3600, $result['expires_in']);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'https://accounts.spotify.com/api/token' &&
                   $data['grant_type'] === 'authorization_code' &&
                   $data['code'] === 'test-code' &&
                   $data['client_id'] === 'test-client-id' &&
                   $data['client_secret'] === 'test-client-secret';
        });
    }

    /** @test */
    public function it_refreshes_access_token(): void
    {
        Http::fake([
            'https://accounts.spotify.com/api/token' => Http::response([
                'access_token' => 'new-access-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ], 200),
        ]);

        $result = $this->service->refreshAccessToken('test-refresh-token');

        $this->assertEquals('new-access-token', $result['access_token']);
        $this->assertEquals(3600, $result['expires_in']);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'https://accounts.spotify.com/api/token' &&
                   $data['grant_type'] === 'refresh_token' &&
                   $data['refresh_token'] === 'test-refresh-token' &&
                   $data['client_id'] === 'test-client-id' &&
                   $data['client_secret'] === 'test-client-secret';
        });
    }

    /** @test */
    public function it_caches_access_token(): void
    {
        $this->service->cacheToken('test-access-token', 'test-refresh-token', 3600);

        $this->assertEquals('test-access-token', Cache::get('spotify_access_token'));
        $this->assertEquals('test-refresh-token', Cache::get('spotify_refresh_token'));
        $this->assertNotNull(Cache::get('spotify_token_expires_at'));
    }

    /** @test */
    public function it_retrieves_cached_token(): void
    {
        Cache::put('spotify_access_token', 'cached-token', 3600);
        Cache::put('spotify_token_expires_at', now()->addHour()->timestamp, 3600);

        $token = $this->service->getCachedToken();

        $this->assertEquals('cached-token', $token);
    }

    /** @test */
    public function it_returns_null_when_no_cached_token(): void
    {
        $token = $this->service->getCachedToken();

        $this->assertNull($token);
    }

    /** @test */
    public function it_clears_cached_tokens(): void
    {
        Cache::put('spotify_access_token', 'test-token', 3600);
        Cache::put('spotify_refresh_token', 'test-refresh', 3600);
        Cache::put('spotify_token_expires_at', now()->timestamp, 3600);

        $this->service->clearCache();

        $this->assertNull(Cache::get('spotify_access_token'));
        $this->assertNull(Cache::get('spotify_refresh_token'));
        $this->assertNull(Cache::get('spotify_token_expires_at'));
    }

    /** @test */
    public function it_auto_refreshes_token_when_about_to_expire(): void
    {
        // Set up token that expires in 3 minutes (less than 5 minute threshold)
        Cache::put('spotify_access_token', 'old-token', 3600);
        Cache::put('spotify_refresh_token', 'test-refresh-token', 3600);
        Cache::put('spotify_token_expires_at', now()->addMinutes(3)->timestamp, 3600);

        Http::fake([
            'https://accounts.spotify.com/api/token' => Http::response([
                'access_token' => 'refreshed-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ], 200),
        ]);

        $token = $this->service->getCachedToken();

        // Should have refreshed and returned new token
        $this->assertEquals('refreshed-token', $token);
        $this->assertEquals('refreshed-token', Cache::get('spotify_access_token'));
    }

    /** @test */
    public function it_clears_cache_when_refresh_fails(): void
    {
        Cache::put('spotify_access_token', 'old-token', 3600);
        Cache::put('spotify_refresh_token', 'test-refresh-token', 3600);
        Cache::put('spotify_token_expires_at', now()->addMinutes(3)->timestamp, 3600);

        Http::fake([
            'https://accounts.spotify.com/api/token' => Http::response([], 401),
        ]);

        $token = $this->service->getCachedToken();

        // Should have cleared cache after failed refresh
        $this->assertNull(Cache::get('spotify_access_token'));
        $this->assertNull(Cache::get('spotify_refresh_token'));
    }

    /** @test */
    public function it_handles_refresh_token_not_returned_on_refresh(): void
    {
        Cache::put('spotify_access_token', 'old-token', 3600);
        Cache::put('spotify_refresh_token', 'original-refresh-token', 3600);
        Cache::put('spotify_token_expires_at', now()->addMinutes(3)->timestamp, 3600);

        Http::fake([
            'https://accounts.spotify.com/api/token' => Http::response([
                'access_token' => 'new-access-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
                // Note: no refresh_token in response (Spotify sometimes doesn't return it)
            ], 200),
        ]);

        $this->service->getCachedToken();

        // Should keep original refresh token
        $this->assertEquals('original-refresh-token', Cache::get('spotify_refresh_token'));
        $this->assertEquals('new-access-token', Cache::get('spotify_access_token'));
    }

    /** @test */
    public function it_returns_time_until_expiry(): void
    {
        $expiresIn = 3600;
        $this->service->cacheToken('test-token', 'test-refresh', $expiresIn);

        $timeUntilExpiry = $this->service->getTimeUntilExpiry();

        $this->assertNotNull($timeUntilExpiry);
        $this->assertGreaterThan(0, $timeUntilExpiry);
        $this->assertLessThanOrEqual($expiresIn, $timeUntilExpiry);
    }

    /** @test */
    public function it_returns_null_when_no_token_for_expiry_check(): void
    {
        $timeUntilExpiry = $this->service->getTimeUntilExpiry();

        $this->assertNull($timeUntilExpiry);
    }

    /** @test */
    public function it_manually_refreshes_current_token(): void
    {
        Cache::put('spotify_access_token', 'old-token', 3600);
        Cache::put('spotify_refresh_token', 'test-refresh-token', 3600);
        Cache::put('spotify_token_expires_at', now()->addMinutes(10)->timestamp, 3600);

        Http::fake([
            'https://accounts.spotify.com/api/token' => Http::response([
                'access_token' => 'new-access-token',
                'refresh_token' => 'new-refresh-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ], 200),
        ]);

        $result = $this->service->refreshCurrentToken();

        $this->assertTrue($result);
        $this->assertEquals('new-access-token', Cache::get('spotify_access_token'));
        $this->assertEquals('new-refresh-token', Cache::get('spotify_refresh_token'));
    }

    /** @test */
    public function it_returns_false_when_refresh_fails(): void
    {
        Cache::put('spotify_refresh_token', 'test-refresh-token', 3600);

        Http::fake([
            'https://accounts.spotify.com/api/token' => Http::response([], 401),
        ]);

        $result = $this->service->refreshCurrentToken();

        $this->assertFalse($result);
        $this->assertNull(Cache::get('spotify_access_token'));
    }

    /** @test */
    public function it_returns_false_when_no_refresh_token(): void
    {
        $result = $this->service->refreshCurrentToken();

        $this->assertFalse($result);
    }
}
