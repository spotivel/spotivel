# Spotivel - Spotify Track Sync Application

A Laravel 12 application with Filament 4 for syncing Spotify tracks and playlists with deduplication via pipelines, following SOLID principles and dynamic programming patterns.

## Architecture Principles

This application is built following strict software engineering principles:

### SOLID Principles

**Single Responsibility Principle (SRP)**
- Each class has one clear, well-defined responsibility
- Services handle database operations for their specific entity
- Orchestrators coordinate pipeline workflows
- Transformers handle DTO creation
- Jobs dispatch to services/orchestrators, not contain business logic

**Open/Closed Principle**
- Extensible through decorators (HttpClientExceptionDecorator, RequestLoggerDecorator)
- Pipeline handlers can be added without modifying existing code

**Liskov Substitution Principle**
- SpotifyTracksClient can substitute SpotifyClient where needed
- Decorators can substitute ExternalClient

**Interface Segregation Principle**
- Focused service interfaces for each entity type
- Clients provide only relevant methods

**Dependency Inversion Principle**
- Jobs depend on service abstractions, not concrete implementations
- Pipeline handlers work with DTO interfaces

### Additional Patterns

**Early Returns**
- Avoid nested conditionals
- Return early on validation failures
- Improve code readability

**Dynamic Programming**
- Optimize deduplication with memoization where applicable
- Efficient collection operations

**No JSON Columns**
- All migrations avoid JSON columns intentionally
- Data normalization follows database best practices
- Better query performance and data integrity

## Features

- **Models with Many-to-Many Relationships:**
  - Track has many Artists, Albums, Playlists (with position)
  - Artist has many Tracks and Albums
  - Album has many Artists and Tracks
  - Playlist has many Tracks (ordered by position)
  - `is_interesting` flag for Tracks and Artists

- **Spotify API Integration:**
  - `ExternalClient` - Base HTTP client for external API calls
  - `SpotifyClient` - Uses ExternalClient (composition pattern) for Spotify API
  - `SpotifyTracksClient` - Extends SpotifyClient for track-specific operations
  - Decorator pattern for exception handling and logging

- **Pipeline-Based Deduplication:**
  - RemoveDuplicateTracksHandler
  - NormalizeTrackDataHandler
  - ValidateTracksHandler
  - DTO-based processing

## Database Schema

### Tables

- **tracks** - Stores Spotify track information
  - Fields: spotify_id, name, duration_ms, popularity, explicit, disc_number, track_number, preview_url, uri, href, external_url, is_local, is_interesting

- **artists** - Stores Spotify artist information
  - Fields: spotify_id, name, popularity, followers, uri, href, external_url, is_interesting

- **albums** - Stores Spotify album information
  - Fields: spotify_id, name, album_type, release_date, release_date_precision, total_tracks, uri, href, external_url

- **playlists** - Stores Spotify playlist information
  - Fields: spotify_id, name, description, public, collaborative, total_tracks, uri, href, external_url, owner_id, owner_name

### Pivot Tables

- **artist_track** - Many-to-many relationship between artists and tracks
- **album_artist** - Many-to-many relationship between albums and artists
- **album_track** - Many-to-many relationship between albums and tracks
- **playlist_track** - Many-to-many relationship between playlists and tracks (with position)

## Models

### Track Model (`app/Models/Track.php`)
- `artists()` - BelongsToMany relationship to Artist
- `albums()` - BelongsToMany relationship to Album
- `playlists()` - BelongsToMany relationship to Playlist
- `is_interesting` - Boolean flag for interesting tracks

### Artist Model (`app/Models/Artist.php`)
- `tracks()` - BelongsToMany relationship to Track
- `albums()` - BelongsToMany relationship to Album
- `is_interesting` - Boolean flag for interesting artists

### Album Model (`app/Models/Album.php`)
- `artists()` - BelongsToMany relationship to Artist
- `tracks()` - BelongsToMany relationship to Track

### Playlist Model (`app/Models/Playlist.php`)
- `tracks()` - BelongsToMany relationship to Track (ordered by position)

## API Clients

### ExternalClient (`app/Services/ExternalClient.php`)
Base HTTP client class with methods:
- `get()`, `post()`, `put()`, `delete()`
- Configurable base URL, headers, and timeout

### SpotifyClient (`app/Services/SpotifyClient.php`)
Spotify API client that **uses** ExternalClient (composition):
- Manages Spotify API authentication
- Sets bearer token for all requests
- Protected methods for HTTP operations

### SpotifyTracksClient (`app/Services/SpotifyTracksClient.php`)
**Extends** SpotifyClient for track-specific operations:
- `getTrack()` - Get a single track
- `getTracks()` - Get multiple tracks
- `getAlbumTracks()` - Get tracks from an album
- `getSavedTracks()` - Get user's saved tracks
- `saveTracks()` - Save tracks for user
- `removeSavedTracks()` - Remove saved tracks
- `checkSavedTracks()` - Check if tracks are saved
- `getTrackAudioFeatures()` - Get audio features
- `getTracksAudioFeatures()` - Get audio features for multiple tracks
- `getTrackAudioAnalysis()` - Get audio analysis
- `getRecommendations()` - Get track recommendations

## Setup

1. Clone the repository
2. Copy `.env.example` to `.env` and configure your Spotify API credentials
3. Run migrations: `php artisan migrate`
4. Install dependencies: `composer install`

## Configuration

Add your Spotify API credentials to `.env`:

```env
SPOTIFY_CLIENT_ID=your_client_id
SPOTIFY_CLIENT_SECRET=your_client_secret
SPOTIFY_ACCESS_TOKEN=your_access_token
```

## Usage Example

```php
use App\Services\SpotifyTracksClient;

$tracksClient = new SpotifyTracksClient();
$track = $tracksClient->getTrack('spotify_track_id');

// Get saved tracks
$savedTracks = $tracksClient->getSavedTracks(limit: 50);
```

## Architecture

The implementation follows these principles:

1. **Composition over Inheritance**: SpotifyClient uses ExternalClient rather than extending it
2. **Single Responsibility**: Each client has a specific purpose
3. **DRY**: Common HTTP logic is centralized in ExternalClient
4. **Extensibility**: SpotifyTracksClient extends SpotifyClient for specialized functionality