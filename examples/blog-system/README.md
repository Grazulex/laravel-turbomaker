# Blog System Example

This example demonstrates how to build a complete blog system using Laravel TurboMaker. It covers basic CRUD operations, relationships, authentication, and authorization.

## Overview

The blog system includes:
- **Users** - Authors and readers with authentication
- **Categories** - Organize posts by topic
- **Posts** - Blog articles with rich content
- **Comments** - User feedback on posts

## System Architecture

```
User (1) -----> (M) Post (M) -----> (1) Category
 |                   |
 |                   v
 |               (M) Comment
 |                   ^
 |___________________|
```

## Relationships

- User `hasMany` Posts
- User `hasMany` Comments  
- Category `hasMany` Posts
- Post `belongsTo` User (author)
- Post `belongsTo` Category
- Post `hasMany` Comments
- Comment `belongsTo` Post
- Comment `belongsTo` User (commenter)

## Generation Commands

Follow these commands in order to build the complete blog system:

### 1. Generate User Model (if not using Laravel Breeze/UI)

```bash
# Generate User with relationships
php artisan turbo:make User \
    --has-many=Post \
    --has-many=Comment \
    --policies \
    --tests \
    --factory
```

### 2. Generate Category Model

```bash
# Categories organize posts
php artisan turbo:make Category \
    --has-many=Post \
    --tests \
    --factory \
    --seeder
```

### 3. Generate Post Model

```bash
# Posts are the main content
php artisan turbo:make Post \
    --belongs-to=User \
    --belongs-to=Category \
    --has-many=Comment \
    --policies \
    --tests \
    --factory \
    --seeder \
    --views
```

### 4. Generate Comment Model

```bash
# Comments allow user interaction
php artisan turbo:make Comment \
    --belongs-to=Post \
    --belongs-to=User \
    --policies \
    --tests \
    --factory
```

## Generated Structure

After running the commands above, you'll have:

```
app/
├── Models/
│   ├── User.php                 # User model with relationships
│   ├── Category.php             # Category model
│   ├── Post.php                 # Post model with relationships
│   └── Comment.php              # Comment model with relationships
├── Http/
│   ├── Controllers/
│   │   ├── UserController.php
│   │   ├── CategoryController.php
│   │   ├── PostController.php
│   │   └── CommentController.php
│   ├── Requests/
│   │   ├── StoreCategoryRequest.php
│   │   ├── UpdateCategoryRequest.php
│   │   ├── StorePostRequest.php
│   │   ├── UpdatePostRequest.php
│   │   ├── StoreCommentRequest.php
│   │   └── UpdateCommentRequest.php
│   └── Resources/
│       ├── UserResource.php
│       ├── CategoryResource.php
│       ├── PostResource.php
│       └── CommentResource.php
├── Policies/
│   ├── UserPolicy.php
│   ├── PostPolicy.php
│   └── CommentPolicy.php
database/
├── migrations/
│   ├── create_users_table.php
│   ├── create_categories_table.php
│   ├── create_posts_table.php
│   └── create_comments_table.php
├── factories/
│   ├── UserFactory.php
│   ├── CategoryFactory.php
│   ├── PostFactory.php
│   └── CommentFactory.php
└── seeders/
    ├── CategorySeeder.php
    └── PostSeeder.php
resources/views/
├── categories/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
└── posts/
    ├── index.blade.php
    ├── create.blade.php
    ├── edit.blade.php
    └── show.blade.php
tests/
├── Feature/
│   ├── UserTest.php
│   ├── CategoryTest.php
│   ├── PostTest.php
│   └── CommentTest.php
└── Unit/
    ├── UserTest.php
    ├── CategoryTest.php
    ├── PostTest.php
    └── CommentTest.php
```

## Key Generated Code Examples

### Post Model (app/Models/Post.php)

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
        'excerpt',
        'published_at',
        'user_id',
        'category_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
```

### Post Controller (app/Http/Controllers/PostController.php)

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with(['user', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('posts.create', compact('categories'));
    }

    public function store(StorePostRequest $request)
    {
        $post = Post::create(array_merge(
            $request->validated(),
            ['user_id' => auth()->id()]
        ));

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post created successfully.');
    }

    // ... other methods
}
```

### Post Policy (app/Policies/PostPolicy.php)

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function view(?User $user, Post $post): bool
    {
        return $post->published_at !== null || 
               ($user && $user->id === $post->user_id);
    }

    public function create(User $user): bool
    {
        return true; // All authenticated users can create posts
    }

    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
```

## Database Setup

Run migrations and seed data:

```bash
# Run migrations
php artisan migrate

# Seed categories and sample posts
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=PostSeeder
```

## Testing

Run the generated tests:

```bash
# Run all tests
php artisan test

# Run specific feature tests
php artisan test tests/Feature/PostTest.php

# Run with coverage
php artisan test --coverage
```

## Customization Examples

### Adding Additional Fields

After generation, you can add fields to migrations:

```php
// Add to create_posts_table migration
$table->string('featured_image')->nullable();
$table->boolean('is_featured')->default(false);
$table->json('meta_data')->nullable();
```

### Enhancing Models

Add custom methods to generated models:

```php
// Add to Post.php
public function getExcerptAttribute($value)
{
    return $value ?: Str::limit(strip_tags($this->content), 150);
}

public function isPublished(): bool
{
    return $this->published_at !== null && $this->published_at <= now();
}

public function scopePublished($query)
{
    return $query->where('published_at', '<=', now());
}
```

### Custom Views

Enhance the generated views with better styling:

```blade
{{-- posts/index.blade.php enhancement --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Blog Posts</h1>
                @can('create', App\Models\Post::class)
                    <a href="{{ route('posts.create') }}" class="btn btn-primary">
                        Create New Post
                    </a>
                @endcan
            </div>

            @foreach($posts as $post)
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="{{ route('posts.show', $post) }}">
                                {{ $post->title }}
                            </a>
                        </h5>
                        <p class="card-text">{{ $post->excerpt }}</p>
                        <small class="text-muted">
                            By {{ $post->user->name }} 
                            in {{ $post->category->name }}
                            on {{ $post->created_at->format('M d, Y') }}
                        </small>
                    </div>
                </div>
            @endforeach

            {{ $posts->links() }}
        </div>
    </div>
</div>
@endsection
```

## Advanced Features

### Adding Rich Text Editor

Enhance the post creation form:

```blade
{{-- Add to posts/create.blade.php --}}
<div class="form-group">
    <label for="content">Content</label>
    <textarea name="content" id="content" rows="10" required>{{ old('content') }}</textarea>
</div>

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/34.0.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor.create(document.querySelector('#content'));
</script>
@endpush
```

### Adding Search Functionality

Add search to the PostController:

```php
public function index(Request $request)
{
    $query = Post::with(['user', 'category']);

    if ($request->filled('search')) {
        $query->where('title', 'like', '%' . $request->search . '%')
              ->orWhere('content', 'like', '%' . $request->search . '%');
    }

    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    $posts = $query->orderBy('created_at', 'desc')->paginate(15);
    $categories = Category::all();

    return view('posts.index', compact('posts', 'categories'));
}
```

## Next Steps

1. **Authentication**: Set up Laravel Breeze or Sanctum for user management
2. **Authorization**: Customize the generated policies for your business rules
3. **File Uploads**: Add image upload functionality for featured images
4. **Caching**: Implement caching for better performance
5. **Search**: Add full-text search capabilities
6. **API**: Use the generated API controllers for mobile/SPA applications

This blog system provides a solid foundation that you can extend based on your specific requirements!