# Schema System

Laravel TurboMaker's schema system is the heart of the package. It allows you to define your data structure once and generate all necessary components automatically.

## Overview

The schema system supports:
- **Field definitions** with 28+ types
- **Relationships** between models
- **Validation rules** and constraints
- **Factory definitions** for testing
- **Custom attributes** for specialized behavior

## Schema File Structure

### Basic Structure

```yaml
# resources/schemas/product.schema.yml
name: "Product"
table_name: "products"

fields:
  # Field definitions here

relationships:
  # Relationship definitions here
```

### Complete Example

```yaml
name: "BlogPost"
table_name: "blog_posts"

fields:
  title:
    type: string
    length: 255
    nullable: false
    validation: ["min:3", "max:255"]
    index: true
    
  slug:
    type: string
    length: 255
    unique: true
    validation: ["regex:/^[a-z0-9-]+$/"]
    
  content:
    type: longText
    nullable: false
    
  excerpt:
    type: text
    nullable: true
    
  status:
    type: string
    length: 20
    default: "draft"
    validation: ["in:draft,published,archived"]
    
  featured_image:
    type: string
    nullable: true
    validation: ["url"]
    
  published_at:
    type: datetime
    nullable: true
    
  view_count:
    type: unsignedBigInteger
    default: 0
    validation: ["min:0"]
    
  metadata:
    type: json
    nullable: true
    
  is_featured:
    type: boolean
    default: false

relationships:
  author:
    type: belongsTo
    model: "User"
    foreign_key: "author_id"
    
  category:
    type: belongsTo
    model: "Category"
    foreign_key: "category_id"
    
  tags:
    type: belongsToMany
    model: "Tag"
    pivot_table: "blog_post_tags"
    
  comments:
    type: hasMany
    model: "Comment"
```

## Field Definitions

### Basic Syntax

```yaml
field_name:
  type: field_type
  # ... additional options
```

### Field Options

#### Core Options
```yaml
field_name:
  type: string          # Required: field type
  nullable: false       # Optional: allow null values
  unique: false         # Optional: unique constraint
  index: false          # Optional: database index
  default: null         # Optional: default value
  comment: "Description" # Optional: column comment
```

#### String-Specific Options
```yaml
name:
  type: string
  length: 255           # Maximum length
```

#### Decimal-Specific Options
```yaml
price:
  type: decimal
  attributes:
    precision: 10       # Total digits
    scale: 2           # Decimal places
```

#### Custom Validation
```yaml
email:
  type: string
  validation:           # Custom validation rules
    - "email"
    - "unique:users,email"
    - "max:255"
```

#### Custom Factory Rules
```yaml
slug:
  type: string
  factory:              # Custom factory generation
    - "slug()"
    - "unique()"
```

#### Custom Attributes
```yaml
coordinates:
  type: json
  attributes:           # Custom type-specific options
    format: "lat_lng"
    precision: 6
```

### Advanced Field Examples

#### E-commerce Price Field
```yaml
price:
  type: decimal
  nullable: false
  attributes:
    precision: 10
    scale: 2
  validation: ["numeric", "min:0", "max:999999"]
  default: 0
  comment: "Product price in USD"
```

#### SEO-Friendly Slug
```yaml
slug:
  type: string
  length: 255
  unique: true
  nullable: false
  validation: ["regex:/^[a-z0-9-]+$/", "min:3"]
  factory: ["slug()"]
  comment: "URL-friendly identifier"
```

#### User Status with Enum
```yaml
status:
  type: string
  length: 20
  default: "active"
  validation: ["in:active,inactive,banned,pending"]
  index: true
```

#### Rich Content
```yaml
content:
  type: longText
  nullable: false
  validation: ["min:10"]
  comment: "Article content in HTML or Markdown"
```

#### Metadata Storage
```yaml
settings:
  type: json
  nullable: true
  factory: ['json_encode(["theme" => "dark", "notifications" => true])']
  comment: "User preferences and settings"
```

## Relationships

### Supported Relationship Types

- `belongsTo` - Many-to-one relationship
- `hasOne` - One-to-one relationship  
- `hasMany` - One-to-many relationship
- `belongsToMany` - Many-to-many relationship
- `morphTo` - Polymorphic many-to-one
- `morphOne` - Polymorphic one-to-one
- `morphMany` - Polymorphic one-to-many

### BelongsTo Relationship

```yaml
relationships:
  category:
    type: belongsTo
    model: "Category"
    foreign_key: "category_id"    # Optional: defaults to {relation}_id
    owner_key: "id"               # Optional: defaults to id
```

Generated code:
```php
// Model method
public function category()
{
    return $this->belongsTo(Category::class, 'category_id');
}

// Migration (if foreign key not in fields)
$table->foreignId('category_id')->constrained('categories');
```

### HasMany Relationship

```yaml
relationships:
  posts:
    type: hasMany
    model: "Post"
    foreign_key: "user_id"        # Optional
    local_key: "id"               # Optional
```

### BelongsToMany Relationship

