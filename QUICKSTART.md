# Quick Start Guide

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/spotivel/spotivel.git
   cd spotivel
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Configure environment:**
   ```bash
   cp .env.example .env
   # Edit .env and add your Spotify API credentials
   ```

4. **Run migrations:**
   ```bash
   php artisan migrate
   ```

## Configuration

Add these variables to your `.env` file:

```env
SPOTIFY_CLIENT_ID=your_client_id_here
SPOTIFY_CLIENT_SECRET=your_client_secret_here
SPOTIFY_ACCESS_TOKEN=your_access_token_here
```

## Usage Examples

### 1. Using the Spotify Tracks Client

```php
use App\Services\SpotifyTracksClient;

// Initialize the client
$tracksClient = new SpotifyTracksClient();

// Or with a specific access token
$tracksClient = new SpotifyTracksClient('your_access_token');

// Get a single track
$track = $tracksClient->getTrack('11dFghVXANMlKmJXsNCbNl');

// Get multiple tracks
$tracks = $tracksClient->getTracks([
    '11dFghVXANMlKmJXsNCbNl',
    '6rqhFgbbKwnb9MLmUQDhG6'
]);

// Get user's saved tracks (paginated)
$savedTracks = $tracksClient->getSavedTracks(limit: 50, offset: 0);
```

### 2. Syncing Tracks to Database

```php
use App\Services\SpotifyTracksClient;
use App\Models\Track;
use App\Models\Artist;
use App\Models\Album;

$client = new SpotifyTracksClient();
$savedTracks = $client->getSavedTracks(limit: 50);

foreach ($savedTracks['items'] as $item) {
    $trackData = $item['track'];
    
    // Create or update track
    $track = Track::updateOrCreate(
        ['spotify_id' => $trackData['id']],
        [
            'name' => $trackData['name'],
            'duration_ms' => $trackData['duration_ms'],
            'popularity' => $trackData['popularity'] ?? null,
            'explicit' => $trackData['explicit'],
            'preview_url' => $trackData['preview_url'] ?? null,
            'uri' => $trackData['uri'],
            'href' => $trackData['href'],
            'external_url' => $trackData['external_urls']['spotify'] ?? null,
        ]
    );
    
    // Sync artists
    $artistIds = [];
    foreach ($trackData['artists'] as $artistData) {
        $artist = Artist::updateOrCreate(
            ['spotify_id' => $artistData['id']],
            [
                'name' => $artistData['name'],
                'uri' => $artistData['uri'],
                'href' => $artistData['href'],
                'external_url' => $artistData['external_urls']['spotify'] ?? null,
            ]
        );
        $artistIds[] = $artist->id;
    }
    $track->artists()->sync($artistIds);
    
    // Sync album
    if (isset($trackData['album'])) {
        $albumData = $trackData['album'];
        $album = Album::updateOrCreate(
            ['spotify_id' => $albumData['id']],
            [
                'name' => $albumData['name'],
                'album_type' => $albumData['album_type'],
                'release_date' => $albumData['release_date'] ?? null,
                'total_tracks' => $albumData['total_tracks'] ?? null,
                'images' => $albumData['images'] ?? null,
                'uri' => $albumData['uri'],
                'href' => $albumData['href'],
                'external_url' => $albumData['external_urls']['spotify'] ?? null,
            ]
        );
        $track->albums()->sync([$album->id]);
        
        // Sync album artists
        $albumArtistIds = [];
        foreach ($albumData['artists'] as $artistData) {
            $artist = Artist::updateOrCreate(
                ['spotify_id' => $artistData['id']],
                ['name' => $artistData['name']]
            );
            $albumArtistIds[] = $artist->id;
        }
        $album->artists()->sync($albumArtistIds);
    }
}
```

### 3. Working with Relationships

