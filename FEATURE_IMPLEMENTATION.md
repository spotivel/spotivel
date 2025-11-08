# Playlist Sync with Live Track Discovery - Implementation Summary

## Overview
This feature enables syncing playlists from Spotify, automatically finding and adding live versions of tracks, and pushing the enriched playlist back to Spotify API.

## Architecture Diagram

```
Dashboard Widget (PlaylistsTableWidget)
    |
    | [Sync All Playlists Button Clicked]
    |
    v
PopulatePlaylistsJob (Dispatched to Queue)
    |
    | Fetches all user playlists from Spotify
    | Creates/updates playlists in database
    | Dispatches SyncPlaylistJob for each playlist
    |
    v
SyncPlaylistJob (Queued per Playlist)
    |
    | 1. Fetch playlist tracks from Spotify
    | 2. Create PlaylistSyncDTO
    | 3. Configure pipeline handlers
    |
    v
Pipeline Processing (SyncOrchestrator)
    |
    +---> RemoveDuplicatePlaylistTracksHandler
    |     (Deduplicates tracks)
    |
    +---> NormalizePlaylistTrackDataHandler
    |     (Normalizes data structure)
    |
    +---> ValidatePlaylistTracksHandler
    |     (Validates track data)
    |
    +---> AddLiveVersionsHandler (NEW)
    |     (For each track: search for 2 live versions)
    |     |
    |     +---> SpotifyTracksClient.searchLiveVersions()
    |           (Searches Spotify for live recordings)
    |
    v
Save to Database (SyncOrchestrator.saveToDatabase)
    |
    | Creates/updates tracks, artists
    | Syncs relationships (tracks -> playlists)
    |
    v
Sync to Spotify API (SyncOrchestrator.syncToSpotifyApi) [NEW]
    |
    | Transforms tracks to Spotify URIs
    | Calls SpotifyPlaylistsClient.replaceTracks()
    | Handles chunking (max 100 tracks per request)
    |
    v
Spotify Playlist Updated ✓
```

## Key Components

### 1. SpotifyTracksClient Enhancements
**File**: `app/Services/SpotifyTracksClient.php`

- `search(string $query, int $limit, int $offset): array`
  - Generic Spotify track search
  - Returns array of track items

- `searchLiveVersions(string $trackName, string $artistName, int $limit = 2): array`
  - Specialized search for live track versions
  - Constructs query: `track:"name" artist:"artist" live`
  - Default limit: 2 live versions per track

### 2. AddLiveVersionsHandler Pipeline
**File**: `app/Pipelines/AddLiveVersionsHandler.php`

**Process**:
1. Iterate through each track in DTO
2. Extract track name and primary artist
3. Search for live versions via SpotifyTracksClient
4. Filter out duplicates (same track ID)
5. Add live versions to collection
6. Handle errors gracefully (continue on API failures)

**Features**:
- Early returns for empty collections
- Skips tracks without name or artist
- Logs warnings for failed searches
- Maintains original track order

### 3. SpotifyPlaylistsClient Enhancements
**File**: `app/Services/SpotifyPlaylistsClient.php`

- `replaceTracks(string $playlistId, array $trackUris): array`
  - Replaces all tracks in a playlist
  - Handles empty arrays (clears playlist)
  - Chunks requests (max 100 tracks per call)
  - First chunk uses PUT (replace), subsequent use POST (append)

### 4. SyncOrchestrator Updates
**File**: `app/Orchestrators/SyncOrchestrator.php`

**New Properties**:
- `protected bool $syncToSpotify = false`
- Dependencies: `SpotifyPlaylistsClient`, `PlaylistSyncDTOTransformer`

**New Methods**:
- `setSyncToSpotify(bool $syncToSpotify): self`
  - Enables/disables Spotify API sync
  - Returns self for method chaining

- `syncToSpotifyApi(SyncDTOInterface $dto, object $entity): void`
  - Called after saving to database
  - Only syncs if `$syncToSpotify` is true
  - Only handles Playlist entities currently
  - Transforms DTO tracks to Spotify URIs
  - Calls `replaceTracks()` on Spotify API
  - Logs sync operations

### 5. Dashboard Widget Enhancement
**File**: `app/Filament/Widgets/PlaylistsTableWidget.php`

**New Header Action**:
- **Label**: "Sync All Playlists"
- **Icon**: `heroicon-o-arrow-path` (refresh icon)
- **Features**:
  - Requires confirmation before execution
  - Shows modal with description
  - Dispatches `PopulatePlaylistsJob` to queue
  - Shows success notification
  - Returns immediately (AJAX)

