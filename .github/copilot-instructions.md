# GitHub Copilot Instructions for Spotivel

## Project Overview
This is a Laravel 12 application with Filament 4 for syncing Spotify tracks and playlists. The codebase was created by **1 programmer in 1 single day** keeping SOLID principles in mind, applying dynamic programming, and using early returns throughout. **Maximum abstraction** is applied wherever possible.

## Critical Rules

### 1. No JSON Columns Policy
**NEVER** add JSON columns to any migration or model. This is a strict architectural decision.
- Store relationships in pivot tables instead
- Use normalized data structures
- Arrays from APIs should be processed and stored in relational format

### 2. SOLID Principles (Mandatory)

**Single Responsibility Principle (SRP)**
- Each class must have exactly one responsibility
- Database Services handle CRUD operations for ONE entity type (TrackService, ArtistService, etc.)
- Generic SyncOrchestrator coordinates pipeline workflows for ALL entity types
- Jobs only dispatch to services/orchestrators - NO business logic in jobs
- Pipeline handlers perform ONE transformation each

**Open/Closed Principle (OCP)**
- Extend functionality through new classes, not modifications
- Add new pipeline handlers without modifying existing code
- Use decorators for cross-cutting concerns (logging, exception handling)

**Liskov Substitution Principle (LSP)**
- SpotifyTracksClient can substitute SpotifyClient where needed
- SpotifyPlaylistsClient can substitute SpotifyClient
- All decorators implement HttpClientInterface and can substitute each other
- All DTOs implement SyncDTOInterface and work with SyncOrchestrator

**Interface Segregation Principle (ISP)**
- HttpClientInterface only requires `request()` method
- NO configuration methods (setBaseUrl, setHeaders, setTimeout) in interface
- Configuration happens in concrete implementation (ExternalClient) via constructor
- SyncDTOInterface provides minimal required methods

**Dependency Inversion Principle (DIP)**
- Jobs depend on abstractions (SyncOrchestrator, Services), not concrete implementations
- ALL dependencies injected via constructor (property promotion pattern)
- Configuration in ServiceProvider, not in classes

### 3. Early Returns Pattern (Mandatory)
Always use early returns to avoid nested conditionals:

```php
// CORRECT - Early return
if (!isset($data['items']) || empty($data['items'])) {
    return; // Early exit
}
// Continue processing...

// WRONG - Nested conditions
if (isset($data['items']) && !empty($data['items'])) {
    // Deep nesting here - BAD
}
```

Examples throughout codebase:
- Pipeline handlers check conditions early and return
- Orchestrator returns early if no handlers configured
- Service methods validate input and return early on failure

### 4. Maximum Abstraction

**Abstract Everything:**
- Create interfaces for all contracts (HttpClientInterface, SyncDTOInterface)
- Use generic SyncOrchestrator instead of entity-specific orchestrators
- Pipeline handlers are abstract and reusable
- Transformers handle all DTO creation/transformation logic
- Services abstract all database operations

**Polymorphism:**
- SyncOrchestrator handles ANY entity type (Playlist, Track, Album, etc.)
- Handlers work with ANY DTO implementing SyncDTOInterface
- ServiceProvider configures concrete implementations

### 5. Testing (Comprehensive Coverage Required)

**PHPUnit Tests:**
- ALL service classes MUST have comprehensive test coverage
- Use Model Factories with Faker for realistic test data
- Test all methods, edge cases, optional fields, and relationships
- RefreshDatabase trait for isolated test environments

**JSON Fixtures:**
- Use actual Spotify API response JSON files in `tests/Fixtures/Spotify/`
- Fixtures for: track.json, artist.json, album.json, playlist.json, saved_tracks.json, playlist_tracks.json
- Mock API responses with real data structures

**Test Coverage Requirements:**
- Database Services: 100% method coverage (40+ tests created)
- Pipeline Handlers: Test transformations
- Orchestrators: Test workflow coordination
- DTOs: Test immutability and transformation

### 6. Architecture Patterns

**Property Promotion (PHP 8.1+):**
```php
public function __construct(
    protected HttpClientInterface $client,
    protected string $baseUrl = '',
    protected array $headers = []
) {}
```

**Generic SyncOrchestrator:**
```php
$orchestrator->setHandlers([
    RemoveDuplicatePlaylistTracksHandler::class,
    NormalizePlaylistTrackDataHandler::class,
    ValidatePlaylistTracksHandler::class,
]);
$orchestrator->sync($dto, $entity);
```

**DTO Interface Pattern:**
```php
interface SyncDTOInterface {
    public function entityId(): int;
    public function spotifyId(): string;
    public function data(): Collection;
    public function metadata(): array;
    public function withData(Collection $data): self; // Immutability
}
```

**Service Layer:**
- Location: `app/Services/Database/`
- Pattern: One service per entity (TrackService, ArtistService, AlbumService, PlaylistService)
- Methods: `createOrUpdate()`, `syncRelationship()`
- All database operations MUST go through services

**Pipeline Pattern:**
- Location: `app/Pipelines/`
- Each handler has single responsibility
- Handlers receive and return DTOs
- Use `withData()` for immutability
- Collection-based operations using `unique()` with closures

