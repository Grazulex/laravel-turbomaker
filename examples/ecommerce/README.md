# E-commerce Platform Example

This example demonstrates building a complete e-commerce platform using Laravel TurboMaker, showcasing complex multi-level relationships and advanced patterns.

## Overview

A comprehensive e-commerce system featuring:
- **Products** with categories and brands
- **Shopping cart** and order management
- **User accounts** with order history
- **Inventory tracking** with stock management
- **Payment processing** integration
- **Reviews and ratings** system

## System Architecture

```
Users -----> Orders -----> OrderItems -----> Products
  |            |                               |
  |            v                               v
  |         Payments                       Categories
  |                                           |
  |---------> Reviews ------------------------|
                |                             
                v                             
            Products <-----> Brands
                |
                v
           Inventory
```

## Entity Relationships

- **User** `hasMany` Orders, Reviews
- **Category** `hasMany` Products
- **Brand** `hasMany` Products  
- **Product** `belongsTo` Category, Brand; `hasMany` OrderItems, Reviews; `hasOne` Inventory
- **Order** `belongsTo` User; `hasMany` OrderItems; `hasOne` Payment
- **OrderItem** `belongsTo` Order, Product
- **Payment** `belongsTo` Order
- **Review** `belongsTo` Product, User
- **Inventory** `belongsTo` Product

## Generation Commands

Execute these commands to build the complete e-commerce platform:

### 1. Core Entities

```bash
# Users (authentication and profiles)
php artisan turbo:make User \
    --has-many=Order \
    --has-many=Review \
    --policies \
    --tests \
    --factory

# Categories for product organization
php artisan turbo:make Category \
    --has-many=Product \
    --tests \
    --factory \
    --seeder \
    --views

# Brands for product attribution
php artisan turbo:make Brand \
    --has-many=Product \
    --tests \
    --factory \
    --seeder \
    --views
```

### 2. Product Management

```bash
# Products with complex relationships
php artisan turbo:make Product \
    --belongs-to=Category \
    --belongs-to=Brand \
    --has-many=OrderItem \
    --has-many=Review \
    --has-one=Inventory \
    --policies \
    --tests \
    --factory \
    --seeder \
    --views \
    --services \
    --observers

# Inventory tracking
php artisan turbo:make Inventory \
    --belongs-to=Product \
    --tests \
    --factory \
    --services \
    --observers
```

### 3. Order Management System

```bash
# Orders with comprehensive features
php artisan turbo:make Order \
    --belongs-to=User \
    --has-many=OrderItem \
    --has-one=Payment \
    --policies \
    --tests \
    --factory \
    --views \
    --services \
    --actions \
    --observers

# Order line items
php artisan turbo:make OrderItem \
    --belongs-to=Order \
    --belongs-to=Product \
    --tests \
    --factory \
    --services

# Payment processing
php artisan turbo:make Payment \
    --belongs-to=Order \
    --policies \
    --tests \
    --factory \
    --services \
    --observers
```

### 4. Customer Feedback

```bash
# Product reviews and ratings
php artisan turbo:make Review \
    --belongs-to=Product \
    --belongs-to=User \
    --policies \
    --tests \
    --factory \
    --views
```

### 5. Additional Features (Optional)

```bash
# Shopping cart (session-based)
php artisan turbo:make Cart \
    --belongs-to=User \
    --has-many=CartItem \
    --services \
    --tests

# Cart items
php artisan turbo:make CartItem \
    --belongs-to=Cart \
    --belongs-to=Product \
    --tests \
    --factory

# Coupons and discounts
php artisan turbo:make Coupon \
    --tests \
    --factory \
    --services \
    --policies

# Shipping addresses
php artisan turbo:make Address \
    --belongs-to=User \
    --tests \
    --factory \
    --views
```

## Generated File Structure

```
app/
├── Models/
│   ├── User.php
│   ├── Category.php
│   ├── Brand.php
│   ├── Product.php
│   ├── Inventory.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Payment.php
│   ├── Review.php
│   ├── Cart.php
│   ├── CartItem.php
│   ├── Coupon.php
│   └── Address.php
├── Http/
│   ├── Controllers/
│   │   ├── CategoryController.php
│   │   ├── BrandController.php
│   │   ├── ProductController.php
│   │   ├── OrderController.php
│   │   ├── ReviewController.php
│   │   └── ...
│   ├── Controllers/Api/
│   │   ├── ProductController.php
│   │   ├── OrderController.php
│   │   ├── CartController.php
│   │   └── ...
│   ├── Requests/
│   ├── Resources/
│   └── ...
├── Services/
│   ├── ProductService.php
│   ├── OrderService.php
│   ├── PaymentService.php
│   ├── InventoryService.php
│   └── CartService.php
├── Actions/
│   ├── CreateOrderAction.php
│   ├── UpdateOrderAction.php
│   ├── ProcessPaymentAction.php
│   └── ...
├── Observers/
│   ├── ProductObserver.php
│   ├── OrderObserver.php
│   ├── PaymentObserver.php
│   └── InventoryObserver.php
└── Policies/
    ├── ProductPolicy.php
    ├── OrderPolicy.php
    ├── PaymentPolicy.php
    └── ReviewPolicy.php
```