```yaml
relationships:
  tags:
    type: belongsToMany
    model: "Tag"
    pivot_table: "post_tags"      # Optional: auto-generated
    foreign_pivot_key: "post_id"  # Optional
    related_pivot_key: "tag_id"   # Optional
    pivot_columns: ["created_at"] # Optional: additional pivot columns
```

### Polymorphic Relationships

#### MorphTo
```yaml
relationships:
  commentable:
    type: morphTo
    # No model needed - determined at runtime
```

#### MorphMany
```yaml
relationships:
  comments:
    type: morphMany
    model: "Comment"
    morph_name: "commentable"     # Optional: defaults to relation name
```

### Relationship Examples

#### Blog System
```yaml
# Post model
relationships:
  author:
    type: belongsTo
    model: "User"
    foreign_key: "author_id"
    
  category:
    type: belongsTo
    model: "Category"
    
  tags:
    type: belongsToMany
    model: "Tag"
    pivot_table: "post_tags"
    
  comments:
    type: hasMany
    model: "Comment"
    
  featured_image:
    type: morphOne
    model: "Image"
    morph_name: "imageable"
```

#### E-commerce System
```yaml
# Order model
relationships:
  customer:
    type: belongsTo
    model: "User"
    foreign_key: "customer_id"
    
  products:
    type: belongsToMany
    model: "Product"
    pivot_table: "order_items"
    pivot_columns: ["quantity", "price", "created_at"]
    
  shipping_address:
    type: belongsTo
    model: "Address"
    foreign_key: "shipping_address_id"
    
  billing_address:
    type: belongsTo
    model: "Address"
    foreign_key: "billing_address_id"
```

## Schema Commands

### Create Schema
```bash
# Create new schema file
php artisan turbo:schema create product

# Create with template
php artisan turbo:schema create product --template=ecommerce
```

### Validate Schema
```bash
# Validate specific schema
php artisan turbo:schema validate product

# Validate all schemas
php artisan turbo:schema validate --all
```

### List Schemas
```bash
# List all available schemas
php artisan turbo:schema list

# Show schema details
php artisan turbo:schema show product
```

### Schema Cache
```bash
# Clear schema cache
php artisan turbo:schema clear-cache
```

## Schema Validation

TurboMaker automatically validates your schemas for:

### Field Validation
- Valid field types
- Required properties
- Type-specific constraints
- Validation rule syntax

### Relationship Validation  
- Valid relationship types
- Required model references
- Foreign key consistency
- Circular relationship detection

### Example Validation Errors
```bash
❌ Invalid field type 'invalid_type' for field 'title'
❌ Field 'price' must have a type
❌ Relationship 'category' must have a model
❌ Invalid relationship type 'invalid_relation'
```

## Best Practices

### 1. Naming Conventions
```yaml
# Good: snake_case for fields and tables
table_name: "blog_posts"
fields:
  published_at:
    type: datetime
    
# Good: PascalCase for models
relationships:
  author:
    model: "User"
```

### 2. Field Organization
```yaml
# Organize fields logically
fields:
  # Core identification
  title:
    type: string
  slug:
    type: string
    
  # Content
  content:
    type: longText
  excerpt:
    type: text
    
  # Status and metadata
  status:
    type: string
  published_at:
    type: datetime
    
  # Relationships (foreign keys)
  author_id:
    type: unsignedBigInteger
```

### 3. Validation Strategy
```yaml
# Combine database constraints with validation rules
email:
  type: string
  unique: true              # Database constraint
  validation: ["email"]     # Application validation
```

### 4. Default Values
```yaml
# Use appropriate defaults
is_active:
  type: boolean
  default: true
  
created_at:
  type: timestamp
  # Laravel handles automatically
```

### 5. Comments and Documentation
```yaml
user_id:
  type: unsignedBigInteger
  nullable: false
  index: true
  comment: "References users table - the content author"
```

## Schema Templates

### Blog Template
```yaml
name: "{{ name }}"
table_name: "{{ table_name }}"

fields:
  title:
    type: string
    length: 255
    nullable: false
    validation: ["min:3"]
    
  slug:
    type: string
    length: 255
    unique: true
    
  content:
    type: longText
    nullable: false
    
  published_at:
    type: datetime
    nullable: true

relationships:
  author:
    type: belongsTo
    model: "User"
```

### E-commerce Template
```yaml
name: "{{ name }}"
table_name: "{{ table_name }}"

fields:
  name:
    type: string
    length: 255
    nullable: false
    
  slug:
    type: string
    length: 255
    unique: true
    
  price:
    type: decimal
    attributes: { precision: 10, scale: 2 }
    validation: ["numeric", "min:0"]
    
  is_active:
    type: boolean
    default: true
```

## Next Steps

- **[[Field Types]]** - Complete field type reference
- **[[Relationships]]** - Deep dive into relationships
- **[[Custom Field Types]]** - Create custom field types
- **[[Examples]]** - Real-world schema examples

---

**Master the schema system and unlock TurboMaker's full potential for rapid development.**
