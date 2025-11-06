# AI Coding Guidelines for Gordüló Simering

## Project Overview

This is a Laravel 12 + Filament 4 + Livewire 3 application served by Laravel Herd. It's built from the Filament Starter Kit with Hungarian localization. The app structure separates concerns: Filament admin panel at `/admin`, public-facing Livewire components for products/categories, and a dual-environment architecture (admin vs frontend).

## Architecture & Key Patterns

### Dual Interface Structure

-   **Admin Panel**: Filament v4 at `/admin` path - uses `app/Providers/Filament/AdminPanelServiceProvider.php` with auto-discovery for Resources/Pages/Widgets in `app/Filament/*` (currently empty, ready for CRUD resources)
-   **Public Frontend**: Livewire v3 components in `app/Livewire/Products/` with route model binding (`{product:slug}`, `{category:slug}`) and `throttle:global` middleware (50 requests/min)
-   **Models**: Use `final` classes with strict types. Product model has slug-based routing. User implements `FilamentUser` with `canAccessPanel()` returning `true` (production should restrict this)

### Code Quality Enforcement

-   **Strict Types**: Every file starts with `declare(strict_types=1);`
-   **Final Classes**: All classes are `final` (enforced by Pint rule `"final_class": true`)
-   **Explicit Returns**: All methods require explicit return type declarations
-   **Constructor Promotion**: Use PHP 8.4 constructor property promotion: `public function __construct(public GitHub $github) {}`
-   **Enum Keys**: TitleCase (e.g., `FavoritePerson`, `BestLake`, `Monthly`)

## Development Workflows

### Running the Application

```bash
# Development with all services (uses Laravel Herd for serving)
composer run dev
# Runs: php artisan serve, queue:listen, pail logs, and npm run dev concurrently

# The app is automatically available at: https://gordulosimering.test (via Herd)
# Do NOT run commands to start the server - Herd handles this
```

### Testing

```bash
composer test                    # Run full test suite
./vendor/bin/pest                # Direct Pest execution
./vendor/bin/pest --filter=name  # Run specific tests
```

**Test Patterns**:

-   All tests use `RefreshDatabase`, freeze time, and prevent stray HTTP requests (see `tests/Pest.php`)
-   Livewire tests: `Livewire::test(ComponentClass::class)->assertStatus(200)`
-   Filament tests: Use `livewire(ListUsers::class)->assertCanSeeTableRecords($users)` pattern
-   Architecture tests in `tests/Unit/ArchTest.php` enforce: Models extend Eloquent, Controllers are abstract, plus php/laravel/security presets

### Code Quality Tools

```bash
composer pint-fix    # Auto-fix with Laravel Pint
composer pint        # Check code style (runs with --test)
composer rector      # Automated refactoring with Rector
```

**Pint Configuration** (`pint.json`): Laravel preset + strict rules including `declare_strict_types`, `final_class`, `global_namespace_import`, `ordered_class_elements` (traits→constants→properties→constructor→methods)

**Rector Configuration** (`rector.php`): Laravel-specific rulesets (array helpers, container FQN, eloquent magic→query builder, facade aliases→full names) + PHP 8.3 + dead code removal + strict booleans

## Filament-Specific Guidance

### Creating Resources

```bash
php artisan make:filament-resource Product --generate  # With form/table from model
# Resources auto-discovered in app/Filament/Resources/
```

### Key Conventions

-   Use `->relationship('author')` on Select components instead of manual `->options()` when dealing with Eloquent relationships
-   Filament testing requires authentication: `Livewire::test()` calls in tests
-   Panel configuration in `app/Providers/Filament/AdminPanelServiceProvider.php` - primary color is Amber
-   Hungarian localization available in `lang/hu/Filament/` and `lang/vendor/filament*/hu/`

## Critical Dependencies & Integrations

### Laravel Boost MCP Server (Development Tool)

Provides specialized tools for this project:

-   `search-docs`: Version-specific documentation search (Laravel, Filament, Livewire, Pest) - use before making changes
-   `tinker`: Execute PHP code for debugging/querying models
-   `database-query`: Read-only database queries
-   `list-artisan-commands`: Verify command parameters before execution
-   `get-absolute-url`: Generate correct URLs with Herd scheme/domain
-   `browser-logs`: Read browser errors and exceptions

**Usage**: Always use `search-docs` with simple, broad queries (e.g., `['rate limiting', 'routing']`) WITHOUT package names in queries - package info is auto-sent.

### Notable Packages

-   `nunomaduro/essentials`: Enables `Unguard::class => true` in `config/essentials.php` (all models unguarded)
-   Tailwind CSS v4 with Vite plugin (`@tailwindcss/vite`)
-   Pest plugins: faker, laravel, livewire

## Database & Migrations

-   Uses Laravel 12 anonymous migration classes
-   Default seeder creates admin user (`admin@admin.com`) and 50 products
-   No soft deletes or timestamps customization observed

## Routing Patterns

-   Route groups use `prefix()` and `as()` for clean namespacing
-   All routes in `routes/web.php` use `throttle:global` middleware
-   Livewire components serve as route controllers (no traditional controllers for frontend)

## What NOT to Do

-   ❌ Don't create documentation files unless explicitly requested
-   ❌ Don't create verification scripts when tests exist
-   ❌ Don't change dependencies without approval
-   ❌ Don't create new base folders without approval
-   ❌ Don't use comments within code (use PHPDoc blocks for complex logic only)
-   ❌ Don't skip curly braces for single-line control structures
-   ❌ Don't allow empty `__construct()` methods with zero parameters
-   ❌ Don't hardcode URLs - use `get-absolute-url` Boost tool or route helpers

## Quick Reference: File Locations

-   Livewire Components: `app/Livewire/Products/` (Index, Show, Categories/\*)
-   Models: `app/Models/` (Product, User)
-   Routes: `routes/web.php` (no API routes)
-   Tests: `tests/Feature/Livewire/` mirrors component structure
-   Factories: `database/factories/` (ProductFactory, UserFactory)
-   Config: `config/essentials.php` (Unguard), `config/filament.php`, `config/livewire.php`
-   Views: `resources/views/livewire/products/` (Blade templates for Livewire)

## When Stuck

1. Search docs first: Use `search-docs` tool with relevant keywords
2. Check sibling files for patterns (especially in `app/Livewire/` and `tests/Feature/`)
3. Verify with Boost tools: `list-artisan-commands`, `tinker`, `database-query`
4. Run tests to validate changes: `composer test`
5. Frontend not updating? User may need to run `composer run dev`
