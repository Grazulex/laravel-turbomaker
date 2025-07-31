# Laravel TurboMaker

<img src="new_logo.png" alt="Laravel TDDraft" width="200">

Supercharge your Laravel development workflow with instant module scaffolding.

[![Latest Version](https://img.shields.io/packagist/v/grazulex/laravel-turbomaker.svg?style=flat-square)](https://packagist.org/packages/grazulex/laravel-turbomaker)
[![Total Downloads](https://img.shields.io/packagist/dt/grazulex/laravel-turbomaker.svg?style=flat-square)](https://packagist.org/packages/grazulex/laravel-turbomaker)
[![License](https://img.shields.io/github/license/grazulex/laravel-turbomaker.svg?style=flat-square)](https://github.com/Grazulex/laravel-turbomaker/blob/main/LICENSE.md)
[![PHP Version](https://img.shields.io/packagist/php-v/grazulex/laravel-turbomaker.svg?style=flat-square)](https://php.net/)
[![Laravel Version](https://img.shields.io/badge/laravel-12.x-ff2d20?style=flat-square&logo=laravel)](https://laravel.com/)
[![Tests](https://img.shields.io/github/actions/workflow/status/grazulex/laravel-turbomaker/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/Grazulex/laravel-turbomaker/actions)
[![Code Style](https://img.shields.io/badge/code%20style-pint-000000?style=flat-square&logo=laravel)](https://github.com/laravel/pint)


---

Laravel **TurboMaker** is a productivity-focused package designed to **save hours of repetitive setup work**.  
With a single command, you can scaffold complete modules (models, migrations, controllers, routes, tests, views, policies, factories...) following **Laravel best practices**.

---

## ✨ Features

- **⚡ One-command scaffolding** – Generate a full CRUD or API module instantly.
- **📦 Complete structure** – Models, controllers, migrations, requests, resources, views & tests.
- **🔒 Security ready** – Generates Policies and authentication hooks out of the box.
- **🧪 Built-in testing** – Pest tests automatically generated for each action.
- **🔌 Extensible** – Add your own templates or modify the defaults.
- **🌐 API & Web ready** – Separate API Resources & Controllers when needed.

---

## 📦 Installation

```bash
composer require --dev grazulex/laravel-turbomaker
```

**Requirements**:
- PHP 8.3+
- Laravel 11.x | 12.x

---

## 🚀 Quick Start

### Generate Complete Module
```bash
php artisan turbo:make Post
```

**What's Generated:**
- **Model**: `app/Models/Post.php` with relationships
- **Controllers**: Web & API controllers with CRUD operations
- **Migrations**: Database table with proper columns and indexes
- **Form Requests**: Validation for Store/Update operations
- **API Resources**: JSON transformations for API responses
- **Views**: Complete CRUD views (index, create, edit, show)
- **Routes**: Both web and API routes automatically added
- **Tests**: Feature and unit tests using Pest framework
- **Factory**: Model factory for testing and seeding

### API-First Development
```bash
php artisan turbo:api Product --tests --policies
```
Generates API-only components (no views) with authentication and authorization.

### Add Relationships
```bash
php artisan turbo:make Comment --belongs-to=Post --belongs-to=User
```
Automatically handles foreign keys, model relationships, and form integration.

---

## 🔍 Available Commands

### Core Commands

| Command | Purpose | Example |
|---------|---------|---------|
| `turbo:make {name}` | Generate complete module | `turbo:make Post --tests --factory` |
| `turbo:api {name}` | API-only module | `turbo:api Product --policies --tests` |
| `turbo:view {name}` | Views only | `turbo:view Product` |
| `turbo:test {name}` | Tests only | `turbo:test User --feature --unit` |

### Generation Options

| Option | Description | Components Generated |
|--------|-------------|---------------------|
| `--tests` | Generate Pest tests | Feature & Unit tests |
| `--factory` | Generate model factory | Database factories |
| `--seeder` | Generate seeder | Database seeders |
| `--policies` | Generate policies | Authorization policies |
| `--actions` | Generate action classes | CRUD action classes |
| `--services` | Generate service classes | Business logic services |
| `--rules` | Generate validation rules | Custom validation rules |
| `--observers` | Generate model observers | Model event handling |
| `--api` | API-only generation | No views, API resources only |
| `--views` | Generate views | Complete CRUD views |
| `--force` | Overwrite existing files | Force regeneration |

### Relationship Options

| Option | Example | Result |
|--------|---------|---------|
| `--belongs-to=User` | Post belongs to User | Foreign key & relationship |
| `--has-many=Comment` | Post has many Comments | Relationship method |
| `--has-one=Profile` | User has one Profile | One-to-one relationship |
| `--relationships="user:belongsTo,posts:hasMany"` | Multiple relationships | Complex relationship definitions |

---

## 🛠 Configuration

TurboMaker can be configured to match your project's needs:

```bash
php artisan vendor:publish --tag=turbomaker-config
```

**Key Configuration Options:**

### Default Generation Settings
- **Tests** - Automatically generate Pest tests (default: true)
- **Factory** - Generate model factories (default: true) 
- **Seeder** - Generate database seeders (default: false)
- **Policies** - Generate authorization policies (default: false)
- **API Resources** - Generate API resource classes (default: true)
- **Actions** - Generate action classes (default: false)
- **Services** - Generate service classes (default: false)
- **Rules** - Generate validation rules (default: false)
- **Observers** - Generate model observers (default: false)

### File Paths Customization
- **Models** - `app/Models` (customizable)
- **Controllers** - `app/Http/Controllers` & `app/Http/Controllers/Api`
- **Requests** - `app/Http/Requests`
- **Resources** - `app/Http/Resources`
- **Policies** - `app/Policies`
- **Actions** - `app/Actions`
- **Services** - `app/Services`
- **Views** - `resources/views`
- **Tests** - `tests/Feature` & `tests/Unit`

### Performance & Status Tracking
- **Caching** - Enable result caching (default: true)
- **Status tracking** - Log generation activities
- **Memory limits** - Configure memory usage for large projects

### 🎨 Custom Templates

Customize the generated code by publishing and modifying the stub templates:

```bash
php artisan vendor:publish --tag=turbomaker-stubs
```

This copies all 24 stub files to `resources/stubs/turbomaker/` where you can modify them to match your coding standards.

**Available Templates:**
- **Models & Database**: `model.stub`, `migration.stub`, `factory.stub`, `seeder.stub`
- **Controllers**: `controller.stub`, `controller.api.stub`
- **Requests**: `request.store.stub`, `request.update.stub`
- **Resources**: `resource.stub` (API resources)
- **Authorization**: `policy.stub`, `observer.stub`
- **Business Logic**: `action.*.stub` (create, update, delete, get), `service.stub`
- **Validation**: `rule.exists.stub`, `rule.unique.stub`
- **Views**: `view.index.stub`, `view.create.stub`, `view.edit.stub`, `view.show.stub`
- **Tests**: `test.feature.stub`, `test.unit.stub`

See the [Configuration Guide](docs/configuration.md) for complete details and team setup examples.

---

## 📚 Documentation

- **[Getting Started](docs/getting-started.md)** - Setup and your first module
- **[Command Reference](docs/commands.md)** - Complete command documentation  
- **[Working with Relationships](docs/relationships.md)** - Model relationships guide
- **[Custom Templates](docs/custom-templates.md)** - Customize generated code
- **[Configuration](docs/configuration.md)** - Configure TurboMaker settings
- **[Advanced Usage](docs/advanced-usage.md)** - Complex patterns and enterprise features

---

## 🔧 Examples

### Complete CRUD Module
```bash
php artisan turbo:make Product --policies --tests --factory --seeder
```

### API-Only Development
```bash
php artisan turbo:api Product --tests --factory --policies
```

### Relationships Made Easy
```bash
php artisan turbo:make Post --belongs-to=User --has-many=Comment --tests
```

### Views Only (for existing models)
```bash
php artisan turbo:view Product
```

### Comprehensive Module with All Features
```bash
php artisan turbo:make Order \
    --belongs-to=User \
    --has-many=OrderItem \
    --has-one=Payment \
    --policies \
    --tests \
    --factory \
    --seeder \
    --actions \
    --services \
    --rules \
    --observers \
    --force
```

This generates a complete Order module with:
- Model with User and OrderItem relationships
- Payment one-to-one relationship  
- Web and API controllers
- Authorization policies
- Comprehensive test suite (Feature + Unit)
- Database factory and seeder
- CRUD action classes
- Business service layer
- Custom validation rules
- Model observer for events
- Complete CRUD views
- API resources and requests

## 📁 Real-World Examples

Explore complete working examples in the [examples directory](examples/):

- **[Blog System](examples/blog-system/)** - User, Post, Comment relationships with authentication
- **[API-Only Application](examples/api-only/)** - REST API with Sanctum authentication
- **[E-commerce Platform](examples/ecommerce/)** - Complex multi-level relationships
- **[Custom Templates](examples/custom-templates/)** - Template customization examples

---

## 🆕 Version Compatibility

| TurboMaker | PHP | Laravel |
|------------|-----|---------|
| 1.x        | 8.3+ | 11.x \| 12.x |

---

## 🤝 Contributing

We welcome contributions! See our [Contributing Guide](CONTRIBUTING.md).

---

<div align="center">
  <p>Made with ❤️ for the Laravel community</p>
  <p>
    <a href="https://github.com/grazulex/laravel-turbomaker/issues">Report Issues</a> •
    <a href="https://github.com/grazulex/laravel-turbomaker/discussions">Discussions</a>
  </p>
</div>