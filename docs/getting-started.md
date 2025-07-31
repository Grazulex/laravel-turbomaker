# Getting Started with Laravel TurboMaker

Welcome to Laravel TurboMaker! This guide will help you get up and running quickly with scaffolding complete Laravel modules.

## Installation

Install the package via Composer in your Laravel project:

```bash
composer require --dev grazulex/laravel-turbomaker
```

### Requirements

- PHP 8.3+
- Laravel 11.x | 12.x

## Your First Module

Let's create a simple blog post module to see TurboMaker in action:

```bash
php artisan turbo:make Post
```

This single command generates:

- **Model**: `app/Models/Post.php`
- **Migration**: `database/migrations/xxxx_create_posts_table.php`
- **Controllers**: `app/Http/Controllers/PostController.php` & `app/Http/Controllers/Api/PostController.php`
- **Form Requests**: `app/Http/Requests/StorePostRequest.php` & `app/Http/Requests/UpdatePostRequest.php`
- **API Resource**: `app/Http/Resources/PostResource.php`
- **Views**: Complete CRUD views in `resources/views/posts/`
- **Routes**: Both web and API routes
- **Tests**: Feature and unit tests using Pest
- **Factory**: `database/factories/PostFactory.php`

## Basic Usage Examples

### Generate API-Only Module

For API-first applications:

```bash
php artisan turbo:api Product
```

This creates only the API components (no views):
- Model, migration, factory
- API controller and resource
- Form requests
- Tests

### Generate Views Only

If you already have a model and want to add views:

```bash
php artisan turbo:view Product
```

### Generate Tests Only

Add tests to an existing module:

```bash
php artisan turbo:test Product
```

## Adding Relationships

TurboMaker can automatically set up model relationships:

```bash
# Post belongs to User, has many Comments
php artisan turbo:make Post --belongs-to=User --has-many=Comment

# Product belongs to Category, has one ProductDetail
php artisan turbo:make Product --belongs-to=Category --has-one=ProductDetail
```

## Common Options

### Include Additional Components

```bash
# Generate with all components
php artisan turbo:make Order --policies --factory --seeder --tests --observers

# Generate with actions and services
php artisan turbo:make Invoice --actions --services --rules

# Generate comprehensive module with all features
php artisan turbo:make Product \
    --policies \
    --factory \
    --seeder \
    --tests \
    --actions \
    --services \
    --rules \
    --observers
```

### Available Generation Options

| Option | Description | Example |
|--------|-------------|---------|
| `--tests` | Generate Pest tests | Feature & Unit tests |
| `--factory` | Generate model factory | Database factory |
| `--seeder` | Generate seeder | Database seeder |
| `--policies` | Generate policies | Authorization policies |
| `--actions` | Generate action classes | CRUD action classes |
| `--services` | Generate service classes | Business logic services |
| `--rules` | Generate validation rules | Custom validation rules |
| `--observers` | Generate model observers | Model event handling |
| `--api` | API-only generation | No views, API resources only |
| `--views` | Generate views | Complete CRUD views |
| `--force` | Overwrite existing files | Force regeneration |

### Force Overwrite

Use `--force` to overwrite existing files:

```bash
php artisan turbo:make Post --force
```

## What's Generated?

### File Structure
When you run `turbo:make Post --all-options`, here's what gets created:

```
app/
├── Models/Post.php
├── Http/
│   ├── Controllers/
│   │   ├── PostController.php
│   │   └── Api/PostController.php
│   ├── Requests/
│   │   ├── StorePostRequest.php
│   │   └── UpdatePostRequest.php
│   └── Resources/PostResource.php
├── Policies/PostPolicy.php (if --policies)
├── Actions/ (if --actions)
│   ├── CreatePostAction.php
│   ├── UpdatePostAction.php
│   ├── DeletePostAction.php
│   └── GetPostAction.php
├── Services/PostService.php (if --services)
├── Rules/ (if --rules)
│   ├── PostExistsRule.php
│   └── PostUniqueRule.php
└── Observers/PostObserver.php (if --observers)

database/
├── migrations/xxxx_create_posts_table.php
├── factories/PostFactory.php (if --factory)
└── seeders/PostSeeder.php (if --seeder)

resources/views/posts/ (unless --api)
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── show.blade.php

routes/
├── web.php (routes added unless --api)
└── api.php (API routes added)

tests/ (if --tests)
├── Feature/PostTest.php
└── Unit/PostTest.php
```

### Generated Components

| Component | File | Purpose |
|-----------|------|---------|
| **Model** | `app/Models/Post.php` | Eloquent model with relationships |
| **Controllers** | `app/Http/Controllers/PostController.php` | Web CRUD operations |
|  | `app/Http/Controllers/Api/PostController.php` | API CRUD operations |
| **Requests** | `app/Http/Requests/StorePostRequest.php` | Form validation for create |
|  | `app/Http/Requests/UpdatePostRequest.php` | Form validation for update |
| **Resources** | `app/Http/Resources/PostResource.php` | API response formatting |
| **Migration** | `database/migrations/*_create_posts_table.php` | Database table structure |
| **Factory** | `database/factories/PostFactory.php` | Test data generation |
| **Seeder** | `database/seeders/PostSeeder.php` | Database seeding |
| **Policy** | `app/Policies/PostPolicy.php` | Authorization rules |
| **Actions** | `app/Actions/Post/*.php` | CRUD action classes |
| **Service** | `app/Services/PostService.php` | Business logic layer |
| **Rules** | `app/Rules/Post*.php` | Custom validation rules |
| **Observer** | `app/Observers/PostObserver.php` | Model event handling |
| **Views** | `resources/views/posts/*.blade.php` | CRUD interface |
| **Tests** | `tests/Feature/PostTest.php` | Feature testing |
|  | `tests/Unit/PostTest.php` | Unit testing |

## Customizing Generated Code

You can customize the generated code by publishing and modifying the stub templates:

```bash
php artisan vendor:publish --tag=turbomaker-stubs
```

This copies all template files to `resources/stubs/turbomaker/` where you can modify them to match your project's coding standards and requirements.

## Next Steps

- [Command Reference](commands.md) - Complete list of all available commands and options
- [Working with Relationships](relationships.md) - Advanced relationship handling
- [Custom Templates](custom-templates.md) - Customize the generated code
- [Configuration](configuration.md) - Configure TurboMaker settings

## Quick Reference

| Command | Purpose |
|---------|---------|
| `turbo:make {name}` | Generate complete module |
| `turbo:api {name}` | Generate API-only module |
| `turbo:view {name}` | Generate views only |
| `turbo:test {name}` | Generate tests only |

Happy coding! 🚀