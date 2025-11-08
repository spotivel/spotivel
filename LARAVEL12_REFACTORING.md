# Laravel 12 Refactoring and Playlist Syncing Implementation

## Changes Implemented (Commit 27eb69d)

### 1. Laravel 12 Standard Compliance

#### Bootstrap Refactoring

**Before (Laravel 11 style):**
```php
// bootstrap/app.php
$app = new Illuminate\Foundation\Application($_ENV['APP_BASE_PATH'] ?? dirname(__DIR__));
$app->singleton(Illuminate\Contracts\Http\Kernel::class, App\Http\Kernel::class);
$app->singleton(Illuminate\Contracts\Console\Kernel::class, App\Console\Kernel::class);
return $app;
```

**After (Laravel 12 standard):**
```php
// bootstrap/app.php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {})
    ->withExceptions(function (Exceptions $exceptions) {})
    ->create();
```

**New Files:**
- ✅ `bootstrap/providers.php` - Service provider registration

**Removed Files:**
- ✅ `app/Http/Kernel.php` - No longer needed in Laravel 12
- ✅ `app/Console/Kernel.php` - No longer needed in Laravel 12
- ✅ `app/Exceptions/Handler.php` - Handled via bootstrap configuration

### 2. Playlist Syncing Implementation

#### Database Schema

**New Tables:**

**playlists:**
```php
- id (primary key)
- spotify_id (unique)
- name
- description
- public (boolean)
- collaborative (boolean)
- total_tracks (integer)
- images (json)
- uri, href, external_url
- owner_id, owner_name
- timestamps
```

**playlist_track (pivot):**
```php
- id
- playlist_id (foreign key → playlists)
- track_id (foreign key → tracks)
- position (integer) - track position in playlist
- timestamps
- unique constraint on (playlist_id, track_id, position)
```

**Updated Tables:**
- `tracks` - Added `is_interesting` boolean column
- `artists` - Added `is_interesting` boolean column

#### Models

**Playlist Model:**
```php
class Playlist extends Model
{
    protected $fillable = [
        'spotify_id', 'name', 'description', 'public', 
        'collaborative', 'total_tracks', 'images', 
        'uri', 'href', 'external_url', 'owner_id', 'owner_name'
    ];
    
    protected $casts = [
        'public' => 'boolean',
        'collaborative' => 'boolean',
        'total_tracks' => 'integer',
        'images' => 'array',
    ];
    
    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class)
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('position');
    }
}
```

**Track Model Updates:**
- Added `is_interesting` to fillable array
- Added `is_interesting` boolean cast
- Added `playlists()` relationship method

**Artist Model Updates:**
- Added `is_interesting` to fillable array
- Added `is_interesting` boolean cast

### 3. Queue Jobs Architecture

#### PopulatePlaylistsJob

**Purpose:** Fetch all user playlists from Spotify and queue sync jobs

**Flow:**
1. Fetches user's playlists with pagination (limit: 50)
2. Creates/updates Playlist records in database
3. Dispatches `SyncPlaylistJob` for each playlist
4. Logs progress and errors

**Key Features:**
- Pagination support for large playlist collections
- Error handling and logging
- Automatic queue dispatch for individual syncing

#### SyncPlaylistJob

**Purpose:** Sync individual playlist tracks using DTO and pipeline processing

**Flow:**
1. Fetch playlist from database
2. Fetch all tracks from Spotify playlist (paginated)
3. Create `PlaylistSyncDTO` with tracks
4. Run through pipeline handlers:
   - Remove duplicates
   - Normalize data
   - Validate tracks
5. Save tracks to database
6. Sync track-artist relationships
7. Attach tracks to playlist with position

**Key Features:**
- DTO-based processing
- Pipeline pattern for data transformation
- Position tracking in pivot table
- Comprehensive error handling

### 4. DTO Pattern

**PlaylistSyncDTO:**
```php
class PlaylistSyncDTO
{
    public function __construct(
        public readonly int $playlistId,
        public readonly string $spotifyId,
        public readonly array $tracks,
        public readonly array $metadata = [],
    ) {}
    
    public function withTracks(array $tracks): self
    {
        return new self(
            $this->playlistId,
            $this->spotifyId,
            $tracks,
            $this->metadata
        );
    }
}
```

**Benefits:**
- Immutable data structure
- Type-safe pipeline processing
- Clean separation of concerns
- Easy to test

### 5. Pipeline Handlers

#### RemoveDuplicatePlaylistTracksHandler
- Removes duplicate tracks based on Spotify ID
- Maintains track order
- Filters duplicate entries from API responses

#### NormalizePlaylistTrackDataHandler
- Trims whitespace from track names
- Ensures booleans: `explicit`, `is_local`
- Ensures integers: `duration_ms`
- Standardizes data format