**DTO & Transformer:**
- DTOs in `app/DTOs/`, implement SyncDTOInterface
- Transformers in `app/Transformers/`
- Fluent getters: `entityId()`, `spotifyId()`, `data()`
- Immutable setters: `withData()` returns clone
- Reverse transformers: `toSpotifyPayload()`, `tracksToSpotifyUris()`

**Specialized Clients:**
- SpotifyTracksClient: Track operations
- SpotifyPlaylistsClient: Playlist operations  
- ALL extend SpotifyClient which uses ExternalClient via DI
- Configuration in ServiceProvider with decorators

**Filament 4 Standards:**
- Separate Schemas and Tables into dedicated classes
- FormSchema: `app/Filament/Resources/{Resource}/Schemas/`
- TableSchema: `app/Filament/Resources/{Resource}/Tables/`
- Resource calls schema classes: `AlbumFormSchema::make()`, `AlbumTableSchema::columns()`

### 7. Http Client Architecture

**ExternalClient:**
- ONLY has `request()` method implementing HttpClientInterface
- Uses constructor property promotion for config (baseUrl, headers, timeout)
- Automatically calls `->throw()` on Http facade for exception handling
- NO setter methods (setBaseUrl, setHeaders, setTimeout)

**SpotifyClient:**
- Uses HttpClientInterface via DI (property promotion)
- ONLY has `request()` method
- Configuration in ServiceProvider with auth headers

**Decorators (Simplified):**
- RequestLoggerDecorator: ONLY logging, ONLY `request()` method
- HttpClientExceptionDecorator: ONLY exception handling, ONLY `request()` method
- NO configuration methods in decorators

**ServiceProvider Configuration:**
```php
$client = new ExternalClient(
    baseUrl: 'https://api.spotify.com/v1',
    headers: ['Authorization' => 'Bearer '.$token],
    timeout: 30
);
$client = new HttpClientExceptionDecorator($client);
$client = new RequestLoggerDecorator($client);
```

### 8. Code Style

**Linting:**
- ALWAYS run `./vendor/bin/pint` before committing
- Follow Laravel Pint standards
- Auto-fix all style issues

**Type Declarations:**
- Type hint all parameters and return types
- Use union types where applicable
- Strict types when appropriate

**Naming Conventions:**
- Services: `{Entity}Service` (TrackService, ArtistService)
- Orchestrators: `SyncOrchestrator` (generic for all entities)
- DTOs: `{Entity}SyncDTO` implementing SyncDTOInterface
- Pipeline handlers: `{Action}{Entity}Handler` (RemoveDuplicatePlaylistTracksHandler)
- Form schemas: `{Entity}FormSchema` 
- Table schemas: `{Entity}TableSchema`

### 9. Documentation

**Update when adding features:**
- README.md: Architecture and SOLID principles
- ARCHITECTURE.md: Diagrams and patterns
- IMPLEMENTATION.md: Code examples and usage
- Inline comments: Complex logic only

**Document:**
- Abstractions used
- Early return patterns
- SOLID principle applications
- Dynamic programming optimizations

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

### Creating a DTO
```php
namespace App\DTOs;

use App\Contracts\SyncDTOInterface;
use Illuminate\Support\Collection;

class YourEntitySyncDTO implements SyncDTOInterface
{
    public function __construct(
        private int $entityId,
        private string $spotifyId,
        private Collection $data,
        private array $metadata = []
    ) {}
    
    public function entityId(): int { return $this->entityId; }
    public function spotifyId(): string { return $this->spotifyId; }
    public function data(): Collection { return $this->data; }
    public function metadata(): array { return $this->metadata; }
    
    public function withData(Collection $data): self {
        $clone = clone $this;
        $clone->data = $data;
        return $clone;
    }
}
```

### Creating a Pipeline Handler
```php
namespace App\Pipelines;

use App\Contracts\SyncDTOInterface;
use Closure;

class YourTransformationHandler
{
    public function handle(SyncDTOInterface $dto, Closure $next)
    {
        // Early return if condition not met
        if ($dto->data()->isEmpty()) {
            return $next($dto);
        }
        
        // Perform single transformation
        $transformedData = $dto->data()->unique(fn($item) => $item['id']);
        
        return $next($dto->withData($transformedData));
    }
}
```

### Job Structure
```php
public function handle(
    SpecializedClient $client,
    SyncOrchestrator $orchestrator
): void {
    // 1. Fetch data
    $data = $client->fetchData();
    
    // 2. Create DTO
    $dto = new YourEntitySyncDTO(/* params */);
    
    // 3. Configure handlers
    $orchestrator->setHandlers([
        RemoveDuplicatesHandler::class,
        NormalizeHandler::class,
        ValidateHandler::class,
    ]);
    
    // 4. Dispatch to orchestrator
    $orchestrator->sync($dto, $entity);
}
```

