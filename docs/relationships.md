# Working with Relationships

Laravel TurboMaker makes it easy to scaffold modules with predefined relationships, automatically generating the necessary model relationships, migrations, and form handling.

## Supported Relationship Types

TurboMaker supports the three most common Laravel relationships:

- **belongsTo** - Many-to-one relationships
- **hasMany** - One-to-many relationships  
- **hasOne** - One-to-one relationships

## Basic Relationship Syntax

### Single Relationships

```bash
# Post belongs to User
php artisan turbo:make Post --belongs-to=User

# User has many Posts
php artisan turbo:make User --has-many=Post

# User has one Profile
php artisan turbo:make User --has-one=Profile
```

### Multiple Relationships

```bash
# Post belongs to User and Category, has many Comments
php artisan turbo:make Post --belongs-to=User --belongs-to=Category --has-many=Comment

# Order belongs to User, has many OrderItems and has one Invoice
php artisan turbo:make Order --belongs-to=User --has-many=OrderItem --has-one=Invoice
```

### Alternative Syntax

You can also use the `--relationships` option with comma-separated format:

```bash
php artisan turbo:make Post --relationships="user:belongsTo,category:belongsTo,comments:hasMany"
```

## What Gets Generated

### Model Relationships

TurboMaker automatically adds the relationship methods to your models:

**Post.php** (with `--belongs-to=User --has-many=Comment`):
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'user_id', // Foreign key added automatically
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
```

### Migration Foreign Keys

Foreign key columns are automatically added to migrations:

**create_posts_table.php**:
```php
public function up()
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('content');
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->timestamps();
    });
}
```

### Form Integration

Controllers and form requests are updated to handle relationships:

**StorePostRequest.php**:
```php
public function rules()
{
    return [
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'user_id' => 'required|exists:users,id',
    ];
}
```

**Views with relationship dropdowns**:
```blade
<div class="form-group">
    <label for="user_id">User</label>
    <select name="user_id" id="user_id" class="form-control" required>
        <option value="">Select User</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                {{ $user->name }}
            </option>
        @endforeach
    </select>
</div>
```

## Real-World Examples

### Blog System

```bash
# Create User model first (if not exists)
php artisan turbo:make User --has-many=Post

# Create Category
php artisan turbo:make Category --has-many=Post

# Create Post with relationships
php artisan turbo:make Post --belongs-to=User --belongs-to=Category --has-many=Comment

# Create Comment
php artisan turbo:make Comment --belongs-to=Post --belongs-to=User
```

### E-commerce System

```bash
# User can have many orders
php artisan turbo:make User --has-many=Order

# Product belongs to category, has many order items
php artisan turbo:make Product --belongs-to=Category --has-many=OrderItem

# Order belongs to user, has many items, has one invoice
php artisan turbo:make Order --belongs-to=User --has-many=OrderItem --has-one=Invoice

# OrderItem belongs to order and product
php artisan turbo:make OrderItem --belongs-to=Order --belongs-to=Product

# Invoice belongs to order
php artisan turbo:make Invoice --belongs-to=Order
```

### CRM System

```bash
# Company has many contacts and projects
php artisan turbo:make Company --has-many=Contact --has-many=Project

# Contact belongs to company, has many projects (many-to-many would need manual setup)
php artisan turbo:make Contact --belongs-to=Company

# Project belongs to company and has many tasks
php artisan turbo:make Project --belongs-to=Company --has-many=Task

# Task belongs to project and optionally to contact (assignee)
php artisan turbo:make Task --belongs-to=Project --belongs-to=Contact
```

## Relationship Naming Conventions

TurboMaker follows Laravel conventions:

### BelongsTo Relationships
- **Model Name**: Singular form of the related model
- **Foreign Key**: `{relationship}_id` (e.g., `user_id`, `category_id`)
- **Method Name**: camelCase singular (e.g., `user()`, `category()`)

### HasMany Relationships  
- **Model Name**: Singular form of the related model
- **Method Name**: camelCase plural (e.g., `posts()`, `comments()`)

### HasOne Relationships
- **Model Name**: Singular form of the related model  
- **Method Name**: camelCase singular (e.g., `profile()`, `invoice()`)

## Advanced Relationship Handling

### Custom Foreign Keys

For non-standard foreign key names, you'll need to manually adjust after generation:

```php
// After generation, customize if needed
public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'author_id'); // Custom foreign key
}
```

### Polymorphic Relationships

Currently, TurboMaker doesn't auto-generate polymorphic relationships. These need to be added manually after scaffolding.

### Many-to-Many Relationships

Pivot tables and many-to-many relationships aren't automatically generated but can be added manually:

```php
// Add manually after generation
public function tags(): BelongsToMany
{
    return $this->belongsToMany(Tag::class);
}
```

## Testing with Relationships

When generating tests with relationships, TurboMaker creates factories and tests that understand the relationships:

```bash
php artisan turbo:make Post --belongs-to=User --has-many=Comment --tests --factory
```

**Generated PostFactory.php**:
```php
public function definition()
{
    return [
        'title' => $this->faker->sentence(),
        'content' => $this->faker->paragraphs(3, true),
        'user_id' => User::factory(),
    ];
}
```

**Generated PostTest.php**:
```php
it('can create a post with user relationship', function () {
    $user = User::factory()->create();
    
    $post = Post::factory()->create([
        'user_id' => $user->id,
    ]);
    
    expect($post->user)->toBeInstanceOf(User::class);
    expect($post->user->id)->toBe($user->id);
});
```

## Best Practices

### 1. Define Relationships Early
Plan your relationships before generating models to avoid rework.

### 2. Start with Core Models
Generate the models that others depend on first (User, Category, etc.).

### 3. Use Consistent Naming
Follow Laravel naming conventions for predictable results.

### 4. Generate Related Models Together
Generate related models in the same session to maintain consistency.

### 5. Review Generated Code
Always review the generated relationships and adjust if needed.

## Troubleshooting

### Missing Related Models
If you reference a model that doesn't exist, TurboMaker will still generate the relationship code. Create the missing model afterward:

```bash
# This works even if User doesn't exist yet
php artisan turbo:make Post --belongs-to=User

# Create User afterward
php artisan turbo:make User --has-many=Post
```

### Foreign Key Constraints
If you get foreign key constraint errors, ensure:
1. Related models are migrated first
2. Foreign key columns exist
3. Referenced IDs are valid

### Relationship Method Conflicts
If you have naming conflicts, manually rename the relationship methods after generation.

## Examples in Practice

See the [examples directory](../examples/) for complete working examples of:
- [Blog System](../examples/blog-system/) - User, Post, Comment relationships
- [E-commerce](../examples/ecommerce/) - Product, Order, OrderItem relationships
- [CRM](../examples/crm/) - Company, Contact, Project relationships