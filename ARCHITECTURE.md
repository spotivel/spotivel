# Architecture Diagram

## Database Schema Relationships

```
┌─────────────┐         ┌──────────────────┐         ┌─────────────┐
│   Artists   │◄───────►│  artist_track    │◄───────►│   Tracks    │
│             │         │  (Pivot Table)   │         │             │
│ - id        │         │ - artist_id      │         │ - id        │
│ - spotify_id│         │ - track_id       │         │ - spotify_id│
│ - name      │         └──────────────────┘         │ - name      │
│ - genres    │                                       │ - duration  │
│ - popularity│         ┌──────────────────┐         │ - popularity│
└─────────────┘         │  album_track     │         └─────────────┘
       │                │  (Pivot Table)   │                │
       │                │ - album_id       │                │
       │                │ - track_id       │                │
       │                └──────────────────┘                │
       │                         ▲                          │
       │                         │                          │
       │                         │                          │
       │                ┌─────────────┐                     │
       └───────────────►│   Albums    │◄────────────────────┘
                        │             │
                        │ - id        │
                        │ - spotify_id│
                        │ - name      │
                        │ - album_type│
                        │ - total_trks│
                        └─────────────┘
                               ▲
                               │
                               │
                        ┌──────────────────┐
                        │  album_artist    │
                        │  (Pivot Table)   │
                        │ - album_id       │
                        │ - artist_id      │
                        └──────────────────┘
```

## API Client Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    ExternalClient                        │
│  (Base HTTP Client - Used via Composition)               │
│                                                          │
│  + setBaseUrl(url): self                                │
│  + setHeaders(headers): self                             │
│  + setTimeout(timeout): self                             │
│  + get(url, query): mixed                                │
│  + post(url, data): mixed                                │
│  + put(url, data): mixed                                 │
│  + delete(url): mixed                                    │
└─────────────────────────────────────────────────────────┘
                            ▲
                            │
                            │ uses (composition)
                            │
                ┌───────────┴──────────────────────────────┐
                │        SpotifyClient                      │
                │  (Spotify API Base Client)                │
                │                                           │
                │  - client: ExternalClient  ◄──────────────┤ HAS-A
                │  - accessToken: string                    │
                │                                           │
                │  + __construct(token)                     │
                │  + setAccessToken(token): self            │
                │  + getClient(): ExternalClient            │
                │  # get(endpoint, query): mixed            │
                │  # post(endpoint, data): mixed            │
                │  # put(endpoint, data): mixed             │
                │  # delete(endpoint): mixed                │
                └───────────────────────────────────────────┘
                            ▲
                            │
                            │ extends (inheritance)
                            │
                ┌───────────┴──────────────────────────────┐
                │     SpotifyTracksClient                   │
                │  (Specialized Track Operations)           │
                │                                           │
                │  + getTrack(trackId): mixed               │
                │  + getTracks(trackIds): mixed             │
                │  + getAlbumTracks(albumId): mixed         │
                │  + getSavedTracks(): mixed                │
                │  + saveTracks(trackIds): mixed            │
                │  + removeSavedTracks(trackIds): mixed     │
                │  + checkSavedTracks(trackIds): mixed      │
                │  + getTrackAudioFeatures(trackId): mixed  │
                │  + getTracksAudioFeatures(trackIds): mixed│
                │  + getTrackAudioAnalysis(trackId): mixed  │
                │  + getRecommendations(seeds): mixed       │
                └───────────────────────────────────────────┘

                ┌───────────┴──────────────────────────────┐
                │    SpotifyPlaylistsClient                 │
                │  (Specialized Playlist Operations)        │
                │                                           │
                │  + list(playlistId): array                │
                │  + getPage(playlistId, limit, offset)     │
                └───────────────────────────────────────────┘
```

## Service Layer Architecture

```
┌─────────────────────────────────────────────────────────┐
│                   Database Services                      │
│  (Single Responsibility - One service per entity)        │
└─────────────────────────────────────────────────────────┘
                            │
          ┌─────────────────┼─────────────────┬─────────────────┐
          │                 │                 │                 │
          ▼                 ▼                 ▼                 ▼
┌──────────────────┐ ┌─────────────┐ ┌─────────────┐ ┌──────────────────┐
│  TrackService    │ │ArtistService│ │AlbumService │ │PlaylistService   │
│                  │ │             │ │             │ │                  │
│+ createOrUpdate()│ │+ create...()│ │+ create...()│ │+ createOrUpdate()│
│+ syncArtists()   │ │+ syncTracks()│ │+ syncArtists│ │+ syncTracks()    │
│+ syncToPlaylist()│ │+ syncAlbums()│ │+ syncTracks()│ │                 │
└──────────────────┘ └─────────────┘ └─────────────┘ └──────────────────┘
```

## Orchestrator Pattern

```
┌─────────────────────────────────────────────────────────┐
│              PlaylistSyncOrchestrator                    │
│  (Coordinates pipeline workflow)                         │
│                                                          │
│  + sync(dto, playlist): void                             │
│  - saveToDatabase(dto, playlist): void                   │
└─────────────────────────────────────────────────────────┘
                            │
                            │ uses
                            ▼
                    ┌───────────────┐
                    │   Pipeline    │
                    └───────────────┘
                            │
          ┌─────────────────┼─────────────────┐
          │                 │                 │
          ▼                 ▼                 ▼
