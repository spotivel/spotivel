<?php

namespace Tests\Feature\Console;

use App\Console\Commands\RefreshSpotifyToken;
use App\Contracts\OAuthServiceInterface;
use Illuminate\Support\Facades\Cache;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(RefreshSpotifyToken::class)]
class RefreshSpotifyTokenTest extends TestCase
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

    #[Test]
    public function it_refreshes_token_when_about_to_expire(): void
    {
        $mockOAuthService = Mockery::mock(OAuthServiceInterface::class);
        $mockOAuthService->shouldReceive('getTimeUntilExpiry')
            ->once()
            ->andReturn(200); // 200 seconds until expiry

        $mockOAuthService->shouldReceive('refreshCurrentToken')
            ->once()
            ->andReturn(true);

        $mockOAuthService->shouldReceive('getTimeUntilExpiry')
            ->once()
            ->andReturn(3600); // New expiry time

        $this->app->instance(OAuthServiceInterface::class, $mockOAuthService);

        $this->artisan('spotify:refresh-token')
            ->expectsOutput('Token expires in 200 seconds.')
            ->expectsOutput('Refreshing token...')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_skips_refresh_when_token_is_still_valid(): void
    {
        $mockOAuthService = Mockery::mock(OAuthServiceInterface::class);
        $mockOAuthService->shouldReceive('getTimeUntilExpiry')
            ->once()
            ->andReturn(3600); // Token valid for an hour

        $this->app->instance(OAuthServiceInterface::class, $mockOAuthService);

        $this->artisan('spotify:refresh-token')
            ->expectsOutput('Token is still valid. No refresh needed.')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_fails_when_no_token_found(): void
    {
        $mockOAuthService = Mockery::mock(OAuthServiceInterface::class);
        $mockOAuthService->shouldReceive('getTimeUntilExpiry')
            ->once()
            ->andReturn(null);

        $this->app->instance(OAuthServiceInterface::class, $mockOAuthService);

        $this->artisan('spotify:refresh-token')
            ->expectsOutput('No token found in cache. Please authenticate first.')
            ->assertExitCode(1);
    }

    #[Test]
    public function it_fails_when_refresh_fails(): void
    {
        $mockOAuthService = Mockery::mock(OAuthServiceInterface::class);
        $mockOAuthService->shouldReceive('getTimeUntilExpiry')
            ->once()
            ->andReturn(200);

        $mockOAuthService->shouldReceive('refreshCurrentToken')
            ->once()
            ->andReturn(false);

        $this->app->instance(OAuthServiceInterface::class, $mockOAuthService);

        $this->artisan('spotify:refresh-token')
            ->expectsOutput('Failed to refresh token. Please re-authenticate.')
            ->assertExitCode(1);
    }
}
