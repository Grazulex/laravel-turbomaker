# Command Reference

Complete reference for all Laravel TurboMaker commands.

## Core Commands

### `turbo:make {name}`

Generate a complete module with all components.

```bash
php artisan turbo:make Post
```

**Generated Components:**
- Model with relationships and casts
- Migration with proper columns and indexes  
- Web Controller with CRUD operations
- API Controller with JSON responses
- Form Requests for validation
- API Resource for JSON transformation
- Complete CRUD views
- Web and API routes
- Feature and unit tests
- Model factory

**Options:**
- `--tests` - Generate Pest tests
- `--factory` - Generate model factory  
- `--seeder` - Generate database seeder
- `--policies` - Generate authorization policies
- `--actions` - Generate action classes
- `--services` - Generate service classes
- `--rules` - Generate validation rules
- `--observers` - Generate model observers
- `--force` - Overwrite existing files

**Field Definition:**
```bash
php artisan turbo:make Product --fields="name:string,price:decimal,active:boolean"
```

**Schema File:**
```bash
php artisan turbo:make Product --schema=products
```

**Relationships:**
```bash
php artisan turbo:make Comment --belongs-to=Post,User
php artisan turbo:make Category --has-many=Product  
php artisan turbo:make Tag --belongs-to-many=Post
```

### `turbo:api {name}`

Generate API-only components (no views).

```bash
php artisan turbo:api Product --tests --policies
```

**Generated Components:**
- Model with relationships and casts
- Migration with proper columns
- API Controller only
- Form Requests for validation
- API Resource for JSON transformation  
- API routes only
- API tests
- Model factory

### `turbo:view {name}`

Generate only view components.

```bash
php artisan turbo:view Product
```

**Generated Components:**
- Complete CRUD views (index, create, edit, show)
- Web routes

### `turbo:test {name}`

Generate only test components.

```bash
php artisan turbo:test User --feature --unit
```

**Options:**
- `--feature` - Generate feature tests
- `--unit` - Generate unit tests

### `turbo:schema {action}`

Manage schema files.

```bash
# Create new schema
php artisan turbo:schema create products

# Validate schema  
php artisan turbo:schema validate products

# Show schema details
php artisan turbo:schema show products

# List all schemas
php artisan turbo:schema list

# Clear schema cache
php artisan turbo:schema clear-cache
```

## Command Options Reference

### Generation Options

| Option | Description | Example |
|--------|-------------|---------|
| `--tests` | Generate Pest tests | `--tests` |
| `--factory` | Generate model factory | `--factory` |
| `--seeder` | Generate database seeder | `--seeder` |
| `--policies` | Generate authorization policies | `--policies` |
| `--actions` | Generate action classes | `--actions` |
| `--services` | Generate service classes | `--services` |
| `--rules` | Generate validation rules | `--rules` |
| `--observers` | Generate model observers | `--observers` |

### Field Options

| Option | Description | Example |
|--------|-------------|---------|
| `--fields` | Define fields inline | `--fields="name:string,price:decimal"` |
| `--schema` | Use schema file | `--schema=products` |

### Relationship Options

| Option | Description | Example |
|--------|-------------|---------|
| `--belongs-to` | Add belongs-to relationships | `--belongs-to=User,Category` |
| `--has-many` | Add has-many relationships | `--has-many=Comment,Review` |
| `--has-one` | Add has-one relationships | `--has-one=Profile` |
| `--belongs-to-many` | Add many-to-many relationships | `--belongs-to-many=Tag,Role` |

### Control Options

| Option | Description | Example |
|--------|-------------|---------|
| `--force` | Overwrite existing files | `--force` |
| `--no-controller` | Skip controller generation | `--no-controller` |
| `--no-views` | Skip view generation | `--no-views` |
| `--no-tests` | Skip test generation | `--no-tests` |
| `--no-migration` | Skip migration generation | `--no-migration` |

## Field Definition Syntax

### Inline Field Definition

```bash
php artisan turbo:make Product --fields="field_name:type:modifiers"
```

**Examples:**
```bash
# Basic fields
--fields="name:string,price:decimal,active:boolean"

# With modifiers
--fields="name:string:nullable,price:decimal:required,email:string:unique"

# Complex example
--fields="title:string:255,content:longText:nullable,published_at:timestamp:nullable,views:unsignedBigInteger:default:0"
```

