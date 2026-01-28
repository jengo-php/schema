# Jengo Schema

Declarative schema-driven querying for CodeIgniter 4

Jengo Schema is an experimental package that brings structured, schema-based querying, relationship derivation, and entity hydration to CodeIgniter 4 applications.

## Development Status

This package is currently in active development (dev version).
APIs may change

Behavior is still being validated
Not recommended for production yet

That said, it is already usable for:
Local development
Prototyping
Internal tools
Early adopters who want to help test
We are targeting a beta release shortly, followed by a stable v1.0 once the fluent query builder is finalized.

## Key Features

- Schema-first design using PHP attributes
- Declarative relationships (BelongsTo, HasMany)
- Automatic join resolution
- Nested derivation (derive('members.user'))
- Request-aware query options (OPEN mode)
- Inline programmatic queries (INLINE mode)
- Deterministic query planning
- Entity hydration
- Debug tools (Explain, QueryLogger)

## Core Concepts

### Schema
A schema describes:
The CI4 model
The entity
Fields and relationships
How data should be derived
Schemas are the source of truth for queries.

### Query Modes
INLINE — programmatic, explicit options
OPEN — request-driven, REST-friendly

#### Example Usage
```php
use Jengo\Schema\Query\Query;
use Jengo\Schema\Query\DTO\QueryOptions;

$result = Query::run(
    schema: UserSchema::class,
    options: new QueryOptions(
        derive: ['profile'],
        pagination: new PaginationOptions(limit: 10),
    )
);
```

`Fluent API (coming soon)`
```php
query(UserSchema::class)
    ->inline()
    ->where('id', 1)
    ->derive('profile')
    ->first();
```

### Debugging

#### Explain
Inspect how a query is planned before execution.

```php
Explain::for(UserSchema::class)
    ->derive(['profile'])
    ->dump();

```
#### Query Logger

Track executed queries, joins, and execution time.
```php
config('Schema')->logger = true;
```
### Testing

This package includes:

- Unit tests for schema reflection
- Relationship graph validation
- Query plan tests
- Integration tests with CI4’s testing database

Contributions that improve test coverage are welcome.

## Roadmap

- Schema reflection
- Relationship graph
- Query planning
- Hydration
- Fluent query builder
- Better error messages
- Performance benchmarks
- Beta release
- Stable v1.0

## Contributing

This project is still evolving. Feedback, issues, and ideas are welcome.

## License

MIT