# Laravel TurboMaker Documentation

Complete documentation for Laravel TurboMaker V6 - Extensible, schema-driven code generation.

## 📚 Documentation Structure

### Getting Started
- **[Getting Started](Getting-Started.md)** - Installation, first steps, and basic usage
- **[Commands](Commands.md)** - Complete command reference and examples

### Core Features  
- **[Field Types](Field-Types.md)** - All 28+ supported field types with examples
- **[Schema System](Schema-System.md)** - Advanced schema file usage and patterns

### Advanced Topics
- **[Custom Field Types](Custom-Field-Types.md)** - Create and register your own field types
- **[Configuration](Configuration.md)** - Complete configuration guide and customization
- **[Examples](Examples.md)** - Real-world examples (blog, e-commerce, SaaS, API)

## 🚀 Quick Navigation

| Topic | Description | Best For |
|-------|-------------|----------|
| [Getting Started](Getting-Started.md) | Installation and first module | New users |
| [Commands](Commands.md) | Complete command reference | Daily usage |
| [Field Types](Field-Types.md) | All available field types | Schema design |
| [Schema System](Schema-System.md) | Advanced schema patterns | Complex models |
| [Custom Field Types](Custom-Field-Types.md) | Extensibility guide | Advanced users |
| [Configuration](Configuration.md) | Customization options | Team setup |
| [Examples](Examples.md) | Real-world patterns | Learning by example |

## 🆕 What's New in V6

### Extensible Field Type Architecture
- **28+ Built-in Field Types** - Complete Laravel column type coverage
- **Custom Field Types** - Community-extensible via configuration
- **Smart Defaults** - Intelligent validation and factory generation
- **Registry Pattern** - Efficient type lookup and validation

### Enhanced Features
- **Schema-driven Generation** - Define once, generate everything
- **Context-aware Validation** - Smart validation rules based on field names
- **Intelligent Factories** - Realistic test data based on field semantics
- **Zero Breaking Changes** - Full backward compatibility

## 🔧 Architecture Overview

```
Laravel TurboMaker V6
├── Field Type System
│   ├── FieldTypeInterface (Contract)
│   ├── FieldTypeRegistry (Central registry)
│   ├── AbstractFieldType (Base implementation)
│   └── 28+ Specialized Types
├── Schema System  
│   ├── YAML-based definitions
│   ├── Validation and caching
│   └── Relationship management
└── Code Generation
    ├── Extensible stub templates
    ├── Smart placeholders
    └── Batch generation support
```

## 💡 Quick Examples

### Basic Usage
```bash
# Generate complete module
php artisan turbo:make Product

# With custom fields
php artisan turbo:make Product --fields="name:string,price:decimal,active:boolean"

# API-only
php artisan turbo:api BlogPost --tests --policies
```

### Schema-Driven
```yaml
# resources/schemas/product.schema.yml
fields:
  name:
    type: string
    validation: ["min:3"]
  price: 
    type: decimal
    validation: ["min:0"]
  stock:
    type: unsignedBigInteger
    default: 0
```

```bash
php artisan turbo:make Product --schema=product
```

### Custom Field Types
```php
```php
// config/turbomaker.php
'custom_field_types' => [
    'money' => App\FieldTypes\MoneyFieldType::class,
    'slug' => App\FieldTypes\SlugFieldType::class,
],
```

## 🎯 Use Cases

### Perfect For
- **CRUD Applications** - Complete modules in seconds
- **API Development** - JSON-first with proper resources
- **Rapid Prototyping** - Get ideas to code fast
- **Team Standards** - Consistent code patterns
- **Learning Laravel** - See best practices in action

### Enterprise Features
- **Multi-tenant Applications** - Tenant-aware models
- **E-commerce Platforms** - Complex product catalogs
- **Content Management** - Blog and CMS systems
- **SaaS Applications** - Subscription and billing models

## 📖 Learning Path

### Beginners
1. [Getting Started](Getting-Started.md) - Installation and first module
2. [Commands](Commands.md) - Learn basic commands
3. [Field Types](Field-Types.md) - Understand available types
4. [Examples](Examples.md) - See real-world patterns

### Intermediate
1. [Schema System](Schema-System.md) - Advanced schema files
2. [Configuration](Configuration.md) - Customize for your team
3. [Examples](Examples.md) - Complex application patterns

### Advanced
1. [Custom Field Types](Custom-Field-Types.md) - Extend the system
2. [Configuration](Configuration.md) - Performance optimization
3. Contribute to the project

## 🔗 External Resources

- **[GitHub Repository](https://github.com/Grazulex/laravel-turbomaker)** - Source code and issues
- **[Packagist](https://packagist.org/packages/grazulex/laravel-turbomaker)** - Package downloads
- **[Laravel Documentation](https://laravel.com/docs)** - Laravel framework docs
- **[Pest Testing](https://pestphp.com/)** - Testing framework used

## 🤝 Contributing

Contributions are welcome! See our [Contributing Guide](../../CONTRIBUTING.md) for details.

### Areas for Contribution
- **Custom Field Types** - Share your field type implementations
- **Documentation** - Improve guides and examples  
- **Bug Reports** - Help us improve reliability
- **Feature Requests** - Suggest new capabilities

## 📄 License

Laravel TurboMaker is open-sourced software licensed under the [MIT license](../../LICENSE.md).

---

*Laravel TurboMaker V6 - Supercharge your Laravel development with extensible, schema-driven code generation.*
```
- **Custom-Templates.md** - Personnalisation des templates
- **Testing.md** - Tests et validation
- **Performance.md** - Optimisation et performance

### Exemples
- **Examples.md** - Exemples concrets d'utilisation
- **Blog-Example.md** - Exemple complet : Blog
- **Ecommerce-Example.md** - Exemple complet : E-commerce
- **API-Example.md** - Exemple complet : API

### Référence
- **Troubleshooting.md** - Dépannage et solutions
- **FAQ.md** - Questions fréquentes
- **Changelog.md** - Historique des versions
- **Migration-Guide.md** - Guide de migration

## 🚀 Comment Utiliser

1. Transférez chaque fichier .md vers le GitHub Wiki
2. Ajustez les liens internes selon la structure du wiki
3. Ajoutez des images dans le dossier `assets/` si nécessaire
4. Mettez à jour la navigation du wiki

## 📝 Notes

- Tous les exemples de code sont prêts à copier-coller
- Les liens vers les autres pages sont formatés pour GitHub Wiki
- La documentation est organisée par niveau de complexité
- Chaque page contient des exemples pratiques
