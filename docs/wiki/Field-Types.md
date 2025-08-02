# Field Types

Laravel TurboMaker supports 28+ field types covering all Laravel column types plus an extensible architecture for custom types.

## Core Field Types

### String Types

#### `string`
Standard string column with optional length.

```yaml
name:
  type: string
  length: 255
  nullable: false
  validation: ["min:3", "max:255"]
```

**Generated Migration**: `$table->string('name', 255)`  
**Validation**: `['required', 'string', 'max:255', 'min:3']`  
**Factory**: Smart name-based generation (e.g., `fake()->name()` for "name" field)

#### `text`
Text column for longer content.

```yaml
description:
  type: text
  nullable: true
```

**Generated Migration**: `$table->text('description')->nullable()`  
**Validation**: `['nullable', 'string']`  
**Factory**: `fake()->paragraph()`

#### `longText`
Long text column for extensive content.

```yaml
content:
  type: longText
  nullable: false
```

**Generated Migration**: `$table->longText('content')`  
**Validation**: `['required', 'string']`  
**Factory**: `fake()->paragraphs(3, true)`

#### `mediumText`
Medium text column.

```yaml
summary:
  type: mediumText
  nullable: true
```

### Integer Types

#### `integer`
Standard integer column.

```yaml
quantity:
  type: integer
  nullable: false
  default: 0
  validation: ["min:0"]
```

**Generated Migration**: `$table->integer('quantity')->default(0)`  
**Validation**: `['required', 'integer', 'min:0']`  
**Factory**: `fake()->numberBetween(1, 1000)`  
**Cast**: `'integer'`

#### `bigInteger`
Big integer for large numbers.

```yaml
views_count:
  type: bigInteger
  default: 0
```

**Generated Migration**: `$table->bigInteger('views_count')->default(0)`

#### `unsignedBigInteger`
Unsigned big integer, commonly used for foreign keys.

```yaml
user_id:
  type: unsignedBigInteger
  nullable: false
  index: true
```

**Generated Migration**: `$table->unsignedBigInteger('user_id')->index()`  
**Validation**: `['required', 'integer', 'min:0']`  
**Factory**: `fake()->numberBetween(1, 999999)`

#### `tinyInteger`, `smallInteger`, `mediumInteger`
Smaller integer types for specific use cases.

```yaml
status:
  type: tinyInteger
  default: 1
```

### Numeric Types

#### `decimal`
Decimal numbers with precision and scale.

```yaml
price:
  type: decimal
  attributes:
    precision: 10
    scale: 2
  validation: ["numeric", "min:0"]
```

**Generated Migration**: `$table->decimal('price', 10, 2)`  
**Validation**: `['required', 'numeric', 'min:0']`  
**Factory**: `fake()->randomFloat(2, 0, 999)`  
**Cast**: `'decimal:2'`

#### `float` / `double`
Floating point numbers.

```yaml
rating:
  type: float
  validation: ["numeric", "min:0", "max:5"]
```

### Boolean Type

#### `boolean`
True/false values.

```yaml
is_active:
  type: boolean
  default: true
```

**Generated Migration**: `$table->boolean('is_active')->default(true)`  
**Validation**: `['required', 'boolean']`  
**Factory**: `fake()->boolean()`  
**Cast**: `'boolean'`

### Date & Time Types

#### `date`
Date only (Y-m-d).

```yaml
birth_date:
  type: date
  nullable: true
  validation: ["before:today"]
```

**Generated Migration**: `$table->date('birth_date')->nullable()`  
**Validation**: `['nullable', 'date', 'before:today']`  
**Factory**: `fake()->date()`  
**Cast**: `'date'`

#### `datetime`
Date and time.

```yaml
published_at:
  type: datetime
  nullable: true
```

**Generated Migration**: `$table->datetime('published_at')->nullable()`  
**Cast**: `'datetime'`

#### `timestamp`
Timestamp column.

```yaml
last_login_at:
  type: timestamp
  nullable: true
```

#### `time`
Time only (H:i:s).

```yaml
opening_time:
  type: time
  nullable: false
```

### Special Types

#### `json`
JSON data storage.

```yaml
metadata:
  type: json
  nullable: true
```

**Generated Migration**: `$table->json('metadata')->nullable()`  
**Validation**: `['nullable', 'json']`  
**Factory**: `json_encode(['key' => 'value'])`  
**Cast**: `'array'`

#### `uuid`
UUID strings.

```yaml
uuid:
  type: uuid
  nullable: false
  unique: true
```

