# Junie Instructions for Spotivel

## Project Overview
Spotivel is a Laravel 12 application with Filament 4 for syncing Spotify tracks and playlists. The codebase was built in a single day following strict SOLID principles, dynamic programming patterns, and early return conventions. Maximum abstraction is applied throughout.

## Critical Architecture Rules

### 1. No JSON Columns - EVER
**NEVER** add JSON columns to migrations or models. This is a fundamental architectural decision:
- Store relationships in pivot tables
- Use normalized data structures
- Process arrays from APIs into relational format

### 2. SOLID Principles (Mandatory)

**Single Responsibility Principle (SRP)**
- Each class has exactly ONE responsibility
- Database Services handle CRUD for ONE entity type (TrackService, ArtistService, AlbumService, PlaylistService)
- Generic SyncOrchestrator coordinates pipeline workflows for ALL entity types
- Jobs only dispatch to services/orchestrators - NO business logic
- Pipeline handlers perform ONE transformation each

**Open/Closed Principle (OCP)**
- Extend through new classes, never modify existing ones
- Add new pipeline handlers without touching existing code
- Use decorators for cross-cutting concerns (logging, exceptions)

**Liskov Substitution Principle (LSP)**
- SpotifyTracksClient can substitute SpotifyClient
- SpotifyPlaylistsClient can substitute SpotifyClient
- All decorators implement HttpClientInterface and are interchangeable
- All DTOs implement SyncDTOInterface and work with SyncOrchestrator

**Interface Segregation Principle (ISP)**
- HttpClientInterface only requires `request()` method
- NO configuration methods in interfaces (setBaseUrl, setHeaders, setTimeout)
- Configuration happens in concrete implementation via constructor
- SyncDTOInterface provides minimal required methods

**Dependency Inversion Principle (DIP)**
- Jobs depend on abstractions, not concrete implementations
- ALL dependencies injected via constructor (property promotion)
- Configuration in ServiceProvider, not in classes

### 3. Early Returns Pattern (Mandatory)
Always use early returns to avoid nested conditionals:

```php
// ✅ CORRECT
if (!isset($data['items']) || empty($data['items'])) {
    return; // Early exit
}
// Continue processing...

// ❌ WRONG
if (isset($data['items']) && !empty($data['items'])) {
    // Deep nesting - BAD
}
```

### 4. Maximum Abstraction
- Create interfaces for all contracts
- Use generic SyncOrchestrator for ALL entity types
- Pipeline handlers are abstract and reusable
- Transformers handle all DTO creation/transformation
- Services abstract all database operations

### 5. Testing Requirements

**PHPUnit Tests:**
- ALL service classes MUST have comprehensive test coverage
- Use Model Factories with Faker for realistic data
- Test all methods, edge cases, optional fields, and relationships
- RefreshDatabase trait for isolated test environments
- **Tests must test FUNCTIONALITY, not just method existence**

**Filament Tests:**
- Test modals thoroughly using PHPUnit
- Test create, edit, delete actions
- Test form validation
- Test edge cases (empty data, invalid data, boundary conditions)

**JSON Fixtures:**
- Use actual Spotify API response JSON files in `tests/Fixtures/Spotify/`
- Mock API responses with real data structures

### 6. Property Promotion Pattern (PHP 8.1+)
Always use constructor property promotion:

```php
public function __construct(
    protected HttpClientInterface $client,
    protected string $baseUrl = '',
    protected array $headers = []
) {}
```

### 7. Filament 4 Standards

**Resource Structure:**
- Separate Schemas and Tables into dedicated classes
- FormSchema: `app/Filament/Resources/{Resource}/Schemas/{Resource}FormSchema.php`
- TableSchema: `app/Filament/Resources/{Resource}/Tables/{Resource}TableSchema.php`
- Resource calls schema classes: `AlbumFormSchema::make()`, `AlbumTableSchema::columns()`

**Schema Pattern:**
```php
class YourFormSchema
{
    public static function make(): array
    {
        return [
            // Form components
        ];
    }
}
```