### Field Types (28+ Available)

**String Types:**
- `string` - VARCHAR(255)
- `text` - TEXT  
- `longText` - LONGTEXT
- `mediumText` - MEDIUMTEXT

**Integer Types:**
- `integer` - INT
- `bigInteger` - BIGINT
- `unsignedBigInteger` - UNSIGNED BIGINT
- `tinyInteger` - TINYINT
- `smallInteger` - SMALLINT  
- `mediumInteger` - MEDIUMINT

**Numeric Types:**
- `decimal` - DECIMAL(8,2)
- `float` - FLOAT
- `double` - DOUBLE
- `boolean` - BOOLEAN

**Date & Time:**
- `date` - DATE
- `datetime` - DATETIME  
- `timestamp` - TIMESTAMP
- `time` - TIME

**Special Types:**
- `json` - JSON
- `uuid` - CHAR(36)
- `email` - VARCHAR(255) with email validation
- `url` - VARCHAR(255) with URL validation
- `foreignId` - Foreign key reference
- `morphs` - Polymorphic relationship fields
- `binary` - LONGBLOB

## Examples

### Complete Blog System

```bash
# Main post model
php artisan turbo:make Post --fields="title:string,slug:string:unique,content:longText,published:boolean,published_at:timestamp:nullable" --tests --factory

# Comments with relationships
php artisan turbo:make Comment --fields="content:text" --belongs-to=Post,User --tests

# Categories with relationships  
php artisan turbo:make Category --fields="name:string:unique,description:text:nullable" --has-many=Post --tests

# Tags with many-to-many
php artisan turbo:make Tag --fields="name:string:unique" --belongs-to-many=Post --tests
```

### E-commerce Platform

```bash
# Products with schema file
php artisan turbo:make Product --schema=product --tests --factory --policies

# Orders with relationships
php artisan turbo:make Order --fields="total:decimal,status:string,order_date:timestamp" --belongs-to=User --tests

# Order items (pivot-like)
php artisan turbo:make OrderItem --fields="quantity:integer,price:decimal" --belongs-to=Order,Product --tests
```

### API-Only Project

```bash
# User management
php artisan turbo:api User --fields="name:string,email:email:unique,email_verified_at:timestamp:nullable" --tests --policies

# API resources
php artisan turbo:api Product --schema=product --tests --policies
php artisan turbo:api Order --tests --policies

# Authentication related
php artisan turbo:api ApiToken --fields="name:string,token:string:unique,expires_at:timestamp:nullable" --belongs-to=User --tests
```

### Multi-tenant Application

```bash
# Tenant model
php artisan turbo:make Tenant --fields="name:string,domain:string:unique,database:string" --tests --factory

# Tenant-aware models
php artisan turbo:make User --fields="name:string,email:email" --belongs-to=Tenant --tests
php artisan turbo:make Project --fields="name:string,description:text" --belongs-to=Tenant,User --tests
```

## Advanced Usage

### Custom Stub Templates

```bash
# Publish stubs for customization
php artisan vendor:publish --tag=turbomaker-stubs

# Customize templates in resources/stubs/turbomaker/
```

### Configuration

```bash
# Publish configuration
php artisan vendor:publish --tag=turbomaker-config

# Edit config/turbomaker.php
```

### Schema Validation

```bash
# Validate before generation
php artisan turbo:schema validate products

# Show detailed schema information
php artisan turbo:schema show products --fields --relationships
```

### Batch Operations

```bash
# Generate multiple models from schema files
for schema in user product order; do
  php artisan turbo:make ${schema^} --schema=$schema --tests --factory
done
```

## Performance Tips

1. **Use Schema Files** for complex models with many fields
2. **Batch Generate** related models together  
3. **Selective Generation** - only generate what you need
4. **Schema Validation** before generation to catch errors early

## Troubleshooting

### Common Issues

**Command not found:**
```bash
# Clear config cache
php artisan config:clear
php artisan cache:clear
```

**Field type not recognized:**
```bash
# Check available field types
php artisan turbo:schema validate your-schema
```

**Generation fails:**
```bash
# Use --force to overwrite
php artisan turbo:make Model --force

# Check file permissions
chmod -R 755 app/ database/ resources/ tests/
```