## Key Implementation Examples

### Product Model with Complex Relationships

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'sku',
        'category_id',
        'brand_id',
        'is_active',
        'featured_image',
        'gallery_images',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'gallery_images' => 'array',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    // Computed attributes
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating');
    }

    public function getReviewCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function getInStockAttribute()
    {
        return $this->inventory && $this->inventory->quantity > 0;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->whereHas('inventory', function ($q) {
            $q->where('quantity', '>', 0);
        });
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }
}
```

### Order Service with Business Logic

```php
<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Actions\CreateOrderAction;
use App\Actions\UpdateInventoryAction;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private CreateOrderAction $createOrderAction,
        private UpdateInventoryAction $updateInventoryAction
    ) {}

    public function createOrder(array $orderData, array $items): Order
    {
        return DB::transaction(function () use ($orderData, $items) {
            // Create the order
            $order = $this->createOrderAction->execute($orderData);

            $totalAmount = 0;

            // Process each item
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Check inventory
                if (!$this->checkInventory($product, $item['quantity'])) {
                    throw new \Exception("Insufficient inventory for {$product->name}");
                }

                // Create order item
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'total' => $product->price * $item['quantity'],
                ]);

                $totalAmount += $orderItem->total;

                // Update inventory
                $this->updateInventoryAction->execute($product, -$item['quantity']);
            }

            // Update order total
            $order->update(['total_amount' => $totalAmount]);

            return $order->load(['orderItems.product', 'user']);
        });
    }

    public function processPayment(Order $order, array $paymentData): bool
    {
        // Integration with payment processor
        // This would typically call Stripe, PayPal, etc.
        
        $order->update(['status' => 'processing']);
        
        return true;
    }

    public function cancelOrder(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            // Restore inventory
            foreach ($order->orderItems as $item) {
                $this->updateInventoryAction->execute(
                    $item->product, 
                    $item->quantity
                );
            }

            // Update order status
            $order->update(['status' => 'cancelled']);

            return true;
        });
    }

    private function checkInventory(Product $product, int $quantity): bool
    {
        return $product->inventory && $product->inventory->quantity >= $quantity;
    }
}
```

### Advanced Product Controller

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'inventory'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        // Apply filters
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('brand')) {
            $query->byBrand($request->brand);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->priceRange(
                $request->get('min_price', 0),
                $request->get('max_price', 999999)
            );
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $products = $query->active()->paginate(12);
        
        $categories = Category::all();
        $brands = Brand::all();

        return view('products.index', compact('products', 'categories', 'brands'));
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'brand',
            'inventory',
            'reviews.user',
        ]);

        // Related products
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->inStock()
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    // Admin methods would include full CRUD operations
    // These would be protected by policies and middleware
}
```

### Comprehensive Testing Example

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Inventory;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
        $this->brand = Brand::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'price' => 99.99,
        ]);
        
        Inventory::factory()->create([
            'product_id' => $this->product->id,
            'quantity' => 100,
        ]);
    }

    public function test_complete_order_workflow()
    {
        $this->actingAs($this->user);

        // 1. Browse products
        $response = $this->get('/products');
        $response->assertStatus(200)
                ->assertSee($this->product->name);

        // 2. View product details
        $response = $this->get("/products/{$this->product->id}");
        $response->assertStatus(200)
                ->assertSee($this->product->name)
                ->assertSee($this->product->price);

        // 3. Create order via service
        $orderService = app(OrderService::class);
        
        $order = $orderService->createOrder([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ], [
            [
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]
        ]);

        // 4. Verify order creation
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(199.98, $order->total_amount); // 2 * 99.99
        $this->assertCount(1, $order->orderItems);

        // 5. Verify inventory update
        $this->product->inventory->refresh();
        $this->assertEquals(98, $this->product->inventory->quantity);

        // 6. Process payment
        $paymentSuccess = $orderService->processPayment($order, [
            'method' => 'stripe',
            'token' => 'tok_visa',
        ]);

        $this->assertTrue($paymentSuccess);
        $order->refresh();
        $this->assertEquals('processing', $order->status);
    }

    public function test_order_cancellation_restores_inventory()
    {
        $this->actingAs($this->user);

        $orderService = app(OrderService::class);
        
        // Create order
        $order = $orderService->createOrder([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ], [
            [
                'product_id' => $this->product->id,
                'quantity' => 5,
            ]
        ]);

        // Verify inventory decreased
        $this->product->inventory->refresh();
        $this->assertEquals(95, $this->product->inventory->quantity);

        // Cancel order
        $cancelled = $orderService->cancelOrder($order);

        // Verify cancellation and inventory restoration
        $this->assertTrue($cancelled);
        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        
        $this->product->inventory->refresh();
        $this->assertEquals(100, $this->product->inventory->quantity);
    }

    public function test_insufficient_inventory_prevents_order()
    {
        $this->actingAs($this->user);

        // Set low inventory
        $this->product->inventory->update(['quantity' => 1]);

        $orderService = app(OrderService::class);

        // Attempt to order more than available
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient inventory');

        $orderService->createOrder([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ], [
            [
                'product_id' => $this->product->id,
                'quantity' => 5, // More than available
            ]
        ]);
    }
}
```

## Database Setup and Seeding

### Run Migrations and Seeders

```bash
# Run all migrations
php artisan migrate