### Filament Resource with Separated Schemas
```php
class YourResource extends Resource
{
    public static function form(Form $form): Form {
        return $form->schema(YourFormSchema::make());
    }

    public static function table(Table $table): Table {
        return $table
            ->columns(YourTableSchema::columns())
            ->filters(YourTableSchema::filters())
            ->actions(YourTableSchema::actions())
            ->bulkActions(YourTableSchema::bulkActions())
            ->headerActions(YourTableSchema::headerActions());
    }
}
```

## Anti-Patterns to Avoid

❌ Direct model operations in jobs  
❌ Business logic in controllers  
❌ JSON columns in migrations  
❌ Nested conditionals (use early returns)  
❌ God classes with multiple responsibilities  
❌ Modifying closed classes instead of extending  
❌ Configuration methods in decorators or interfaces  
❌ Entity-specific orchestrators (use generic SyncOrchestrator)  
❌ Hardcoded handlers in orchestrator (configure in jobs)  

## Questions to Ask Before Coding

1. Does this class have a single, clear responsibility?
2. Am I adding a JSON column? (If yes, STOP and redesign)
3. Should this be a service, handler, or use existing abstraction?
4. Am I using early returns to avoid nesting?
5. Are my dependencies injected via constructor (property promotion)?
6. Does this follow existing abstractions (SyncDTOInterface, SyncOrchestrator)?
7. Can I use the generic SyncOrchestrator instead of creating new orchestrator?
8. Have I created comprehensive PHPUnit tests?
9. Is maximum abstraction applied?
10. Would this work in a dynamic programming context?

## File Organization

```
app/
├── Contracts/
│   ├── HttpClientInterface.php       # Only request()
│   └── SyncDTOInterface.php          # DTO contract
├── Services/
│   ├── Database/                     # Entity services (CRUD)
│   │   ├── TrackService.php
│   │   ├── ArtistService.php
│   │   └── ...
│   ├── ExternalClient.php            # Base HTTP with property promotion
│   ├── SpotifyClient.php             # Uses ExternalClient via DI
│   ├── SpotifyTracksClient.php
│   ├── SpotifyPlaylistsClient.php
│   └── Decorators/                   # Single-responsibility decorators
│       ├── RequestLoggerDecorator.php
│       └── HttpClientExceptionDecorator.php
├── Orchestrators/
│   └── SyncOrchestrator.php          # Generic for ALL entity types
├── Pipelines/                        # Data transformation handlers
├── DTOs/                             # Data transfer objects (implement SyncDTOInterface)
├── Transformers/                     # DTO creation + reverse transformation
├── Jobs/                             # Thin dispatchers (configure handlers)
├── Filament/
│   └── Resources/
│       └── {Resource}/
│           ├── Schemas/              # Form schemas
│           └── Tables/               # Table schemas
└── Models/                           # Eloquent models

tests/
├── Fixtures/
│   └── Spotify/                      # JSON fixtures for API responses
└── Unit/
    └── Services/
        └── Database/                 # Comprehensive service tests
```

## Dynamic Programming & Performance

- Use Collection `unique()` with closures for deduplication
- Batch database operations to minimize queries
- Early returns prevent unnecessary processing
- Pipeline pattern allows composable optimizations
- Memoization where applicable

## Development Commands

### Setup
```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate
```

### Testing
```bash
# Run all tests
./vendor/bin/phpunit

# Run tests with verbose output
./vendor/bin/phpunit --testdox

# Run specific test suite
./vendor/bin/phpunit --testsuite=Unit
./vendor/bin/phpunit --testsuite=Feature

# Run specific test file
./vendor/bin/phpunit tests/Unit/Services/Database/TrackServiceTest.php
```

### Linting & Code Quality
```bash
# Run Pint (auto-fix code style)
./vendor/bin/pint

# Run Pint in test mode (check without fixing)
./vendor/bin/pint --test

# Run PHPStan (static analysis)
./vendor/bin/phpstan analyse
```

### Development Server
```bash
# Start Laravel development server
php artisan serve

# Access Filament admin panel
# Navigate to: http://localhost:8000/admin
```

## CI/CD Workflows

This repository uses GitHub Actions for continuous integration:

### Available Workflows
- **PHPUnit** (`.github/workflows/phpunit.yml`) - Runs test suite
- **Pint** (`.github/workflows/pint.yml`) - Validates code style
- **PHPStan** (`.github/workflows/phpstan.yml`) - Runs static analysis

All workflows are manually triggered via `workflow_dispatch` and support PHP 8.2 and 8.3.

### Pre-commit Checklist
Before committing code, ensure:
```bash
# 1. Code style is correct
./vendor/bin/pint

# 2. Static analysis passes
./vendor/bin/phpstan analyse

# 3. All tests pass
./vendor/bin/phpunit
```

## Environment Requirements

- **PHP**: ^8.2
- **Laravel**: ^11.0
- **Filament**: ^4.0
- **Database**: MySQL/MariaDB/PostgreSQL/SQLite (SQLite for testing)

## Remember

**When in doubt:**
1. Favor maximum abstraction
2. Use existing generic patterns (SyncOrchestrator, SyncDTOInterface)
3. Apply SOLID principles
4. Use early returns
5. Write comprehensive tests
6. Follow single responsibility
7. Keep it simple and clean

**This codebase was built in 1 day with strict principles - maintain that standard!**
