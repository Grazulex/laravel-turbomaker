# API-Only Application Example

This example demonstrates building a complete REST API using Laravel TurboMaker's API-focused commands. Perfect for mobile applications, SPAs, or microservices.

## Overview

We'll build a Product Catalog API with:
- **Products** - Main catalog items
- **Categories** - Product organization
- **Reviews** - Customer feedback
- **Users** - API consumers with authentication

## API Architecture

```
Users -----> Reviews -----> Products -----> Categories
  |            |              |
  |            |              v
  |            |          Inventory
  |            |
  |----------> Orders -----> OrderItems
```

## API Endpoints

The generated API will provide these endpoints:

### Categories
- `GET /api/categories` - List all categories
- `GET /api/categories/{id}` - Get category details
- `POST /api/categories` - Create category
- `PUT /api/categories/{id}` - Update category
- `DELETE /api/categories/{id}` - Delete category

### Products
- `GET /api/products` - List products (with filtering)
- `GET /api/products/{id}` - Get product details
- `POST /api/products` - Create product
- `PUT /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product

### Reviews
- `GET /api/products/{id}/reviews` - Get product reviews
- `POST /api/products/{id}/reviews` - Add review
- `PUT /api/reviews/{id}` - Update review
- `DELETE /api/reviews/{id}` - Delete review

## Generation Commands

Execute these commands to build the complete API:

### 1. Generate User Model (for API authentication)

```bash
php artisan turbo:api User \
    --has-many=Review \
    --has-many=Order \
    --tests \
    --factory \
    --policies
```

### 2. Generate Category API

```bash
php artisan turbo:api Category \
    --has-many=Product \
    --tests \
    --factory \
    --seeder
```

### 3. Generate Product API

```bash
php artisan turbo:api Product \
    --belongs-to=Category \
    --has-many=Review \
    --has-many=OrderItem \
    --tests \
    --factory \
    --seeder \
    --policies \
    --services
```

### 4. Generate Review API

```bash
php artisan turbo:api Review \
    --belongs-to=Product \
    --belongs-to=User \
    --tests \
    --factory \
    --policies
```

### 5. Generate Order System (Optional)

```bash
# Orders for purchase tracking
php artisan turbo:api Order \
    --belongs-to=User \
    --has-many=OrderItem \
    --tests \
    --factory \
    --policies \
    --services \
    --actions

# Order items
php artisan turbo:api OrderItem \
    --belongs-to=Order \
    --belongs-to=Product \
    --tests \
    --factory
```

## Generated API Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── CategoryController.php
│   │   ├── ProductController.php
│   │   ├── ReviewController.php
│   │   ├── OrderController.php
│   │   └── OrderItemController.php
│   ├── Resources/
│   │   ├── CategoryResource.php
│   │   ├── ProductResource.php
│   │   ├── ReviewResource.php
│   │   ├── OrderResource.php
│   │   └── OrderItemResource.php
│   └── Requests/
│       ├── StoreCategoryRequest.php
│       ├── UpdateCategoryRequest.php
│       ├── StoreProductRequest.php
│       ├── UpdateProductRequest.php
│       └── ...
├── Models/
│   ├── Category.php
│   ├── Product.php
│   ├── Review.php
│   ├── Order.php
│   └── OrderItem.php
├── Policies/
│   ├── ProductPolicy.php
│   ├── ReviewPolicy.php
│   └── OrderPolicy.php
└── Services/
    ├── ProductService.php
    └── OrderService.php
routes/
└── api.php                    # All API routes
tests/
├── Feature/
│   ├── CategoryApiTest.php
│   ├── ProductApiTest.php
│   ├── ReviewApiTest.php
│   └── OrderApiTest.php
└── Unit/
    └── ...
```

## Key Generated Code Examples

### Product API Controller

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'reviews']);

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Price range filtering
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->paginate($request->get('per_page', 15));

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());
        $product->load(['category', 'reviews']);

        return new ProductResource($product);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'reviews.user']);
        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        $product->load(['category', 'reviews']);

        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
```

### Product API Resource

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => [
                'amount' => $this->price,
                'currency' => 'USD',
                'formatted' => '$' . number_format($this->price, 2),
            ],
            'sku' => $this->sku,
            'in_stock' => $this->stock_quantity > 0,
            'stock_quantity' => $this->stock_quantity,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'reviews' => [
                'data' => ReviewResource::collection($this->whenLoaded('reviews')),
                'stats' => $this->when($this->reviews_count !== null, [
                    'count' => $this->reviews_count ?? 0,
                    'average_rating' => $this->reviews_avg_rating ? round($this->reviews_avg_rating, 1) : null,
                ]),
            ],
            'images' => $this->when($this->images, $this->images),
            'meta' => [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
        ];
    }
}
```

### API Routes

