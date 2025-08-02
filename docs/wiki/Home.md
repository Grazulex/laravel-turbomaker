# Laravel TurboMaker

**Supercharge your Laravel development with extensible, schema-driven code generation.**

[![Latest Version](https://img.shields.io/packagist/v/grazulex/laravel-turbomaker.svg?style=flat-square)](https://packagist.org/packages/grazulex/laravel-turbomaker)
[![Total Downloads](https://img.shields.io/packagist/dt/grazulex/laravel-turbomaker.svg?style=flat-square)](https://packagist.org/packages/grazulex/laravel-turbomaker)
[![Tests](https://img.shields.io/github/actions/workflow/status/grazulex/laravel-turbomaker/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/Grazulex/laravel-turbomaker/actions)

Laravel TurboMaker is a powerful productivity package that generates complete Laravel modules from simple schemas. Save hours of repetitive coding with intelligent scaffolding that follows Laravel best practices.

## ✨ What Makes TurboMaker Special

### 🎯 Schema-Driven Development
Define your data structure once, generate everything automatically:
```yaml
# resources/schemas/product.schema.yml
fields:
  name:
    type: string
    length: 255
    nullable: false
    validation: ["min:3"]
  price:
    type: decimal
    attributes: { precision: 10, scale: 2 }
    validation: ["numeric", "min:0"]
  is_active:
    type: boolean
    default: true
```

### 🔧 28+ Field Types
Complete coverage of Laravel column types plus extensible architecture:
- **String Types**: `string`, `text`, `longText`, `mediumText`
- **Integer Types**: `integer`, `bigInteger`, `unsignedBigInteger`, `tinyInteger`
- **Numeric Types**: `decimal`, `float`, `double`, `boolean`
- **Date Types**: `date`, `datetime`, `timestamp`, `time`
- **Special Types**: `json`, `uuid`, `email`, `url`, `foreignId`, `morphs`, `binary`

### 🧩 Extensible Architecture
Create custom field types for your specific needs:
```php
final class MoneyFieldType extends AbstractFieldType
{
    public function getValidationRules(Field $field): array
    {
        return ['numeric', 'min:0'];
    }
    
    public function getFactoryDefinition(Field $field): string
    {
        return 'fake()->randomFloat(2, 10, 9999)';
    }
}
```

## 🚀 Quick Start

### Installation
```bash
composer require --dev grazulex/laravel-turbomaker
```

### Generate Your First Module
```bash
# Complete CRUD module
php artisan turbo:make Product

# With custom fields
php artisan turbo:make Product --fields="name:string,price:decimal,active:boolean"

# API-only module
php artisan turbo:api BlogPost

# Using schema file
php artisan turbo:make Product --schema=products
```

### What Gets Generated
A single command creates:
- ✅ **Model** with relationships, fillable, casts
- ✅ **Migration** with proper columns and indexes
- ✅ **Controllers** (Web + API) with full CRUD operations
- ✅ **Form Requests** with intelligent validation
- ✅ **API Resources** for JSON transformations
- ✅ **Views** (index, create, edit, show) with Bootstrap styling
- ✅ **Routes** (web + API) with proper naming conventions
- ✅ **Tests** (Feature + Unit) using Pest framework
- ✅ **Factory** for testing and seeding
- ✅ **Policies** for authorization (optional)
- ✅ **Seeders** for data population (optional)

## 📚 Documentation Structure

### 🏁 Getting Started
- [[Getting Started]] - Installation and first steps
- [[Installation]] - Detailed installation guide
- [[Commands]] - Complete command reference

### 🎨 Core Features
- [[Schema System]] - Understanding the schema system
- [[Field Types]] - All available field types
- [[Custom Field Types]] - Creating extensible field types
- [[Relationships]] - Model relationships guide

### ⚙️ Advanced Usage
- [[Configuration]] - Configuration options
- [[Custom Templates]] - Customizing generated code
- [[Testing]] - Testing strategies
- [[Performance]] - Optimization tips

### 💡 Real-World Examples
- [[Examples]] - Collection of practical examples
- [[Blog Example]] - Complete blog system
- [[Ecommerce Example]] - E-commerce application
- [[API Example]] - RESTful API development

### 🔧 Reference
- [[Troubleshooting]] - Common issues and solutions
- [[FAQ]] - Frequently asked questions
- [[Changelog]] - Version history
- [[Migration Guide]] - Upgrading between versions

## 🎯 Key Benefits

### For Developers
- **Save Time**: Generate complete modules in seconds
- **Consistency**: Every module follows Laravel best practices
- **Quality**: Built-in validation, testing, and security
- **Flexibility**: Customize everything to your needs

### For Teams
- **Standardization**: Consistent code structure across projects
- **Onboarding**: New developers productive immediately
- **Maintenance**: Clean, well-documented generated code
- **Scalability**: Extensible architecture grows with your needs

### For Projects
- **Rapid Prototyping**: Ideas to working code in minutes
- **API-First**: Modern development workflows supported
- **Testing**: Comprehensive test coverage out of the box
- **Security**: Policies and validation by default

## 🌟 Version 6 Highlights

Laravel TurboMaker V6 introduces a revolutionary **extensible field type architecture**:

- **Community Extensions**: Add custom field types via configuration
- **28+ Field Types**: Complete Laravel column type coverage
- **Smart Validation**: Context-aware validation rules
- **Intelligent Factories**: Name-based factory generation
- **Zero Breaking Changes**: Fully backward compatible

## 🤝 Community

- **GitHub**: [Grazulex/laravel-turbomaker](https://github.com/Grazulex/laravel-turbomaker)
- **Issues**: [Report bugs and feature requests](https://github.com/Grazulex/laravel-turbomaker/issues)
- **Discussions**: [Community discussions](https://github.com/Grazulex/laravel-turbomaker/discussions)
- **Wiki**: [Complete documentation](https://github.com/Grazulex/laravel-turbomaker/wiki)

---

**Ready to supercharge your Laravel development? Start with [[Getting Started]] →**
