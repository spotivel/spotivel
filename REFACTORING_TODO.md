# Refactoring TODO List

This document tracks the remaining refactoring tasks based on code review comments.

## ✅ Completed (Commits 257fc96, 779c14a)

- [x] Add Laravel storage directory structure
- [x] Remove JSON columns from all migrations
- [x] Remove JSON casts from all models
- [x] Remove available_markets field completely from Track model

## 🚧 Remaining Tasks

### Job Refactoring (Remove JSON Field References)

**File: `app/Jobs/PopulateAlbumsJob.php`**
- [ ] Remove `'images' => $albumData['images'] ?? null` assignment (line 44)
- [ ] Remove `'available_markets' => $albumData['available_markets'] ?? null` assignment (line 43)

**File: `app/Jobs/PopulateArtistsJob.php`**
- [ ] Remove `'genres' => $artistData['genres'] ?? null` assignment (line 38)
- [ ] Remove `'images' => $artistData['images'] ?? null` assignment (line 41)

**File: `app/Jobs/PopulatePlaylistsJob.php`**
- [ ] Remove `'images' => $playlistData['images'] ?? null` assignment (line 51)

**File: `app/Jobs/PopulateTracksJob.php`**
- [ ] Remove `'available_markets' => $trackData['available_markets'] ?? null` assignment (line 67)

**File: `app/Jobs/SyncPlaylistJob.php`**
- [ ] Remove any JSON field references in track syncing

### Filament 4 Standards (Comment ID: 2506133565)

**Location: `app/Filament/Resources/AlbumResource.php:21`**
- [ ] Refactor to use Schema instead of Form
- [ ] Create separate Schema classes in `app/Filament/Resources/AlbumResource/Schemas/`
- [ ] Create separate Table classes in `app/Filament/Resources/AlbumResource/Tables/`
- [ ] Apply same pattern to TrackResource, ArtistResource, PlaylistResource

### DTO & Transformer Pattern (Comment ID: 2506137904)

**Location: `app/Jobs/SyncPlaylistJob.php:43`**
- [ ] Create `app/Transformers/PlaylistSyncDTOTransformer.php`
- [ ] Add fluent getters and setters to `PlaylistSyncDTO`
- [ ] Refactor DTO construction to use Transformer
- [ ] Example pattern:
  ```php
  class PlaylistSyncDTO {
      public function playlistId(): int { return $this->playlistId; }
      public function spotifyId(): string { return $this->spotifyId; }
      public function tracks(): array { return $this->tracks; }
      public function withTracks(array $tracks): self { ... }
  }
  
  class PlaylistSyncDTOTransformer {
      public function transform(Playlist $playlist, array $tracks): PlaylistSyncDTO { ... }
  }
  ```

### Orchestrator Pattern (Comment ID: 2506141568)

**Location: `app/Jobs/SyncPlaylistJob.php:54`**
- [ ] Create `app/Orchestrators/PlaylistSyncOrchestrator.php`
- [ ] Move pipeline coordination logic from SyncPlaylistJob to Orchestrator
- [ ] SyncPlaylistJob should only dispatch to Orchestrator
- [ ] Example:
  ```php
  class PlaylistSyncOrchestrator {
      public function sync(PlaylistSyncDTO $dto): void {
          $processedDTO = app(Pipeline::class)
              ->send($dto)
              ->through([...handlers...])
              ->thenReturn();
          
          $this->saveToDatabase($processedDTO);
      }
  }
  ```

### SpotifyPlaylistsClient (Comment ID: 2506144155)

**Location: `app/Jobs/SyncPlaylistJob.php:76`**
- [ ] Create `app/Services/SpotifyPlaylistsClient.php` extending SpotifyClient
- [ ] Implement `list(string $playlistId, int $limit = 100, int $offset = 0): array` method
- [ ] Move fetchPlaylistTracks logic to this client
- [ ] Refactor SyncPlaylistJob to use `$playlistsClient->list($playlistId)`