```php
// routes/api.php (generated and enhanced)
<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('v1')->group(function () {
    // Categories
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    
    // Products
    Route::apiResource('products', ProductController::class)->only(['index', 'show']);
    Route::get('products/{product}/reviews', [ReviewController::class, 'index']);
    
    // Search
    Route::get('search/products', [ProductController::class, 'search']);
});

// Authenticated routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Categories (admin only)
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
    
    // Products (admin only)
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    
    // Reviews (user actions)
    Route::apiResource('reviews', ReviewController::class)->except(['index']);
    Route::post('products/{product}/reviews', [ReviewController::class, 'store']);
    
    // Orders
    Route::apiResource('orders', OrderController::class);
    Route::post('orders/{order}/items', [OrderController::class, 'addItem']);
    Route::delete('orders/{order}/items/{item}', [OrderController::class, 'removeItem']);
});
```

## API Authentication

Set up Sanctum for API authentication:

### 1. Install Laravel Sanctum

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 2. Add to API Middleware

```php
// config/sanctum.php
'middleware' => [
    'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
],
```

### 3. Authentication Controller

```php
// app/Http/Controllers/Api/AuthController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
```

## API Testing

### Feature Tests Example

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    public function test_can_list_products()
    {
        Product::factory(3)->create(['category_id' => $this->category->id]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'price',
                            'category',
                        ]
                    ],
                    'links',
                    'meta'
                ]);
    }

    public function test_can_create_product_when_authenticated()
    {
        $this->actingAs($this->user, 'sanctum');

        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 29.99,
            'sku' => 'TEST001',
            'stock_quantity' => 100,
            'category_id' => $this->category->id,
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
                ->assertJson([
                    'data' => [
                        'name' => 'Test Product',
                        'price' => ['amount' => 29.99],
                    ]
                ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST001',
        ]);
    }

    public function test_cannot_create_product_when_unauthenticated()
    {
        $productData = [
            'name' => 'Test Product',
            'price' => 29.99,
            'category_id' => $this->category->id,
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(401);
    }

    public function test_can_filter_products_by_category()
    {
        $category1 = Category::factory()->create(['name' => 'Electronics']);
        $category2 = Category::factory()->create(['name' => 'Books']);
        
        Product::factory(2)->create(['category_id' => $category1->id]);
        Product::factory(3)->create(['category_id' => $category2->id]);

        $response = $this->getJson("/api/v1/products?category={$category1->id}");

        $response->assertStatus(200)
                ->assertJsonCount(2, 'data');
    }

    public function test_can_search_products()
    {
        Product::factory()->create(['name' => 'iPhone 13']);
        Product::factory()->create(['name' => 'Samsung Galaxy']);
        Product::factory()->create(['name' => 'MacBook Pro']);

        $response = $this->getJson('/api/v1/products?search=iPhone');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.name', 'iPhone 13');
    }
}
```

## API Documentation

### OpenAPI/Swagger Setup

Add API documentation with Laravel Scribe:

```bash
composer require --dev knuckleswtf/scribe
php artisan vendor:publish --tag=scribe-config
php artisan scribe:generate
```

### API Response Examples

#### Success Response (Product List)

```json
{
  "data": [
    {
      "id": 1,
      "name": "iPhone 13",
      "description": "Latest iPhone with advanced camera",
      "price": {
        "amount": 999.00,
        "currency": "USD",
        "formatted": "$999.00"
      },
      "sku": "IPHONE13-128",
      "in_stock": true,
      "stock_quantity": 50,
      "category": {
        "id": 1,
        "name": "Electronics",
        "slug": "electronics"
      },
      "reviews": {
        "stats": {
          "count": 125,
          "average_rating": 4.5
        }
      },
      "meta": {
        "created_at": "2024-01-15T10:30:00Z",
        "updated_at": "2024-01-20T14:45:00Z"
      }
    }
  ],
  "links": {
    "first": "http://api.example.com/v1/products?page=1",
    "last": "http://api.example.com/v1/products?page=10",
    "prev": null,
    "next": "http://api.example.com/v1/products?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 15,
    "to": 15,
    "total": 150
  }
}
```

#### Error Response (Validation)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "price": ["The price must be a number."],
    "category_id": ["The selected category id is invalid."]
  }
}
```

## Performance Optimization

### Eager Loading

```php
// In ProductController
public function index(Request $request)
{
    $products = Product::with([
        'category:id,name,slug',
        'reviews:id,product_id,rating'
    ])
    ->withCount('reviews')
    ->withAvg('reviews', 'rating')
    ->paginate();

    return ProductResource::collection($products);
}
```

### Caching

```php
// Cache popular products
public function popular()
{
    $products = Cache::remember('popular_products', 3600, function () {
        return Product::with(['category', 'reviews'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->having('reviews_count', '>', 10)
            ->orderBy('reviews_avg_rating', 'desc')
            ->limit(20)
            ->get();
    });

    return ProductResource::collection($products);
}
```

## Next Steps

1. **Rate Limiting**: Implement API rate limiting with Laravel's built-in features
2. **Versioning**: Add API versioning strategy
3. **Monitoring**: Set up API monitoring and logging
4. **Documentation**: Complete OpenAPI documentation
5. **Testing**: Add comprehensive API integration tests
6. **Deployment**: Set up CI/CD for API deployment

This API-only setup provides a robust foundation for mobile apps, SPAs, or any client that needs to consume your Laravel application's data!