┌──────────────────┐ ┌─────────────┐ ┌─────────────────┐
│RemoveDuplicates  │ │  Normalize  │ │   Validate      │
│   Handler        │ │   Handler   │ │   Handler       │
└──────────────────┘ └─────────────┘ └─────────────────┘
```

## Decorator Pattern (Simplified)

```
┌─────────────────────────────────────────────────────────┐
│              HttpClientInterface                         │
│  (Focused on single responsibility)                      │
│                                                          │
│  + request(): PendingRequest                             │
└─────────────────────────────────────────────────────────┘
                            ▲
          ┌─────────────────┼─────────────────┐
          │                 │                 │
          │                 │                 │
┌──────────────────┐ ┌─────────────┐ ┌──────────────────┐
│ ExternalClient   │ │Exception    │ │RequestLogger     │
│                  │ │Decorator    │ │Decorator         │
│+ setBaseUrl()    │ │+ request()  │ │+ request()       │
│+ setHeaders()    │ │             │ │                  │
│+ setTimeout()    │ │             │ │                  │
│+ request()       │ │             │ │                  │
└──────────────────┘ └─────────────┘ └──────────────────┘

Note: Configuration methods (setBaseUrl, setHeaders, setTimeout) 
      are only in ExternalClient and SpotifyClient.
      Decorators focus solely on their single responsibility:
      - Exception handling
      - Request logging
```

## Design Patterns Explained

### 1. Composition (SpotifyClient uses ExternalClient)
```
SpotifyClient {
    private ExternalClient $client;  // HAS-A relationship
}
```
**Benefits:**
- Loose coupling
- Can swap HTTP implementations
- Single Responsibility Principle
- Easier to test with mocks

### 2. Inheritance (SpotifyTracksClient extends SpotifyClient)
```
SpotifyTracksClient extends SpotifyClient  // IS-A relationship
```
**Benefits:**
- Code reuse
- Specialized functionality
- Polymorphism
- Logical hierarchy

## Many-to-Many Relationships

### Track ↔ Artist
```php
// A track can have many artists (features, collaborations)
$track->artists()->attach($artistId);

// An artist can have many tracks
$artist->tracks()->attach($trackId);
```

### Album ↔ Artist
```php
// An album can have many artists (compilations, collaborations)
$album->artists()->attach($artistId);

// An artist can have many albums
$artist->albums()->attach($albumId);
```

### Album ↔ Track
```php
// An album can have many tracks
$album->tracks()->attach($trackId);

// A track can be on many albums (different versions, compilations)
$track->albums()->attach($albumId);
```

## Data Flow Example

```
1. API Request
   ┌──────────────┐
   │ Controller   │
   └──────┬───────┘
          │
          ▼
2. Client Usage
   ┌──────────────────────┐
   │ SpotifyTracksClient  │ (extends SpotifyClient)
   └──────┬───────────────┘
          │
          ▼
3. HTTP Request
   ┌──────────────┐
   │ SpotifyClient│ (uses ExternalClient)
   └──────┬───────┘
          │
          ▼
   ┌──────────────────┐
   │ ExternalClient   │
   └──────┬───────────┘
          │
          ▼
4. API Response
   ┌──────────────┐
   │ Spotify API  │
   └──────┬───────┘
          │
          ▼
5. Data Persistence
   ┌──────────────┐
   │ Track Model  │
   │ Artist Model │
   │ Album Model  │
   └──────────────┘
          │
          ▼
6. Database
   ┌──────────────┐
   │  MySQL/DB    │
   └──────────────┘
```

## Updated Data Flow with Service Layer

```
1. Job Dispatch
   ┌──────────────────────┐
   │   SyncPlaylistJob    │
   └──────┬───────────────┘
          │
          ▼
2. Fetch from Spotify
   ┌──────────────────────────┐
   │ SpotifyPlaylistsClient   │ (extends SpotifyClient)
   └──────┬───────────────────┘
          │
          ▼
3. Create DTO
   ┌──────────────────────┐
   │  PlaylistSyncDTO     │
   └──────┬───────────────┘
          │
          ▼
4. Orchestrate Pipeline
   ┌──────────────────────────┐
   │PlaylistSyncOrchestrator  │
   └──────┬───────────────────┘
          │
          ▼
5. Pipeline Processing
   ┌────────────────────┐
   │ RemoveDuplicates   │
   └────────┬───────────┘
            │
            ▼
   ┌────────────────────┐
   │   Normalize Data   │
   └────────┬───────────┘
            │
            ▼
   ┌────────────────────┐
   │  Validate Tracks   │
   └────────┬───────────┘
            │
            ▼
6. Database Services
   ┌────────────────────┐
   │   TrackService     │ ──► createOrUpdate() ──► Track Model
   └────────────────────┘
   ┌────────────────────┐
   │   ArtistService    │ ──► createOrUpdate() ──► Artist Model
   └────────────────────┘
   ┌────────────────────┐
   │  PlaylistService   │ ──► syncTracks() ──► Playlist Model
   └────────────────────┘
            │
            ▼
7. Database Persistence
   ┌──────────────────────┐
   │  MySQL Database      │
   │  - tracks            │
   │  - artists           │
   │  - playlists         │
   │  - pivot tables      │
   └──────────────────────┘
```

## Benefits of This Architecture

1. **Testability**: Each component can be tested in isolation
2. **Maintainability**: Clear separation of concerns
3. **Scalability**: Easy to add new features without modifying existing code
4. **Reusability**: Services can be used by multiple jobs/controllers
5. **SOLID Compliance**: Every class has a single, well-defined responsibility