```php
use App\Models\Track;
use App\Models\Artist;

// Get a track with its artists
$track = Track::with('artists')->where('spotify_id', 'track_id')->first();

foreach ($track->artists as $artist) {
    echo $artist->name . "\n";
}

// Get an artist with their tracks
$artist = Artist::with('tracks')->where('spotify_id', 'artist_id')->first();

foreach ($artist->tracks as $track) {
    echo $track->name . " (" . $track->duration_ms . "ms)\n";
}

// Get tracks by popularity
$popularTracks = Track::where('popularity', '>', 80)
    ->orderBy('popularity', 'desc')
    ->with('artists')
    ->get();
```

### 4. Deduplicating Tracks (Example with Laravel Pipelines)

```php
use App\Models\Track;
use Illuminate\Pipeline\Pipeline;

class RemoveDuplicatesByName
{
    public function handle($tracks, $next)
    {
        $unique = $tracks->unique('name');
        return $next($unique);
    }
}

class RemoveDuplicatesByDuration
{
    public function handle($tracks, $next)
    {
        $unique = $tracks->unique(function ($track) {
            return $track->name . '-' . $track->duration_ms;
        });
        return $next($unique);
    }
}

// Pipeline to deduplicate tracks
$tracks = Track::all();

$deduplicated = app(Pipeline::class)
    ->send($tracks)
    ->through([
        RemoveDuplicatesByName::class,
        RemoveDuplicatesByDuration::class,
    ])
    ->thenReturn();

echo "Original: " . $tracks->count() . " tracks\n";
echo "After deduplication: " . $deduplicated->count() . " tracks\n";
```

### 5. Using the External Client Directly

```php
use App\Services\ExternalClient;

// Create a client for any external API
$client = new ExternalClient('https://api.example.com');
$client->setHeaders(['Authorization' => 'Bearer token']);
$client->setTimeout(60);

// Make requests
$data = $client->get('/endpoint', ['param' => 'value']);
$result = $client->post('/endpoint', ['data' => 'value']);
```

### 6. Advanced Spotify API Usage

```php
use App\Services\SpotifyTracksClient;

$client = new SpotifyTracksClient();

// Get audio features for a track
$features = $client->getTrackAudioFeatures('track_id');
// Returns: danceability, energy, key, loudness, mode, speechiness, etc.

// Get audio features for multiple tracks
$features = $client->getTracksAudioFeatures(['track_id_1', 'track_id_2']);

// Get audio analysis
$analysis = $client->getTrackAudioAnalysis('track_id');
// Returns: detailed audio analysis including bars, beats, sections, segments

// Get recommendations based on seed tracks
$recommendations = $client->getRecommendations(
    seedTracks: ['track_id_1', 'track_id_2'],
    limit: 20
);
```

## Testing

Run the test suite:

```bash
vendor/bin/phpunit
```

Run specific test files:

```bash
vendor/bin/phpunit tests/Unit/Models/TrackTest.php
vendor/bin/phpunit tests/Unit/Services/SpotifyClientTest.php
```

## Architecture Overview

### Models
- **Track**: Represents a Spotify track with artists and albums
- **Artist**: Represents a Spotify artist with tracks and albums
- **Album**: Represents a Spotify album with artists and tracks

### Services
- **ExternalClient**: Generic HTTP client for external APIs
- **SpotifyClient**: Spotify API base client (uses ExternalClient)
- **SpotifyTracksClient**: Specialized client for track operations (extends SpotifyClient)

### Relationships
```
Track ←→ Artist (many-to-many)
Artist ←→ Album (many-to-many)
Album ←→ Track (many-to-many)
```

## Next Steps

1. **Set up authentication** - Implement OAuth flow for Spotify
2. **Create controllers** - Add HTTP controllers for API endpoints
3. **Implement pipelines** - Create Laravel Pipeline classes for deduplication logic
4. **Add queue jobs** - Queue track syncing for better performance
5. **Add caching** - Cache API responses to reduce Spotify API calls
6. **Create Filament resources** - Build admin panel with Filament 4

## Documentation

- **README.md** - Project overview and setup
- **IMPLEMENTATION.md** - Detailed technical documentation
- **ARCHITECTURE.md** - Architecture diagrams and patterns
- **VERIFICATION.md** - Requirements verification checklist
- **QUICKSTART.md** - This file

## Support

For issues or questions, please refer to the documentation files or create an issue on GitHub.