# Seed data for development
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=BrandSeeder
php artisan db:seed --class=ProductSeeder

# Or run all seeders
php artisan db:seed
```

### Sample Database Seeder

```php
<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class EcommerceSeeder extends Seeder
{
    public function run()
    {
        // Create categories
        $electronics = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        $clothing = Category::create(['name' => 'Clothing', 'slug' => 'clothing']);
        $books = Category::create(['name' => 'Books', 'slug' => 'books']);

        // Create brands
        $apple = Brand::create(['name' => 'Apple', 'slug' => 'apple']);
        $samsung = Brand::create(['name' => 'Samsung', 'slug' => 'samsung']);
        $nike = Brand::create(['name' => 'Nike', 'slug' => 'nike']);

        // Create products with inventory
        $products = [
            [
                'name' => 'iPhone 13',
                'price' => 999.00,
                'category_id' => $electronics->id,
                'brand_id' => $apple->id,
                'inventory' => 50,
            ],
            [
                'name' => 'Samsung Galaxy S21',
                'price' => 799.00,
                'category_id' => $electronics->id,
                'brand_id' => $samsung->id,
                'inventory' => 30,
            ],
            [
                'name' => 'Nike Air Max',
                'price' => 120.00,
                'category_id' => $clothing->id,
                'brand_id' => $nike->id,
                'inventory' => 100,
            ],
        ];

        foreach ($products as $productData) {
            $inventory = $productData['inventory'];
            unset($productData['inventory']);

            $product = Product::create($productData);
            
            Inventory::create([
                'product_id' => $product->id,
                'quantity' => $inventory,
                'reserved_quantity' => 0,
            ]);
        }
    }
}
```

## Testing the Complete System

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test tests/Feature/EcommerceWorkflowTest.php
php artisan test tests/Unit/ProductServiceTest.php

# Run with coverage
php artisan test --coverage
```

## Performance Optimizations

### Database Indexes

Add these to your migrations for better performance:

```php
// In create_products_table migration
$table->index(['category_id', 'is_active']);
$table->index(['brand_id', 'is_active']);
$table->index(['price', 'is_active']);
$table->fullText(['name', 'description']);

// In create_orders_table migration
$table->index(['user_id', 'status']);
$table->index(['created_at', 'status']);

// In create_order_items_table migration
$table->index(['order_id', 'product_id']);
```

### Eager Loading

Prevent N+1 queries in controllers:

```php
// In ProductController
$products = Product::with([
    'category:id,name,slug',
    'brand:id,name,slug',
    'inventory:id,product_id,quantity'
])
->withCount('reviews')
->withAvg('reviews', 'rating')
->paginate();
```

### Caching

Implement caching for frequently accessed data:

```php
// Cache popular products
$popularProducts = Cache::remember('popular_products', 3600, function () {
    return Product::with(['category', 'brand'])
        ->withCount('orderItems')
        ->orderBy('order_items_count', 'desc')
        ->limit(10)
        ->get();
});
```

## Next Steps

1. **Frontend Integration**: Add Vue.js or React for dynamic user experience
2. **Payment Gateway**: Integrate Stripe, PayPal, or other payment processors
3. **Search**: Implement Elasticsearch or Laravel Scout for advanced search
4. **Admin Panel**: Build comprehensive admin interface
5. **API**: Expose API endpoints for mobile app integration
6. **Analytics**: Add tracking for sales, inventory, and user behavior
7. **Notifications**: Email and SMS notifications for order updates
8. **Shipping**: Integration with shipping providers

This e-commerce example provides a robust foundation that can be extended for real-world applications!