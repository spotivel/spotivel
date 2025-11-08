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
