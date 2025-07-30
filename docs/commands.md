# Command Reference

Complete reference for all Laravel TurboMaker commands and options.

## Core Commands

### `turbo:make {name}`

Generate a complete Laravel module with all components.

**Signature:**
```bash
php artisan turbo:make {name} [options]
```

**Arguments:**
- `name` - The name of the module to generate (required)

**Options:**
- `--api` - Generate API resources only (no views)
- `--views` - Generate views
- `--policies` - Generate policies for authorization
- `--factory` - Generate model factory
- `--seeder` - Generate database seeder
- `--tests` - Generate Pest tests
- `--actions` - Generate action classes
- `--services` - Generate service classes
- `--rules` - Generate validation rules
- `--observers` - Generate model observers
- `--belongs-to=*` - Add belongs-to relationships
- `--has-many=*` - Add has-many relationships
- `--has-one=*` - Add has-one relationships
- `--force` - Overwrite existing files

**Examples:**
```bash
# Basic module
php artisan turbo:make Post

# API-only module
php artisan turbo:make Product --api

# Full module with all components
php artisan turbo:make Order --policies --factory --seeder --tests --actions --services

# Module with relationships
php artisan turbo:make Comment --belongs-to=Post --belongs-to=User

# Overwrite existing files
php artisan turbo:make Post --force
```

### `turbo:api {name}`

Scaffold only API Resources & Controllers for a Laravel module.

**Signature:**
```bash
php artisan turbo:api {name} [options]
```

**Arguments:**
- `name` - The name of the module to generate API for (required)

**Options:**
- `--relationships=` - Define relationships (format: "user:belongsTo,posts:hasMany")
- `--factory` - Generate factory
- `--seeder` - Generate seeder
- `--tests` - Generate tests
- `--actions` - Generate action classes
- `--services` - Generate service classes
- `--rules` - Generate validation rules
- `--observers` - Generate model observers
- `--policies` - Generate policies
- `--belongs-to=*` - Add belongs-to relationships
- `--has-many=*` - Add has-many relationships
- `--has-one=*` - Add has-one relationships
- `--force` - Overwrite existing files

**Generated Files:**
- Model
- Migration
- API Controller
- API Resource
- Form Requests (Store/Update)
- Tests (if requested)

**Examples:**
```bash
# Basic API module
php artisan turbo:api Product

# API module with relationships
php artisan turbo:api Order --belongs-to=User --has-many=OrderItem

# API module with all features
php artisan turbo:api Invoice --factory --seeder --tests --actions --services
```

### `turbo:view {name}`

Generate only the views for a Laravel module.

**Signature:**
```bash
php artisan turbo:view {name} [options]
```

**Arguments:**
- `name` - The name of the module to generate views for (required)

**Options:**
- `--relationships=` - Define relationships (format: "user:belongsTo,posts:hasMany")
- `--force` - Overwrite existing files

**Generated Files:**
- `resources/views/{name}/index.blade.php`
- `resources/views/{name}/create.blade.php`
- `resources/views/{name}/edit.blade.php`
- `resources/views/{name}/show.blade.php`

**Examples:**
```bash
# Generate views for existing model
php artisan turbo:view Product

# Generate views with relationship context
php artisan turbo:view Post --relationships="user:belongsTo,comments:hasMany"
```

### `turbo:test {name}`

Generate Pest tests for an existing Laravel module.

**Signature:**
```bash
php artisan turbo:test {name} [options]
```

**Arguments:**
- `name` - The name of the module to generate tests for (required)

**Options:**
- `--relationships=` - Define relationships (format: "user:belongsTo,posts:hasMany")
- `--unit` - Generate only unit tests
- `--feature` - Generate only feature tests
- `--belongs-to=*` - Add belongs-to relationships for test context
- `--has-many=*` - Add has-many relationships for test context
- `--has-one=*` - Add has-one relationships for test context
- `--force` - Overwrite existing files

**Generated Files:**
- `tests/Feature/{Name}Test.php` (unless --unit only)
- `tests/Unit/{Name}Test.php` (unless --feature only)

