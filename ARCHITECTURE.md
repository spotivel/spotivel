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

## Filament UI Architecture

### Resource Organization Pattern

```
app/Filament/
├── Pages/
│   └── Dashboard.php                    # Main dashboard with widgets
├── Resources/
│   ├── TrackResource.php                # Resource orchestrator
│   ├── TrackResource/
│   │   ├── Pages/                       # CRUD pages
│   │   │   ├── ListTracks.php
│   │   │   ├── CreateTrack.php
│   │   │   └── EditTrack.php
│   │   ├── Schemas/                     # Form definitions
│   │   │   └── TrackFormSchema.php
│   │   └── Tables/                      # Table definitions
│   │       └── TrackTableSchema.php
│   ├── ArtistResource.php               # Follows same pattern
│   ├── ArtistResource/...
│   ├── AlbumResource.php
│   ├── AlbumResource/...
│   ├── PlaylistResource.php
│   └── PlaylistResource/...
└── Widgets/                             # Dashboard widgets
    ├── TracksTableWidget.php
    ├── ArtistsTableWidget.php
    ├── AlbumsTableWidget.php
    └── PlaylistsTableWidget.php
```

### Filament Resource Pattern (SOLID Compliance)

**Single Responsibility Principle:**
```
TrackResource (Orchestrator)
    ├── TrackFormSchema (Form definition only)
    └── TrackTableSchema (Table definition only)
```

Each class has ONE job:
- **FormSchema**: Define form fields and validation
- **TableSchema**: Define columns, filters, actions, and bulk actions
- **Resource**: Orchestrate schemas and route to pages
- **Pages**: Handle Livewire lifecycle and user interactions

### Data Flow in Filament Resources

```
1. User Request
   ┌────────────────┐
   │  Browser/UI    │
   └──────┬─────────┘
          │
          ▼
2. Filament Resource
   ┌────────────────────┐
   │  TrackResource     │
   │  form()            │
   │  table()           │
   └──────┬─────────────┘
          │
          ├──────► TrackFormSchema::make()  ──► [Form Fields]
          │
          └──────► TrackTableSchema
                      ├──► columns()         ──► [Table Columns]
                      ├──► filters()         ──► [Filters]
                      ├──► actions()         ──► [Row Actions]
                      ├──► bulkActions()     ──► [Bulk Actions]
                      └──► headerActions()   ──► [Populate Button]
          │
          ▼
3. Livewire Page Component
   ┌──────────────────────┐
   │  CreateTrack.php     │
   │  EditTrack.php       │
   │  ListTracks.php      │
   └──────┬───────────────┘
          │
          ▼
4. Database Model
   ┌──────────────────┐
   │   Track Model    │
   └──────┬───────────┘
          │
          ▼
5. Database
   ┌──────────────┐
   │   tracks     │
   └──────────────┘
```

### Dashboard Widget Architecture

```
Dashboard Page
    │
    ├──► getHeaderWidgets()
    │       ├──► TracksTableWidget       [Latest 5 Tracks]
    │       ├──► ArtistsTableWidget      [Latest 5 Artists]
    │       ├──► AlbumsTableWidget       [Latest 5 Albums]
    │       └──► PlaylistsTableWidget    [Latest 5 Playlists]
    │
    └──► Blade View
            └──► Renders widgets in grid layout
```

**Widget Pattern:**
```php
class TracksTableWidget extends TableWidget
{
    // Query limited to 5 records
    query(Track::query()->latest()->limit(5))
    
    // Columns configuration
    columns([...])
    
    // Widget metadata
    heading('Recent Tracks')
}
```

### Modal Behavior

Filament resources use modals for create/edit operations:

```
User clicks "Create Track"
    │
    ▼
Modal Opens with Form
    │
    ├──► Form Fields from TrackFormSchema
    ├──► Client-side validation
    └──► User fills form
    │
    ▼
User clicks "Create"
    │
    ├──► Server-side validation
    ├──► If valid: Save to database
    ├──► If invalid: Show errors in modal
    │
    ▼
Success
    ├──► Modal closes
    ├──► Table refreshes
    └──► Success notification
```

### Testing Strategy for Filament Resources

```
Feature Tests (tests/Feature/Filament/)
    │
    ├──► List Page Tests
    │       ├── Can see records
    │       ├── Can search
    │       ├── Can filter
    │       └── Can sort
    │
    ├──► Create Tests
    │       ├── Can fill form
    │       ├── Can submit form
    │       ├── Validates required fields
    │       ├── Validates max lengths
    │       └── Validates data types
    │
    ├──► Edit Tests
    │       ├── Can load existing data
    │       ├── Can update fields
    │       └── Validates changes
    │
    ├──► Delete Tests
    │       ├── Can delete single record
    │       └── Can bulk delete
    │
    └──► Edge Case Tests
            ├── Empty data
            ├── Invalid URLs
            ├── Boundary conditions
            └── Optional vs required fields
```

### Filament + Service Layer Integration

```
Filament Resource (UI Layer)
    │
    ├──► Create/Update Form
    │       │
    │       ▼
    │   Eloquent Model::create()
    │   Eloquent Model::update()
    │       │
    │       ▼
    │   Database (Direct)
    │
    └──► "Populate" Action (Header Button)
            │
            ▼
        Job Dispatch
            │
            ▼
        Service Layer
        (TrackService, ArtistService, etc.)
            │
            ▼
        Pipeline Processing
            │
            ▼
        Database (Via Services)
```

**Key Insight:** 
- Manual CRUD through Filament UI → Direct Eloquent operations
- Bulk sync via "Populate" → Job → Service Layer → Pipeline → Database

This separation ensures:
1. Simple operations remain simple (CRUD)
2. Complex operations go through proper abstractions (Sync)
3. No duplication of business logic
4. Clear boundaries between UI and business logic

## Architecture Benefits Summary

### Separation of Concerns
- **UI Layer** (Filament): User interaction, forms, tables
- **Service Layer**: Business logic, data transformation
- **Data Layer**: Models, relationships, persistence
- **Integration Layer**: API clients, external services

### Testability
- Filament resources: Feature tests with Livewire
- Services: Unit tests with mocked dependencies
- Models: Unit tests with factories
- Pipelines: Unit tests with DTOs

### Maintainability
- Each file has a single purpose
- Changes localized to specific layers
- Easy to find and modify code
- Clear naming conventions

### Extensibility
- Add new resources by following established patterns
- Add new widgets without modifying existing ones
- Add new pipeline handlers without changing orchestrators
- Add new services without touching jobs

### SOLID Compliance Throughout
- **S**: Each class (Resource, Schema, Widget, Service) has one job
- **O**: Extend via new classes (handlers, services, widgets)
- **L**: All decorators, clients, DTOs are interchangeable
- **I**: Focused interfaces (HttpClientInterface, SyncDTOInterface)
- **D**: Depend on abstractions, not concretions
