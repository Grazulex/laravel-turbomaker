# Advanced Usage

This guide covers advanced features and patterns for using Laravel TurboMaker effectively in complex projects.

## Complex Relationship Patterns

### Multi-Level Relationships

Build complex relationship hierarchies by generating models in dependency order:

```bash
# E-commerce example with complex relationships
php artisan turbo:make Category --has-many=Product
php artisan turbo:make Brand --has-many=Product
php artisan turbo:make Product --belongs-to=Category --belongs-to=Brand --has-many=OrderItem --has-many=Review
php artisan turbo:make User --has-many=Order --has-many=Review
php artisan turbo:make Order --belongs-to=User --has-many=OrderItem --has-one=Payment
php artisan turbo:make OrderItem --belongs-to=Order --belongs-to=Product
php artisan turbo:make Review --belongs-to=Product --belongs-to=User
php artisan turbo:make Payment --belongs-to=Order
```

### Polymorphic Relationships

While TurboMaker doesn't auto-generate polymorphic relationships, you can set up the base structure and add polymorphic relationships manually:

```bash
# Generate base models
php artisan turbo:make Post --tests --factory
php artisan turbo:make Video --tests --factory
php artisan turbo:make Comment --tests --factory

# Manually add polymorphic relationships after generation
```

**Comment.php (after generation):**
```php
public function commentable()
{
    return $this->morphTo();
}
```

**Post.php (after generation):**
```php
public function comments()
{
    return $this->morphMany(Comment::class, 'commentable');
}
```

## Modular Architecture Patterns

### Domain-Driven Design (DDD)

Structure your modules by domain:

```bash
# User Domain
php artisan turbo:make User --services --actions --policies --tests
php artisan turbo:make Role --belongs-to=User --services --tests
php artisan turbo:make Permission --tests

# Product Domain  
php artisan turbo:make Product --services --actions --observers --tests --factory
php artisan turbo:make Category --has-many=Product --services --tests
php artisan turbo:make Inventory --belongs-to=Product --observers --tests

# Order Domain
php artisan turbo:make Order --belongs-to=User --services --actions --observers --tests
php artisan turbo:make OrderItem --belongs-to=Order --belongs-to=Product --tests
```

### Feature-Based Organization

Organize by features rather than file types:

```bash
# Configure custom paths in config/turbomaker.php
'paths' => [
    'models' => 'app/Features/{Feature}/Models',
    'controllers' => 'app/Features/{Feature}/Http/Controllers',
    'services' => 'app/Features/{Feature}/Services',
    'actions' => 'app/Features/{Feature}/Actions',
],
```

## Advanced Testing Patterns

### Test-Driven Development (TDD)

Generate tests first, then implement:

```bash
# Generate tests first
php artisan turbo:test Product --feature --unit --factory

# Then generate the actual module
php artisan turbo:make Product --api --policies --services
```

### Comprehensive Testing

Generate complete test suites with all components:

```bash
php artisan turbo:make Order \
    --belongs-to=User \
    --has-many=OrderItem \
    --tests \
    --factory \
    --seeder \
    --policies \
    --actions \
    --services \
    --observers
```

This generates tests for:
- Model relationships and validations
- Controller CRUD operations
- Policy authorization
- Service layer methods
- Action class execution
- Observer events

## API Development Patterns

### API-First Development

Build API-first applications:

```bash
# Generate API-only modules
php artisan turbo:api Product --tests --factory --policies
php artisan turbo:api Category --tests --factory
php artisan turbo:api Order --belongs-to=User --has-many=OrderItem --tests --policies

# Add views later if needed
php artisan turbo:view Product
php artisan turbo:view Category
```

### Versioned APIs

Create versioned API endpoints:

```bash
# Configure API versioning in config/turbomaker.php
'api' => [
    'version_prefix' => 'v1',
    'namespace' => 'App\\Http\\Controllers\\Api\\V1',
],

# Generate versioned controllers
php artisan turbo:api Product
```

### API Resources with Relationships

Generate comprehensive API resources:

```php
// ProductResource (generated and enhanced)
class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'average_rating' => $this->when($this->reviews_avg_rating, $this->reviews_avg_rating),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

## Performance Optimization

### Lazy Loading Prevention

Generate models with relationship prevention:

```php
// Generated model with optimizations
class Product extends Model
{
    protected $with = ['category']; // Eager load by default
    
    protected static function boot()
    {
        parent::boot();
        
        // Prevent N+1 queries in development
        if (app()->environment('local')) {
            static::preventLazyLoading();
        }
    }
}
```

### Database Optimization

Generate optimized database structures:

```bash
# Generate with database optimizations
php artisan turbo:make Product --factory --seeder

# Add database indexes manually after generation
```

**Migration (enhanced after generation):**
```php
public function up()
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description');
        $table->decimal('price', 10, 2);
        $table->foreignId('category_id')->constrained()->cascadeOnDelete();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        
        // Add indexes for performance
        $table->index(['is_active', 'created_at']);
        $table->index(['category_id', 'is_active']);
        $table->fullText(['name', 'description']);
    });
}
```

## Batch Operations

### Generate Multiple Related Modules

Use scripts to generate related modules:

**generate-ecommerce.sh:**
```bash
#!/bin/bash

# User management
php artisan turbo:make User --has-many=Order --has-many=Review --policies --tests --factory
php artisan turbo:make Role --tests --factory
php artisan turbo:make Permission --tests

# Product catalog
php artisan turbo:make Category --has-many=Product --tests --factory --seeder
php artisan turbo:make Brand --has-many=Product --tests --factory
php artisan turbo:make Product --belongs-to=Category --belongs-to=Brand --has-many=OrderItem --has-many=Review --tests --factory --seeder --policies --observers

