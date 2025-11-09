# Spotify OAuth2 Implementation

This document describes the OAuth2 implementation for Spotify authentication in the Spotivel application.

## Overview

The application now supports OAuth2 authentication flow with Spotify, including:
- Authorization redirect
- Token exchange via callback
- Automatic token caching
- Automatic token refresh when expiring
- Multiple header support for API calls

## Configuration

### Environment Variables

Update your `.env` file with the following Spotify credentials:

```env
SPOTIFY_CLIENT_ID=your_client_id_here
SPOTIFY_CLIENT_SECRET=your_client_secret_here
SPOTIFY_REDIRECT_URI=http://localhost/auth/spotify/callback
```

You can obtain these credentials from the [Spotify Developer Dashboard](https://developer.spotify.com/dashboard).

### Cache Configuration

The OAuth service uses Laravel's cache system to store tokens. By default, it uses the cache driver configured in your `config/cache.php`. For production, consider using Redis or Memcached for better performance.

## Usage

### Routes

The following routes are available for OAuth flow:

- **Redirect to Spotify**: `GET /auth/spotify/redirect`
  - Redirects user to Spotify authorization page
  - User grants permissions to your app

- **OAuth Callback**: `GET /auth/spotify/callback`
  - Handles the callback from Spotify
  - Exchanges authorization code for access token
  - Caches the tokens

- **Disconnect**: `GET /auth/spotify/disconnect`
  - Clears cached tokens
  - Logs user out of Spotify integration

### Programmatic Usage

#### Getting the OAuth Service

```php
use App\Contracts\OAuthServiceInterface;

class YourController
{
    public function __construct(
        protected OAuthServiceInterface $oauthService
    ) {}
}
```

#### Manually Getting Tokens

```php
// Get authorization URL
$authUrl = $this->oauthService->getAuthorizationUrl();

// Exchange code for token
$tokenData = $this->oauthService->getAccessToken($code);

// Refresh token
$newTokenData = $this->oauthService->refreshAccessToken($refreshToken);

// Get cached token (auto-refreshes if needed)
$accessToken = $this->oauthService->getCachedToken();

// Clear cache
$this->oauthService->clearCache();
```

#### Using the SpotifyClient with Headers

```php
use App\Services\SpotifyClient;

// Via dependency injection
public function __construct(
    protected SpotifyClient $client
) {}

// Basic request (uses cached token from service provider)
$response = $this->client->request()->get('/me');

// Request with additional headers
$response = $this->client->requestWithHeaders([
    'X-Custom-Header' => 'value'
])->get('/me');

// Using HTTP method helpers
$response = $this->client->request(HttpMethod::GET, '/tracks/123');
$response = $this->client->request(HttpMethod::POST, '/playlists/123/tracks', [
    'json' => ['uris' => ['spotify:track:1', 'spotify:track:2']]
]);
```

## Architecture

### Components

1. **OAuthServiceInterface**: Contract defining OAuth operations
2. **SpotifyOAuthService**: Implementation of OAuth flow for Spotify
3. **OAuthController**: HTTP controller handling OAuth routes
4. **SpotifyServiceProvider**: Binds OAuth service and configures clients

### Token Management

The service automatically handles token lifecycle:

1. **Authorization**: User is redirected to Spotify
2. **Token Exchange**: Authorization code is exchanged for access/refresh tokens
3. **Caching**: Tokens are cached with appropriate TTL
4. **Auto-Refresh**: When cached token is about to expire (< 5 minutes), it's automatically refreshed
5. **Fallback**: If refresh fails, cache is cleared to force re-authentication

### Token Storage

Tokens are stored in Laravel's cache with the following keys:
- `spotify_access_token`: The current access token
- `spotify_refresh_token`: The refresh token (30-day TTL)
- `spotify_token_expires_at`: Unix timestamp of token expiration

## Security Considerations

1. **HTTPS**: Always use HTTPS in production for OAuth callbacks
2. **Environment Variables**: Never commit credentials to version control
3. **Cache Security**: Use encrypted cache drivers in production
4. **Token Storage**: Consider using database cache driver for persistent tokens
5. **Scopes**: Only request necessary Spotify scopes

## Scopes

The default scopes requested are:
- `user-library-read`: Read user's saved tracks
- `playlist-read-private`: Read private playlists
- `playlist-modify-public`: Modify public playlists
- `playlist-modify-private`: Modify private playlists

To modify scopes, edit the `getAuthorizationUrl()` method in `SpotifyOAuthService`.

## Testing

All components include comprehensive PHPUnit tests:

```bash
# Run OAuth tests
./vendor/bin/phpunit tests/Unit/Services/SpotifyOAuthServiceTest.php
./vendor/bin/phpunit tests/Feature/Controllers/OAuthControllerTest.php
./vendor/bin/phpunit tests/Unit/Services/SpotifyClientRequestTest.php

# Run all tests
./vendor/bin/phpunit
```

## Troubleshooting

### "Authorization failed: No code received"
- Check that redirect URI in Spotify dashboard matches your `.env` setting
- Ensure the callback URL is accessible

### "Failed to obtain access token"
- Verify `SPOTIFY_CLIENT_ID` and `SPOTIFY_CLIENT_SECRET` are correct
- Check that the authorization code hasn't expired (expires in 10 minutes)

### Tokens not persisting
- Check cache configuration in `config/cache.php`
- Ensure cache driver is working (`php artisan cache:clear`)
- In tests, use array cache driver

### Token refresh fails
- Refresh tokens are valid for 1 hour after last use
- If refresh fails, user must re-authenticate
- Check Spotify API status if getting 5xx errors

## Examples

### Basic OAuth Flow

```php
// 1. User clicks "Connect to Spotify" button
<a href="{{ route('spotify.redirect') }}">Connect to Spotify</a>

// 2. User is redirected to Spotify, grants permissions

// 3. Spotify redirects back to callback with code

// 4. OAuthController handles callback, exchanges code, caches tokens

// 5. All subsequent API calls use cached token automatically
```

### Using in Jobs

```php
use App\Services\SpotifyTracksClient;

class FetchTracksJob
{
    public function __construct(
        protected SpotifyTracksClient $client
    ) {}
    
    public function handle(): void
    {
        // Client automatically uses cached token
        $tracks = $this->client->getSavedTracks();
        
        // Process tracks...
    }
}
```

## Related Files

- `app/Contracts/OAuthServiceInterface.php` - OAuth contract
- `app/Services/SpotifyOAuthService.php` - OAuth implementation
- `app/Http/Controllers/OAuthController.php` - OAuth routes handler
- `app/Providers/SpotifyServiceProvider.php` - Service configuration
- `app/Services/SpotifyClient.php` - Enhanced HTTP client
- `routes/web.php` - OAuth routes
- `config/services.php` - Spotify credentials config