#### ValidatePlaylistTracksHandler
- Validates required fields: `id`, `name`, `duration_ms`
- Filters out invalid/incomplete tracks
- Ensures data integrity before database save
- Re-indexes array after filtering

**Pipeline Flow:**
```php
app(Pipeline::class)
    ->send($dto)
    ->through([
        RemoveDuplicatePlaylistTracksHandler::class,
        NormalizePlaylistTrackDataHandler::class,
        ValidatePlaylistTracksHandler::class,
    ])
    ->thenReturn();
```

### 6. Filament 4 Resources

#### PlaylistResource

**Features:**
- Table columns: spotify_id, name, public, collaborative, total_tracks, owner_name
- Form fields for creating/editing playlists
- "Populate" button dispatches `PopulatePlaylistsJob`
- Filters: public, collaborative
- Full CRUD operations

**Pages:**
- ListPlaylists
- CreatePlaylist
- EditPlaylist

**Populate Action:**
```php
Tables\Actions\Action::make('populate')
    ->label('Populate')
    ->icon('heroicon-o-arrow-down-tray')
    ->action(function () {
        \App\Jobs\PopulatePlaylistsJob::dispatch();
        
        \Filament\Notifications\Notification::make()
            ->title('Playlists population queued')
            ->success()
            ->send();
    })
```

#### Updated Resources

**TrackResource:**
- Added `is_interesting` toggle in form
- Added `is_interesting` icon column in table
- Added `is_interesting` filter

**ArtistResource:**
- Added `is_interesting` toggle in form
- Added `is_interesting` icon column in table
- Added `is_interesting` filter

### 7. Complete Feature Set

**Navigation:**
```
Dashboard (no widgets)
├── Tracks (is_interesting toggle)
├── Albums
├── Artists (is_interesting toggle)
└── Playlists (new)
```

**Sync Flow:**
```
User clicks "Populate" on PlaylistResource
  ↓
PopulatePlaylistsJob dispatched
  ↓
Fetches playlists from Spotify API
  ↓
Creates Playlist records
  ↓
Dispatches SyncPlaylistJob for each playlist
  ↓
SyncPlaylistJob processes each playlist:
  - Fetches tracks from Spotify
  - Creates PlaylistSyncDTO
  - Pipeline: Deduplicate → Normalize → Validate
  - Saves tracks to database
  - Syncs relationships
  - Attaches to playlist with position
```

### 8. Files Created/Modified

**Created (16 files):**
1. bootstrap/providers.php
2. app/Models/Playlist.php
3. app/DTOs/PlaylistSyncDTO.php
4. app/Jobs/PopulatePlaylistsJob.php
5. app/Jobs/SyncPlaylistJob.php
6. app/Pipelines/RemoveDuplicatePlaylistTracksHandler.php
7. app/Pipelines/NormalizePlaylistTrackDataHandler.php
8. app/Pipelines/ValidatePlaylistTracksHandler.php
9. app/Filament/Resources/PlaylistResource.php
10. app/Filament/Resources/PlaylistResource/Pages/ListPlaylists.php
11. app/Filament/Resources/PlaylistResource/Pages/CreatePlaylist.php
12. app/Filament/Resources/PlaylistResource/Pages/EditPlaylist.php
13. database/migrations/2024_01_01_000007_create_playlists_table.php
14. database/migrations/2024_01_01_000008_create_playlist_track_table.php
15. database/migrations/2024_01_01_000009_add_is_interesting_to_tracks_and_artists.php

**Modified (4 files):**
1. bootstrap/app.php - Laravel 12 standard
2. app/Models/Track.php - Added is_interesting, playlists relationship
3. app/Models/Artist.php - Added is_interesting
4. app/Filament/Resources/TrackResource.php - Added is_interesting toggle/filter
5. app/Filament/Resources/ArtistResource.php - Added is_interesting toggle/filter

**Removed (3 files):**
1. app/Http/Kernel.php
2. app/Console/Kernel.php
3. app/Exceptions/Handler.php

## Summary

All requirements from comment #3437234486 have been implemented:

✅ Laravel 12 bootstrap/app.php standard with Application::configure()
✅ Laravel 12 bootstrap/providers.php for provider registration
✅ Removed old kernel files
✅ Playlist syncing with PopulatePlaylistsJob and SyncPlaylistJob
✅ DTO pattern with PlaylistSyncDTO
✅ Pipeline handlers for deduplication, normalization, validation
✅ Filament 4 PlaylistResource with full CRUD
✅ is_interesting field for Track and Artist models
✅ is_interesting toggle and filter in Filament resources

The implementation follows Laravel 12 conventions, uses modern patterns (DTO, Pipeline), and provides a complete playlist syncing solution with queue-based processing and data validation.