# Order management
php artisan turbo:make Order --belongs-to=User --has-many=OrderItem --has-one=Payment --tests --factory --policies --services --actions --observers
php artisan turbo:make OrderItem --belongs-to=Order --belongs-to=Product --tests --factory
php artisan turbo:make Payment --belongs-to=Order --tests --factory --observers

# Reviews and ratings
php artisan turbo:make Review --belongs-to=Product --belongs-to=User --tests --factory --policies

echo "E-commerce modules generated successfully!"
```

### Bulk Testing

Generate comprehensive test suites:

**generate-tests.sh:**
```bash
#!/bin/bash

# List of existing models
MODELS=("User" "Product" "Category" "Order" "OrderItem" "Payment" "Review")

for model in "${MODELS[@]}"; do
    echo "Generating tests for $model..."
    php artisan turbo:test "$model" --feature --unit --force
done

echo "All tests generated!"
```

## Custom Workflows

### CI/CD Integration

Integrate TurboMaker into your CI/CD pipeline:

**generate-module.yml:**
```yaml
name: Generate Module
on:
  workflow_dispatch:
    inputs:
      module_name:
        description: 'Module name to generate'
        required: true
      include_tests:
        description: 'Include tests'
        type: boolean
        default: true

jobs:
  generate:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
          
      - name: Install dependencies
        run: composer install
        
      - name: Generate module
        run: |
          MODULE_NAME="${{ github.event.inputs.module_name }}"
          INCLUDE_TESTS="${{ github.event.inputs.include_tests }}"
          
          if [ "$INCLUDE_TESTS" = "true" ]; then
            php artisan turbo:make "$MODULE_NAME" --tests --factory --policies
          else
            php artisan turbo:make "$MODULE_NAME" --factory --policies
          fi
          
      - name: Commit generated files
        run: |
          git config --local user.email "action@github.com"
          git config --local user.name "GitHub Action"
          git add .
          git commit -m "Generate ${{ github.event.inputs.module_name }} module" || exit 0
          git push
```

### Pre-commit Hooks

Ensure code quality with pre-commit hooks:

**.pre-commit-config.yaml:**
```yaml
repos:
  - repo: local
    hooks:
      - id: turbomaker-validation
        name: Validate TurboMaker Generated Code
        entry: ./scripts/validate-generated-code.sh
        language: script
        pass_filenames: false
```

**scripts/validate-generated-code.sh:**
```bash
#!/bin/bash

# Run code style checks on generated files
./vendor/bin/pint --test

# Run static analysis
./vendor/bin/phpstan analyse

# Run tests
./vendor/bin/pest --parallel

echo "All generated code validation passed!"
```

## Integration Patterns

### Event-Driven Architecture

Generate modules with event support:

```bash
# Generate modules with observers
php artisan turbo:make Order --observers --tests --actions --services
php artisan turbo:make Product --observers --tests
```

**OrderObserver (enhanced after generation):**
```php
class OrderObserver
{
    public function created(Order $order)
    {
        // Dispatch events
        OrderCreated::dispatch($order);
        
        // Send notifications
        $order->user->notify(new OrderConfirmation($order));
        
        // Update inventory
        UpdateInventoryJob::dispatch($order);
    }
    
    public function updated(Order $order)
    {
        if ($order->wasChanged('status')) {
            OrderStatusChanged::dispatch($order, $order->getOriginal('status'));
        }
    }
}
```

### Queue Integration

Generate modules with queue support:

```bash
php artisan turbo:make EmailNotification --services --actions --tests
```

**EmailNotificationService (enhanced after generation):**
```php
class EmailNotificationService
{
    public function sendWelcomeEmail(User $user)
    {
        SendWelcomeEmailJob::dispatch($user);
    }
    
    public function sendOrderConfirmation(Order $order)
    {
        SendOrderConfirmationJob::dispatch($order);
    }
}
```

## Debugging and Troubleshooting

### Verbose Mode

Enable verbose output for debugging:

```bash
php artisan turbo:make Product --tests --factory -vvv
```

### Dry Run Mode

Preview what would be generated without creating files:

```bash
# Custom script to preview generation
php artisan turbo:make Product --dry-run --tests --factory
```

### File Conflict Resolution

Handle file conflicts gracefully:

```bash
# Always overwrite (use with caution)
php artisan turbo:make Product --force

# Backup existing files before generation
cp -r app/Models/Product.php app/Models/Product.php.bak
php artisan turbo:make Product --force
```

## Best Practices for Large Projects

### 1. Establish Conventions Early
- Define naming conventions
- Set up coding standards
- Configure TurboMaker defaults

### 2. Use Consistent Patterns
- Always include tests and factories
- Use services for business logic
- Implement policies for authorization

### 3. Modular Development
- Generate related modules together
- Use scripts for batch generation
- Organize by business domains

### 4. Performance Considerations
- Include database indexes
- Prevent N+1 queries
- Use appropriate caching strategies

### 5. Team Collaboration
- Document generation patterns
- Use version control for configurations
- Establish code review processes

## Examples

See the [examples directory](../examples/) for complete implementations of:
- [Complex E-commerce System](../examples/ecommerce-advanced/) - Multi-level relationships and advanced patterns
- [API-First Application](../examples/api-first/) - Comprehensive API development
- [DDD Implementation](../examples/domain-driven/) - Domain-driven design patterns
- [Performance Optimized](../examples/performance/) - Optimized for high-traffic applications