**Examples:**
```bash
# Generate all tests
php artisan turbo:test Product

# Generate only feature tests
php artisan turbo:test Order --feature

# Generate only unit tests
php artisan turbo:test Invoice --unit

# Generate tests with relationship context
php artisan turbo:test Post --belongs-to=User --has-many=Comment
```

## Options Reference

### Component Options

| Option | Description | Affects |
|--------|-------------|---------|
| `--api` | Generate API-only (no views) | Controllers, routes |
| `--views` | Include view generation | Blade templates |
| `--policies` | Generate authorization policies | Policy classes |
| `--factory` | Generate model factories | Database factories |
| `--seeder` | Generate database seeders | Database seeders |
| `--tests` | Generate Pest tests | Feature & unit tests |
| `--actions` | Generate action classes | Action pattern classes |
| `--services` | Generate service classes | Service layer classes |
| `--rules` | Generate validation rules | Custom validation rules |
| `--observers` | Generate model observers | Model event observers |

### Relationship Options

| Option | Description | Example |
|--------|-------------|---------|
| `--belongs-to=Model` | Add belongsTo relationship | `--belongs-to=User` |
| `--has-many=Model` | Add hasMany relationship | `--has-many=Comment` |
| `--has-one=Model` | Add hasOne relationship | `--has-one=Profile` |
| `--relationships=` | Define multiple relationships | `--relationships="user:belongsTo,posts:hasMany"` |

### Utility Options

| Option | Description |
|--------|-------------|
| `--force` | Overwrite existing files without confirmation |

## File Generation Matrix

This table shows which files are generated by each command and option combination:

| Component | `turbo:make` | `turbo:api` | `turbo:view` | `turbo:test` |
|-----------|--------------|-------------|--------------|--------------|
| Model | ✅ | ✅ | ❌ | ❌ |
| Migration | ✅ | ✅ | ❌ | ❌ |
| Web Controller | ✅ | ❌ | ❌ | ❌ |
| API Controller | ✅ | ✅ | ❌ | ❌ |
| Form Requests | ✅ | ✅ | ❌ | ❌ |
| API Resource | ✅ | ✅ | ❌ | ❌ |
| Views | ✅ (unless --api) | ❌ | ✅ | ❌ |
| Routes | ✅ | ✅ | ❌ | ❌ |
| Tests | ✅ (with --tests) | ✅ (with --tests) | ❌ | ✅ |
| Factory | ✅ (with --factory) | ✅ (with --factory) | ❌ | ❌ |
| Seeder | ✅ (with --seeder) | ✅ (with --seeder) | ❌ | ❌ |
| Policy | ✅ (with --policies) | ✅ (with --policies) | ❌ | ❌ |
| Actions | ✅ (with --actions) | ✅ (with --actions) | ❌ | ❌ |
| Services | ✅ (with --services) | ✅ (with --services) | ❌ | ❌ |
| Rules | ✅ (with --rules) | ✅ (with --rules) | ❌ | ❌ |
| Observers | ✅ (with --observers) | ✅ (with --observers) | ❌ | ❌ |

## Common Patterns

### Full-Stack Development
```bash
# Complete CRUD with all features
php artisan turbo:make Product --policies --factory --seeder --tests --actions --services --rules
```

### API Development
```bash
# API-first with testing
php artisan turbo:api Order --tests --factory --seeder --policies
```

### Rapid Prototyping
```bash
# Quick model with basic features
php artisan turbo:make Post --factory --seeder
```

### Testing Existing Code
```bash
# Add comprehensive tests to existing models
php artisan turbo:test User --belongs-to=Role --has-many=Post
```

### Adding Views to API
```bash
# First create API
php artisan turbo:api Product

# Then add views
php artisan turbo:view Product
```

## Exit Codes

| Code | Meaning |
|------|---------|
| 0 | Success |
| 1 | General error |
| 2 | Invalid arguments |

## Tips

1. **Use `--force` carefully** - It overwrites existing files without backup
2. **Combine options** - Most options work together for comprehensive scaffolding
3. **Start with API** - Use `turbo:api` first, then add views if needed
4. **Test early** - Always include `--tests` for quality code
5. **Relationships matter** - Define them early for better code generation