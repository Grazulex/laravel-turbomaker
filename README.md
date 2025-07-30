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

### Scaffold a complete module
```bash
php artisan turbo:make Blog
```
This generates:
- `App\Models\Blog`
- Migrations, Factory, Seeder
- Controller + Resource Controller
- FormRequest classes (Store/Update)
- Routes (web & api)
- Blade views (index, create, edit, show)
- Pest tests (Feature & Unit)
- Policy for access control

### Scaffold only API + tests
```bash
php artisan turbo:make Blog --api --tests
```

### Add relationships automatically
```bash
php artisan turbo:make Post --belongs-to=User --has-many=Comment
```

---

## 🔍 Available Commands

- `turbo:make {name}` – Generate a full Laravel module
- `turbo:view {name}` – Generate only the views for a module
- `turbo:api {name}` – Scaffold only API Resources & Controllers
- `turbo:test {name}` – Generate Pest tests for an existing module

---

## 🛠 Configuration

Publish configuration:
```bash
php artisan vendor:publish --tag=turbomaker-config
```
Customize:
- Default folders
- Test generation options
- API vs Web routes
- Templates used for scaffolding

---

## 📚 Documentation

- **[Getting Started](docs/getting-started.md)**  
- **[Command Reference](docs/commands.md)**  
- **[Custom Templates](docs/custom-templates.md)**  

---

## 🔧 Examples

### CRUD with policies & views
```bash
php artisan turbo:make Product --policies --views
```

### Module with API only + factories & seeders
```bash
php artisan turbo:make Order --api --factory --seeder
```

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