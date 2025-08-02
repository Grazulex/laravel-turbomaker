# Getting Started with Laravel TurboMaker V6

Welcome to Laravel TurboMaker V6! This guide will get you up and running quickly with our extensible, schema-driven code generation.

## Prerequisites

Before you begin, make sure you have:
- PHP 8.2 or higher
- Laravel 11.x
- Composer installed

## Installation

### 1. Install via Composer

```bash
composer require --dev grazulex/laravel-turbomaker
```

### 2. Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=turbomaker-config
```

This creates `config/turbomaker.php` where you can customize paths, defaults, and add custom field types.

### 3. Publish Stubs (Optional)

```bash
php artisan vendor:publish --tag=turbomaker-stubs
```

This publishes the templates to `resources/stubs/turbomaker/` for customization.

## Your First Module

Let's create a complete blog post module:

### Simple Generation

```bash
php artisan turbo:make Post
```

This creates a basic Post module with `id`, `name`, `created_at`, and `updated_at` fields.

### With Custom Fields

```bash
php artisan turbo:make Post --fields="title:string,content:longText,published:boolean,published_at:datetime"
```

### What Gets Generated

After running the command, you'll have:

```
app/
├── Models/
│   └── Post.php                    # Eloquent model
├── Http/
│   ├── Controllers/
│   │   ├── PostController.php      # Web controller
│   │   └── Api/
│   │       └── PostController.php  # API controller
│   ├── Requests/
│   │   ├── StorePostRequest.php    # Store validation
│   │   └── UpdatePostRequest.php   # Update validation
│   └── Resources/
│       └── PostResource.php        # API resource
database/
├── migrations/
│   └── 2024_xx_xx_create_posts_table.php
└── factories/
    └── PostFactory.php             # Model factory
resources/views/posts/
├── index.blade.php                 # List posts
├── create.blade.php                # Create form
├── edit.blade.php                  # Edit form
└── show.blade.php                  # Show post
routes/
├── web.php                         # Web routes added
└── api.php                         # API routes added
tests/
├── Feature/
│   └── PostTest.php                # Feature tests
└── Unit/
    └── Models/
        └── PostTest.php            # Unit tests
```

## Generated Code Examples

### Model (app/Models/Post.php)
```php
<?php

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content', 
        'published',
        'published_at',
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];
}
```

### Controller (app/Http/Controllers/PostController.php)
```php
<?php

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(15);
        return view('posts.index', compact('posts'));
    }

    public function store(StorePostRequest $request)
    {
        $post = Post::create($request->validated());
        return redirect()->route('posts.show', $post)
            ->with('success', 'Post created successfully.');
    }
    
    // ... other CRUD methods
}
```

### Migration
```php
<?php

Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->longText('content');
    $table->boolean('published')->default(false);
    $table->datetime('published_at')->nullable();
    $table->timestamps();
});
```

### Form Request (app/Http/Requests/StorePostRequest.php)
```php
<?php

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
```

## Next Steps

### Run Migration
```bash
php artisan migrate
```

### Test Your Module
```bash
# Run the generated tests
php artisan test --filter=Post

# Or manually visit the routes
# Web: /posts
# API: /api/posts
```

### Add Relationships
```bash
php artisan turbo:make Comment --fields="content:text,approved:boolean" --belongs-to=Post --belongs-to=User
```

### API-Only Development
```bash
php artisan turbo:api Product --fields="name:string,price:decimal" --tests --policies
```

## Common Options

| Option | Description | Example |
|--------|-------------|---------|
| `--fields` | Define custom fields | `--fields="name:string,email:email"` |
| `--tests` | Generate tests | `--tests` |
| `--factory` | Generate factory | `--factory` |
| `--seeder` | Generate seeder | `--seeder` |
| `--policies` | Generate policies | `--policies` |
| `--force` | Overwrite existing files | `--force` |
| `--api-only` | Skip web views | `--api-only` |

## Schema Files

For complex models, use schema files:

### Create Schema File
```bash
# Create resources/schemas/product.schema.yml
php artisan turbo:schema create product
```

### Example Schema
```yaml
# resources/schemas/product.schema.yml
name: "Product"
table_name: "products"

fields:
  name:
    type: string
    length: 255
    nullable: false
    validation: ["min:3"]
    
  price:
    type: decimal
    attributes:
      precision: 10
      scale: 2
    validation: ["numeric", "min:0"]
    
  is_active:
    type: boolean
    default: true

relationships:
  category:
    type: belongsTo
    model: "Category"
    foreign_key: "category_id"
```

### Generate from Schema
```bash
php artisan turbo:make Product --schema=product
```

## What's Next?

- **[[Field Types]]** - Learn about all available field types
- **[[Relationships]]** - Working with model relationships  
- **[[Custom Field Types]]** - Create your own field types
- **[[Schema System]]** - Deep dive into the schema system
- **[[Commands]]** - Complete command reference
- **[[Examples]]** - Real-world examples

## Need Help?

- **[[FAQ]]** - Common questions and answers
- **[[Troubleshooting]]** - Solutions to common issues
- **[GitHub Issues](https://github.com/Grazulex/laravel-turbomaker/issues)** - Report bugs or request features
- **[Discussions](https://github.com/Grazulex/laravel-turbomaker/discussions)** - Community help

---

**🎉 Congratulations! You've generated your first Laravel TurboMaker module. Ready to explore more? Check out [[Field Types]] next.**