**Modal Content**:
- **Heading**: "Sync All Playlists"
- **Description**: Explains the sync process
- **Submit Label**: "Start Sync"

### 6. Updated SyncPlaylistJob
**File**: `app/Jobs/SyncPlaylistJob.php`

**Pipeline Configuration**:
```php
$orchestrator->setHandlers([
    RemoveDuplicatePlaylistTracksHandler::class,
    NormalizePlaylistTrackDataHandler::class,
    ValidatePlaylistTracksHandler::class,
    AddLiveVersionsHandler::class, // NEW
]);

$orchestrator->setSyncToSpotify(true); // NEW
```

## Test Coverage (23 Tests, 42 Assertions)

### SpotifyTracksClientSearchTest (6 tests)
- ✔ It has search method
- ✔ It searches for tracks with query
- ✔ It returns empty array when no tracks found
- ✔ It has search live versions method
- ✔ It searches for live versions of track
- ✔ It limits live version search results

### AddLiveVersionsHandlerTest (7 tests)
- ✔ It adds live versions to track collection
- ✔ It handles empty track collection
- ✔ It skips tracks without name
- ✔ It skips tracks without artist
- ✔ It filters out duplicate live versions
- ✔ It continues processing on search error
- ✔ It processes multiple tracks with live versions

### SpotifyPlaylistsClientReplaceTracksTest (5 tests)
- ✔ It has replace tracks method
- ✔ It replaces tracks in playlist
- ✔ It handles large track lists with chunking
- ✔ It handles empty track list
- ✔ It replaces exactly 100 tracks without chunking

### SyncOrchestratorSpotifySyncTest (3 tests)
- ✔ It syncs to spotify when enabled
- ✔ It does not sync to spotify when disabled
- ✔ It has set sync to spotify method

### PlaylistsTableWidgetTest (2 tests)
- ✔ It has table method
- ✔ It can be instantiated

## SOLID Principles Applied

### Single Responsibility Principle
- `SpotifyTracksClient`: Search operations only
- `AddLiveVersionsHandler`: Only finds and adds live versions
- `SyncOrchestrator`: Coordinates sync, doesn't perform searches

### Open/Closed Principle
- New `AddLiveVersionsHandler` extends pipeline without modifying existing handlers
- `SyncOrchestrator.syncToSpotifyApi()` is extensible for other entity types

### Liskov Substitution Principle
- `AddLiveVersionsHandler` works with `SyncDTOInterface`
- Can be swapped with other pipeline handlers

### Interface Segregation Principle
- `SyncDTOInterface` provides minimal required methods
- Handlers depend only on interface, not concrete DTO

### Dependency Inversion Principle
- All dependencies injected via constructor
- Orchestrator depends on abstractions (interfaces, abstract classes)
- Configuration in job, not in orchestrator

## Usage Example

```php
// User clicks "Sync All Playlists" button on dashboard
// Action dispatches job:
PopulatePlaylistsJob::dispatch();

// For each playlist, SyncPlaylistJob is dispatched:
// 1. Fetches tracks from Spotify
// 2. Creates DTO
// 3. Runs through pipeline (dedup, normalize, validate, add live versions)
// 4. Saves to database
// 5. Pushes enriched playlist back to Spotify
// 6. User's Spotify playlist now includes original tracks + live versions
```

## Benefits

1. **Automatic Discovery**: Live versions automatically found and added
2. **Bi-directional Sync**: Updates both database and Spotify
3. **Queue-based**: Non-blocking, handles large playlists
4. **Fault Tolerant**: Continues on individual track failures
5. **Scalable**: Chunks large playlists for API limits
6. **Testable**: 23 comprehensive tests with mocking
7. **Maintainable**: Follows SOLID principles and early returns

## Configuration

To enable/disable Spotify sync in a job:

```php
// Enable (default for SyncPlaylistJob)
$orchestrator->setSyncToSpotify(true);

// Disable (default for other operations)
$orchestrator->setSyncToSpotify(false);
```

## Performance Considerations

- Pipeline runs asynchronously via queue
- Live version search: 1 API call per track (can be optimized with caching)
- Playlist update: Chunked in batches of 100 tracks
- Database operations: Bulk upserts where possible

## Future Enhancements

1. Cache live version searches to reduce API calls
2. Configurable number of live versions per track
3. Filter live versions by quality/popularity
4. Support for albums, artists (extend `syncToSpotifyApi()`)
5. Real-time progress updates via websockets
