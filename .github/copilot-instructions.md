# GitHub Copilot Instructions for Spotivel

## Project Overview
This is a Laravel 12 application with Filament 4 for syncing Spotify tracks and playlists. The codebase strictly follows SOLID principles and employs various design patterns for maintainability and testability.

## Critical Rules

### 1. No JSON Columns Policy
**NEVER** add JSON columns to any migration or model. This is a strict architectural decision.
- Store relationships in pivot tables instead
- Use normalized data structures
- Arrays from APIs should be processed and stored in relational format

### 2. SOLID Principles (Mandatory)

**Single Responsibility Principle**
- Each class must have exactly one responsibility
- Services handle database operations for ONE entity type
- Jobs only dispatch to services/orchestrators
- Pipeline handlers perform ONE transformation

**Open/Closed Principle**
- Extend functionality through new classes, not modifications
- Use decorators, inheritance, or new pipeline handlers

**Liskov Substitution Principle**
- Subtypes must be substitutable for their base types
- All decorators must implement HttpClientInterface

**Interface Segregation Principle**
- Keep interfaces minimal and focused
- HttpClientInterface only requires `request()` method

**Dependency Inversion Principle**
- Depend on abstractions, not concretions
- Inject dependencies via constructor

### 3. Early Returns Pattern
Always use early returns to avoid nested conditionals:

```php
// CORRECT
if (!isset($data['items']) || empty($data['items'])) {
    return;
}
// Continue processing...

// WRONG
if (isset($data['items']) && !empty($data['items'])) {
    // Deep nesting here
}
```

### 4. Architecture Patterns

**Service Layer**
- Location: `app/Services/Database/`
- One service per entity: TrackService, ArtistService, AlbumService, PlaylistService
- Methods: `createOrUpdate()`, `syncRelationship()`
- All database operations go through services

**Orchestrator Pattern**
- Location: `app/Orchestrators/`
- Coordinate complex workflows
- Manage pipeline execution
- Call multiple services in sequence

**Pipeline Pattern**
- Location: `app/Pipelines/`
- Each handler has single responsibility
- Handlers receive and return DTOs
- Use `withTracks()` for immutability

**DTO & Transformer**
- DTOs in `app/DTOs/`
- Transformers in `app/Transformers/`
- Fluent getters: `playlistId()`, `spotifyId()`, `tracks()`
- Immutable setters: `withTracks()` returns clone

**Specialized Clients**
- SpotifyTracksClient: Track operations
- SpotifyPlaylistsClient: Playlist operations
- Extend SpotifyClient for specialized functionality

### 5. Decorator Simplification
Decorators should ONLY implement the `request()` method from HttpClientInterface.
- NO `setBaseUrl()` in decorators
- NO `setHeaders()` in decorators  
- NO `setTimeout()` in decorators
- These configuration methods belong only to ExternalClient and SpotifyClient

### 6. Code Style

**Linting**
- Always run `./vendor/bin/pint` before committing
- Follow Laravel Pint standards

**Type Declarations**
- Use strict types: `declare(strict_types=1);` (if appropriate for Laravel)
- Type hint all parameters and return types
- Use union types where applicable

**Naming Conventions**
- Services: `{Entity}Service` (e.g., TrackService)
- Orchestrators: `{Process}Orchestrator` (e.g., PlaylistSyncOrchestrator)
- DTOs: `{Entity}{Purpose}DTO` (e.g., PlaylistSyncDTO)
- Pipeline handlers: `{Action}{Entity}Handler` (e.g., RemoveDuplicatePlaylistTracksHandler)

### 7. Testing
- Write unit tests for all new services
- Mock external dependencies
- Test single responsibility of each class
- Verify SOLID principles in tests

### 8. Documentation
When adding new features:
- Update README.md if affecting architecture
- Update ARCHITECTURE.md for new patterns
- Update IMPLEMENTATION.md for implementation details
- Add inline comments for complex logic only

## Common Patterns

### Creating a New Service
```php
namespace App\Services\Database;

use App\Models\YourEntity;

class YourEntityService
{
    public function createOrUpdate(array $data): YourEntity
    {
        return YourEntity::updateOrCreate(
            ['spotify_id' => $data['id']],
            [/* mapped fields */]
        );
    }
    
    public function syncRelationship(YourEntity $entity, array $ids): void
    {
        $entity->relationship()->sync($ids);
    }
}
```

### Creating a Pipeline Handler
```php
namespace App\Pipelines;

use App\DTOs\YourDTO;
use Closure;

class YourTransformationHandler
{
    public function handle(YourDTO $dto, Closure $next)
    {
        // Perform single transformation
        $transformedData = $dto->data()->map(/* transformation */);
        
        return $next($dto->withData($transformedData));
    }
}
```

### Job Structure
```php
public function handle(
    SpecializedClient $client,
    YourOrchestrator $orchestrator
): void {
    // 1. Fetch data
    $data = $client->fetchData();
    
    // 2. Create DTO
    $dto = new YourDTO(/* params */);
    
    // 3. Dispatch to orchestrator
    $orchestrator->process($dto);
}
```

## Anti-Patterns to Avoid

❌ Direct model operations in jobs
❌ Business logic in controllers
❌ JSON columns in migrations
❌ Nested conditionals (use early returns)
❌ God classes with multiple responsibilities
❌ Modifying closed classes instead of extending
❌ Configuration methods in decorators

## Questions to Ask Before Coding

1. Does this class have a single, clear responsibility?
2. Am I adding a JSON column? (If yes, STOP and redesign)
3. Should this be a service, orchestrator, or pipeline handler?
4. Am I using early returns to avoid nesting?
5. Are my dependencies injected via constructor?
6. Does this follow the existing architectural patterns?
7. Will this require a test?

## File Organization

```
app/
├── Services/
│   ├── Database/          # Entity services (CRUD)
│   │   ├── TrackService.php
│   │   ├── ArtistService.php
│   │   └── ...
│   ├── SpotifyClient.php
│   ├── SpotifyTracksClient.php
│   ├── SpotifyPlaylistsClient.php
│   └── Decorators/        # Single-responsibility decorators
├── Orchestrators/         # Workflow coordination
├── Pipelines/             # Data transformation handlers
├── DTOs/                  # Data transfer objects
├── Transformers/          # DTO creation logic
├── Jobs/                  # Thin dispatchers
└── Models/                # Eloquent models
```

Remember: When in doubt, favor simplicity, single responsibility, and following existing patterns over clever solutions.