**Generated Migration**: `$table->uuid('uuid')->unique()`  
**Validation**: `['required', 'uuid']`  
**Factory**: `fake()->uuid()`

#### `email`
Email addresses with validation.

```yaml
email:
  type: email
  nullable: false
  unique: true
```

**Generated Migration**: `$table->string('email')->unique()`  
**Validation**: `['required', 'string', 'email', 'unique:table,email']`  
**Factory**: `fake()->unique()->safeEmail()`

#### `url`
URL strings with validation.

```yaml
website:
  type: url
  nullable: true
```

**Validation**: `['nullable', 'string', 'url']`  
**Factory**: `fake()->url()`

#### `foreignId`
Foreign key relationships.

```yaml
category_id:
  type: foreignId
  nullable: false
  index: true
```

**Generated Migration**: `$table->foreignId('category_id')->index()`

#### `morphs`
Polymorphic relationships.

```yaml
commentable:
  type: morphs
```

**Generated Migration**: `$table->morphs('commentable')`

#### `binary`
Binary data storage.

```yaml
file_data:
  type: binary
  nullable: true
```

## Smart Features

### Name-Based Factory Generation

TurboMaker intelligently generates factory data based on field names:

```yaml
first_name:
  type: string
  # Factory: fake()->firstName()

email:
  type: string  
  # Factory: fake()->unique()->safeEmail()

phone:
  type: string
  # Factory: fake()->phoneNumber()

address:
  type: string
  # Factory: fake()->address()

title:
  type: string
  # Factory: fake()->sentence(3)

slug:
  type: string
  # Factory: fake()->slug()
```

### Context-Aware Validation

Validation rules are intelligently generated based on field type and attributes:

```yaml
birth_date:
  type: date
  # Auto-adds: before:today

end_date:
  type: date
  attributes:
    after_field: start_date
  # Auto-adds: after:start_date

price:
  type: decimal
  # Auto-adds: numeric, min:0

email:
  type: email
  unique: true
  # Auto-adds: email, unique:table,email
```

### Automatic Casting

Models get proper casting based on field types:

```php
protected $casts = [
    'is_active' => 'boolean',
    'price' => 'decimal:2',
    'published_at' => 'datetime',
    'metadata' => 'array',
];
```

## Field Options

### Common Options

All field types support these options:

```yaml
field_name:
  type: string
  nullable: false          # Default: false
  unique: false           # Default: false  
  index: false            # Default: false
  default: null           # Default value
  length: 255             # For string types
  comment: "Field description"
  validation: ["min:3"]   # Custom validation rules
  factory: ["word()"]     # Custom factory rules
  attributes:             # Type-specific attributes
    precision: 10
    scale: 2
```

### Type-Specific Attributes

#### Decimal Fields
```yaml
price:
  type: decimal
  attributes:
    precision: 10  # Total digits
    scale: 2       # Decimal places
```

#### String Fields with Length
```yaml
code:
  type: string
  length: 10
```

#### Date Fields with Constraints
```yaml
birth_date:
  type: date
  validation: ["before:today"]
  
end_date:
  type: date
  validation: ["after:start_date"]
```

## Examples by Use Case

### E-commerce Product
```yaml
fields:
  name:
    type: string
    length: 255
    nullable: false
    validation: ["min:3"]
    
  slug:
    type: string
    length: 255
    unique: true
    validation: ["regex:/^[a-z0-9-]+$/"]
    
  price:
    type: decimal
    attributes: { precision: 10, scale: 2 }
    validation: ["numeric", "min:0"]
    
  description:
    type: longText
    nullable: true
    
  is_active:
    type: boolean
    default: true
    
  stock_quantity:
    type: integer
    default: 0
    validation: ["min:0"]
    
  metadata:
    type: json
    nullable: true
    
  published_at:
    type: datetime
    nullable: true
```

### User Profile
```yaml
fields:
  first_name:
    type: string
    length: 100
    nullable: false
    
  last_name:
    type: string
    length: 100
    nullable: false
    
  email:
    type: email
    unique: true
    
  phone:
    type: string
    length: 20
    nullable: true
    
  birth_date:
    type: date
    nullable: true
    validation: ["before:today"]
    
  avatar_url:
    type: url
    nullable: true
    
  bio:
    type: text
    nullable: true
    
  is_verified:
    type: boolean
    default: false
```

## Next Steps

- **[[Custom Field Types]]** - Create your own field types
- **[[Schema System]]** - Complete schema documentation
- **[[Relationships]]** - Working with relationships
- **[[Examples]]** - Real-world schema examples

---

**Need a field type that doesn't exist? Learn how to create [[Custom Field Types]].**