**Table Pattern:**
```php
class YourTableSchema
{
    public static function columns(): array { /* ... */ }
    public static function filters(): array { /* ... */ }
    public static function actions(): array { /* ... */ }
    public static function bulkActions(): array { /* ... */ }
    public static function headerActions(): array { /* ... */ }
}
```

**Modals:**
- Filament resources use modals for create/edit forms
- Test modal behavior thoroughly
- Test form submission, validation, and error handling

### 8. Service Layer Architecture
- Location: `app/Services/Database/`
- One service per entity (TrackService, ArtistService, AlbumService, PlaylistService)
- Methods: `createOrUpdate()`, `syncRelationship()`
- ALL database operations MUST go through services

### 9. HTTP Client Architecture

**ExternalClient:**
- ONLY `request()` method implementing HttpClientInterface
- Constructor property promotion for config (baseUrl, headers, timeout)
- Automatically calls `->throw()` on Http facade
- NO setter methods

**SpotifyClient:**
- Uses HttpClientInterface via DI (property promotion)
- ONLY `request()` method
- Configuration in ServiceProvider with auth headers

**Decorators:**
- RequestLoggerDecorator: logging only, `request()` only
- HttpClientExceptionDecorator: exception handling only, `request()` only
- NO configuration methods in decorators

### 10. Code Style

**Linting:**
- ALWAYS run `./vendor/bin/pint` before committing
- Follow Laravel Pint standards

**Type Declarations:**
- Type hint all parameters and return types
- Use union types where applicable

**Naming Conventions:**
- Services: `{Entity}Service`
- Orchestrators: `SyncOrchestrator` (generic)
- DTOs: `{Entity}SyncDTO`
- Pipeline handlers: `{Action}{Entity}Handler`
- Form schemas: `{Entity}FormSchema`
- Table schemas: `{Entity}TableSchema`

## File Organization

```
app/
├── Contracts/
│   ├── HttpClientInterface.php
│   └── SyncDTOInterface.php
├── Services/
│   ├── Database/          # Entity CRUD services
│   ├── ExternalClient.php
│   ├── SpotifyClient.php
│   ├── SpotifyTracksClient.php
│   ├── SpotifyPlaylistsClient.php
│   └── Decorators/
├── Orchestrators/
│   └── SyncOrchestrator.php
├── Pipelines/            # Data transformation handlers
├── DTOs/                 # SyncDTOInterface implementations
├── Transformers/         # DTO creation + reverse transformation
├── Jobs/                 # Thin dispatchers (configure handlers)
├── Filament/
│   ├── Pages/
│   └── Resources/
│       └── {Resource}/
│           ├── Schemas/   # Form schemas
│           └── Tables/    # Table schemas
└── Models/

tests/
├── Fixtures/
│   └── Spotify/          # JSON fixtures
├── Unit/
│   └── Services/
│       └── Database/     # Service tests
└── Feature/              # Filament tests
```

## Common Patterns

### Creating a Service
```php
namespace App\Services\Database;

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

### Filament Resource
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
❌ Hardcoded handlers in orchestrator  
❌ Tests that only check method existence  

## Pre-Commit Checklist

1. Does this class have a single, clear responsibility?
2. Am I adding a JSON column? (If yes, STOP and redesign)
3. Should this be a service, handler, or use existing abstraction?
4. Am I using early returns to avoid nesting?
5. Are dependencies injected via constructor (property promotion)?
6. Does this follow existing abstractions?
7. Can I use generic SyncOrchestrator?
8. Have I created comprehensive PHPUnit tests?
9. Is maximum abstraction applied?
10. Have I run `./vendor/bin/pint`?

## Dynamic Programming & Performance

- Use Collection `unique()` with closures for deduplication
- Batch database operations to minimize queries
- Early returns prevent unnecessary processing
- Pipeline pattern enables composable optimizations
- Memoization where applicable

## When In Doubt

1. Favor maximum abstraction
2. Use existing generic patterns
3. Apply SOLID principles
4. Use early returns
5. Write comprehensive tests
6. Follow single responsibility
7. Keep it simple and clean

**This codebase was built in 1 day with strict principles - maintain that standard!**