### Database Services (Comment ID: 2506147097)

**Location: `app/Jobs/SyncPlaylistJob.php:111`**
- [ ] Create `app/Services/Database/TrackService.php`
- [ ] Create `app/Services/Database/ArtistService.php`
- [ ] Create `app/Services/Database/AlbumService.php`
- [ ] Create `app/Services/Database/PlaylistService.php`
- [ ] Each service should handle CRUD and relationship syncing for its entity
- [ ] Move database operations from jobs to appropriate services
- [ ] Apply Single Responsibility Principle
- [ ] Example:
  ```php
  class TrackService {
      public function createOrUpdate(array $data): Track { ... }
      public function syncArtists(Track $track, array $artistIds): void { ... }
      public function syncToPlaylist(Track $track, Playlist $playlist, int $position): void { ... }
  }
  ```

### Pipeline Refactoring (Comment ID: 2506150909)

**Location: `app/Pipelines/RemoveDuplicatePlaylistTracksHandler.php:27`**
- [ ] Refactor to use Collection `unique()` method with closure
- [ ] Make tracks unique by combination: `spotify_id|duration_ms|popularity`
- [ ] Example:
  ```php
  public function handle(PlaylistSyncDTO $dto, Closure $next) {
      $tracks = collect($dto->getTracks());
      
      $uniqueTracks = $tracks->unique(function ($track) {
          return $track['id'] . '|' . 
                 ($track['duration_ms'] ?? '') . '|' . 
                 ($track['popularity'] ?? '');
      });
      
      return $next($dto->withTracks($uniqueTracks->values()->all()));
  }
  ```

### Decorator Simplification (Comment ID: 2506161943)

**Location: `app/Services/Decorators/HttpClientExceptionDecorator.php:9`**
- [ ] Remove `setBaseUrl()`, `setHeaders()`, `setTimeout()` from all Decorators
- [ ] Move these methods to `SpotifyClient` only
- [ ] Decorators should focus on single responsibility (exception handling, logging)
- [ ] Apply to:
  - `HttpClientExceptionDecorator`
  - `RequestLoggerDecorator`
- [ ] SpotifyClient should configure the ExternalClient directly

### Nord Theme (Comment ID: 2506156653)

**Location: `app/Providers/Filament/AdminPanelProvider.php:31`**
- [ ] Add Nord theme to Filament panel
- [ ] Configure viteTheme in panel configuration
- [ ] Reference: https://filamentphp.com/docs/4.x/panels/themes

### Documentation Updates

**README.md**
- [ ] Add section on SOLID Principles
- [ ] Add section on Dynamic Programming approach
- [ ] Add examples of early returns pattern
- [ ] Document Single Responsibility Principle usage

**Other .md files**
- [ ] Update ARCHITECTURE.md with service layer
- [ ] Update IMPLEMENTATION.md with DTO/Transformer pattern
- [ ] Add SOLID_PRINCIPLES.md document

**Junie and Copilot Instructions**
- [ ] Create/update `.github/copilot-instructions.md`
- [ ] Document SOLID principles requirement
- [ ] Document no-JSON policy
- [ ] Document early returns pattern

## Implementation Priority

1. **High Priority** (Critical for functionality)
   - Job refactoring (remove JSON field references)
   - Database Services (SOLID principle)
   - SpotifyPlaylistsClient

2. **Medium Priority** (Improves architecture)
   - DTO & Transformer pattern
   - Orchestrator pattern
   - Pipeline refactoring
   - Decorator simplification

3. **Low Priority** (Polish)
   - Filament 4 standards (Schema separation)
   - Nord theme
   - Documentation updates

## Notes

- All changes should follow SOLID principles, especially Single Responsibility Principle
- Use early returns to avoid nested conditions
- Apply dynamic programming where applicable
- No JSON columns/fields allowed
- Each class should have one clear responsibility
