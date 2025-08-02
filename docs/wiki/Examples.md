# Real-World Examples

Complete examples for common Laravel application patterns.

## Blog Platform

A complete blog system with posts, comments, categories, and tags.

### 1. Schema Files

```yaml
# resources/schemas/post.schema.yml
name: "Post"
table_name: "posts"

fields:
  title:
    type: string
    length: 255
    nullable: false
    index: true
    validation: ["min:3", "max:255"]
    
  slug:
    type: string
    length: 255
    nullable: false
    unique: true
    validation: ["regex:/^[a-z0-9-]+$/"]
    factory: ["slug()"]
    
  excerpt:
    type: text
    nullable: true
    validation: ["max:500"]
    
  content:
    type: longText
    nullable: false
    validation: ["min:10"]
    
  featured_image:
    type: string
    nullable: true
    validation: ["url"]
    
  published:
    type: boolean
    nullable: false
    default: false
    
  published_at:
    type: timestamp
    nullable: true
    
  views_count:
    type: unsignedBigInteger
    nullable: false
    default: 0
    
  meta_description:
    type: string
    length: 160
    nullable: true
    validation: ["max:160"]
    
  author_id:
    type: unsignedBigInteger
    nullable: false
    index: true

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
```

```yaml
# resources/schemas/comment.schema.yml
name: "Comment"
table_name: "comments"

fields:
  content:
    type: text
    nullable: false
    validation: ["min:3", "max:1000"]
    
  approved:
    type: boolean
    nullable: false
    default: false
    
  user_id:
    type: unsignedBigInteger
    nullable: false
    index: true
    
  post_id:
    type: unsignedBigInteger
    nullable: false
    index: true
    
  parent_id:
    type: unsignedBigInteger
    nullable: true
    index: true
    comment: "For nested comments"

relationships:
  user:
    type: belongsTo
    model: "User"
    
  post:
    type: belongsTo
    model: "Post"
    
  parent:
    type: belongsTo
    model: "Comment"
    foreign_key: "parent_id"
    
  replies:
    type: hasMany
    model: "Comment"
    foreign_key: "parent_id"
```

```yaml
# resources/schemas/category.schema.yml
name: "Category"
table_name: "categories"

fields:
  name:
    type: string
    length: 100
    nullable: false
    unique: true
    index: true
    validation: ["min:2", "max:100"]
    
  slug:
    type: string
    length: 100
    nullable: false
    unique: true
    factory: ["slug()"]
    
  description:
    type: text
    nullable: true
    validation: ["max:500"]
    
  color:
    type: string
    length: 7
    nullable: true
    validation: ["regex:/^#[0-9A-Fa-f]{6}$/"]
    comment: "Hex color code"
    
  parent_id:
    type: unsignedBigInteger
    nullable: true
    index: true

relationships:
  parent:
    type: belongsTo
    model: "Category"
    foreign_key: "parent_id"
    
  children:
    type: hasMany
    model: "Category"
    foreign_key: "parent_id"
    
  posts:
    type: hasMany
    model: "Post"
```

### 2. Generation Commands

```bash
# Generate core models
php artisan turbo:make User --tests --factory --policies
php artisan turbo:make Post --schema=post --tests --factory --policies
php artisan turbo:make Comment --schema=comment --tests --factory
php artisan turbo:make Category --schema=category --tests --factory
php artisan turbo:make Tag --fields="name:string:unique,slug:string:unique" --tests --factory

# Generate additional components
php artisan turbo:make PostView --fields="ip_address:string,user_agent:text" --belongs-to=Post
php artisan turbo:make Newsletter --fields="email:email:unique,subscribed_at:timestamp" --tests
```

### 3. Generated Structure

```
app/
├── Models/
│   ├── User.php
│   ├── Post.php
│   ├── Comment.php
│   ├── Category.php
│   └── Tag.php
├── Http/
│   ├── Controllers/
│   │   ├── PostController.php
│   │   ├── CommentController.php
│   │   └── CategoryController.php
│   ├── Controllers/Api/
│   │   ├── PostController.php
│   │   ├── CommentController.php
│   │   └── CategoryController.php
│   ├── Requests/
│   │   ├── StorePostRequest.php
│   │   ├── UpdatePostRequest.php
│   │   └── ...
│   └── Resources/
│       ├── PostResource.php
│       ├── CommentResource.php
│       └── ...
└── Policies/
    ├── PostPolicy.php
    └── UserPolicy.php
```

## E-commerce Platform

Complete e-commerce system with products, orders, and inventory management.

### 1. Product Schema

```yaml
# resources/schemas/product.schema.yml
name: "Product"
table_name: "products"

fields:
  name:
    type: string
    length: 255
    nullable: false
    index: true
    validation: ["min:3", "max:255"]
    
  slug:
    type: string
    length: 255
    nullable: false
    unique: true
    
  sku:
    type: string
    length: 100
    nullable: false
    unique: true
    comment: "Stock Keeping Unit"
    
  description:
    type: longText
    nullable: true
    
  short_description:
    type: text
    nullable: true
    validation: ["max:500"]
    
  price:
    type: decimal
    nullable: false
    attributes:
      precision: 10
      scale: 2
    validation: ["numeric", "min:0"]
    
  sale_price:
    type: decimal
    nullable: true
    attributes:
      precision: 10
      scale: 2
    validation: ["numeric", "min:0"]
    
  cost_price:
    type: decimal
    nullable: true
    attributes:
      precision: 10
      scale: 2
    validation: ["numeric", "min:0"]
    
  stock_quantity:
    type: integer
    nullable: false
    default: 0
    validation: ["integer", "min:0"]
    
  weight:
    type: decimal
    nullable: true
    attributes:
      precision: 8
      scale: 3
    validation: ["numeric", "min:0"]
    
  dimensions:
    type: json
    nullable: true
    comment: "Length, width, height"
    
  is_active:
    type: boolean
    nullable: false
    default: true
    
  is_featured:
    type: boolean
    nullable: false
    default: false
    
  meta_title:
    type: string
    length: 60
    nullable: true
    
  meta_description:
    type: string
    length: 160
    nullable: true
    
  category_id:
    type: unsignedBigInteger
    nullable: false
    index: true
    
  brand_id:
    type: unsignedBigInteger
    nullable: true
    index: true

relationships:
  category:
    type: belongsTo
    model: "Category"
    
  brand:
    type: belongsTo
    model: "Brand"
    
  orders:
    type: belongsToMany
    model: "Order"
    pivot_table: "order_items"
    pivot_fields: ["quantity", "price", "total"]
    
  reviews:
    type: hasMany
    model: "Review"
    
  images:
    type: hasMany
    model: "ProductImage"
```

### 2. Order Schema

```yaml
# resources/schemas/order.schema.yml
name: "Order"
table_name: "orders"

fields:
  order_number:
    type: string
    length: 20
    nullable: false
    unique: true
    
  status:
    type: string
    length: 50
    nullable: false
    default: "pending"
    validation: ["in:pending,processing,shipped,delivered,cancelled"]
    
  subtotal:
    type: decimal
    nullable: false
    attributes:
      precision: 10
      scale: 2
    validation: ["numeric", "min:0"]
    
  tax_amount:
    type: decimal
    nullable: false
    default: 0
    attributes:
      precision: 10
      scale: 2
    validation: ["numeric", "min:0"]
    
  shipping_amount:
    type: decimal
    nullable: false
    default: 0
    attributes:
      precision: 10
      scale: 2
    validation: ["numeric", "min:0"]
    
  discount_amount:
    type: decimal
    nullable: false
    default: 0
    attributes:
      precision: 10
      scale: 2
    validation: ["numeric", "min:0"]
    
  total:
    type: decimal
    nullable: false
    attributes:
      precision: 10
      scale: 2
    validation: ["numeric", "min:0"]
    
  currency:
    type: string
    length: 3
    nullable: false
    default: "USD"
    
  payment_status:
    type: string
    length: 50
    nullable: false
    default: "pending"
    validation: ["in:pending,paid,failed,refunded"]
    
  payment_method:
    type: string
    length: 50
    nullable: true
    
  shipping_address:
    type: json
    nullable: false
    
  billing_address:
    type: json
    nullable: false
    
  notes:
    type: text
    nullable: true
    
  shipped_at:
    type: timestamp
    nullable: true
    
  delivered_at:
    type: timestamp
    nullable: true
    
  user_id:
    type: unsignedBigInteger
    nullable: false
    index: true

relationships:
  user:
    type: belongsTo
    model: "User"
    
  products:
    type: belongsToMany
    model: "Product"
    pivot_table: "order_items"
    pivot_fields: ["quantity", "price", "total"]
    
  payments:
    type: hasMany
    model: "Payment"
```

### 3. Generation Commands

```bash
# Core e-commerce models
php artisan turbo:make Product --schema=product --tests --factory --policies
php artisan turbo:make Order --schema=order --tests --factory --policies
php artisan turbo:make Category --fields="name:string:unique,slug:string:unique,description:text" --tests --factory

# Supporting models
php artisan turbo:make Brand --fields="name:string:unique,logo:string" --tests --factory
php artisan turbo:make Review --fields="rating:integer,title:string,content:text" --belongs-to=Product,User --tests
php artisan turbo:make ProductImage --fields="url:string,alt_text:string,sort_order:integer" --belongs-to=Product --tests

# Inventory and shipping
php artisan turbo:make InventoryLog --fields="type:string,quantity:integer,reason:string" --belongs-to=Product --tests
php artisan turbo:make ShippingMethod --fields="name:string,price:decimal,estimated_days:integer" --tests

# API-only models for admin
php artisan turbo:api Payment --fields="amount:decimal,status:string,transaction_id:string" --belongs-to=Order --tests
php artisan turbo:api Coupon --fields="code:string:unique,type:string,value:decimal,expires_at:timestamp" --tests
```

## Multi-tenant SaaS

Multi-tenant SaaS application with tenant isolation.

### 1. Tenant Schema

```yaml
# resources/schemas/tenant.schema.yml
name: "Tenant"
table_name: "tenants"

fields:
  name:
    type: string
    length: 255
    nullable: false
    validation: ["min:2", "max:255"]
    
  slug:
    type: string
    length: 100
    nullable: false
    unique: true
    validation: ["regex:/^[a-z0-9-]+$/"]
    
  domain:
    type: string
    length: 255
    nullable: true
    unique: true
    validation: ["regex:/^[a-z0-9.-]+$/"]
    
  database_name:
    type: string
    length: 100
    nullable: true
    
  is_active:
    type: boolean
    nullable: false
    default: true
    
  plan:
    type: string
    length: 50
    nullable: false
    default: "basic"
    validation: ["in:basic,pro,enterprise"]
    
  max_users:
    type: integer
    nullable: false
    default: 10
    
  max_projects:
    type: integer
    nullable: false
    default: 5
    
  settings:
    type: json
    nullable: true
    
  trial_ends_at:
    type: timestamp
    nullable: true
    
  subscription_ends_at:
    type: timestamp
    nullable: true

relationships:
  users:
    type: hasMany
    model: "User"
    
  projects:
    type: hasMany
    model: "Project"
    
  subscriptions:
    type: hasMany
    model: "Subscription"
```

### 2. Tenant-aware Models

```yaml
# resources/schemas/project.schema.yml
name: "Project"
table_name: "projects"

fields:
  name:
    type: string
    length: 255
    nullable: false
    validation: ["min:2", "max:255"]
    
  description:
    type: text
    nullable: true
    
  status:
    type: string
    length: 50
    nullable: false
    default: "active"
    validation: ["in:active,completed,archived"]
    
  start_date:
    type: date
    nullable: true
    
  end_date:
    type: date
    nullable: true
    validation: ["after:start_date"]
    
  budget:
    type: decimal
    nullable: true
    attributes:
      precision: 10
      scale: 2
    validation: ["numeric", "min:0"]
    
  settings:
    type: json
    nullable: true
    
  tenant_id:
    type: unsignedBigInteger
    nullable: false
    index: true
    
  owner_id:
    type: unsignedBigInteger
    nullable: false
    index: true

relationships:
  tenant:
    type: belongsTo
    model: "Tenant"
    
  owner:
    type: belongsTo
    model: "User"
    foreign_key: "owner_id"
    
  members:
    type: belongsToMany
    model: "User"
    pivot_table: "project_members"
    pivot_fields: ["role", "joined_at"]
    
  tasks:
    type: hasMany
    model: "Task"
```

### 3. Generation Commands

```bash
# Core tenant models
php artisan turbo:make Tenant --schema=tenant --tests --factory --policies
php artisan turbo:make User --fields="name:string,email:email:unique,role:string" --belongs-to=Tenant --tests --factory --policies

# Tenant-aware business models
php artisan turbo:make Project --schema=project --tests --factory --policies
php artisan turbo:make Task --fields="title:string,description:text,status:string,due_date:date" --belongs-to=Project,User --tests
php artisan turbo:make Document --fields="name:string,file_path:string,size:integer" --belongs-to=Project,User --tests

# Subscription and billing
php artisan turbo:api Subscription --fields="plan:string,status:string,starts_at:timestamp,ends_at:timestamp" --belongs-to=Tenant --tests
php artisan turbo:api Invoice --fields="amount:decimal,status:string,due_date:date" --belongs-to=Tenant --tests

# Usage tracking
php artisan turbo:api UsageMetric --fields="metric:string,value:integer,recorded_at:timestamp" --belongs-to=Tenant --tests
```

## REST API with Authentication

Complete API-only application with authentication and authorization.

### 1. Generation Commands

```bash
# Authentication models
php artisan turbo:api User --fields="name:string,email:email:unique,email_verified_at:timestamp" --tests --policies
php artisan turbo:api PersonalAccessToken --fields="name:string,token:string:unique,abilities:json,expires_at:timestamp" --belongs-to=User --tests

# Core business models
php artisan turbo:api Company --fields="name:string,slug:string:unique,description:text,website:url" --tests --policies
php artisan turbo:api Project --fields="name:string,description:text,status:string" --belongs-to=Company --tests --policies
php artisan turbo:api Task --fields="title:string,description:text,status:string,due_date:date" --belongs-to=Project,User --tests --policies

# Collaboration features
php artisan turbo:api Comment --fields="content:text" --belongs-to=Task,User --tests
php artisan turbo:api Attachment --fields="name:string,file_path:string,file_size:integer,mime_type:string" --belongs-to=Task,User --tests

# Audit and logging
php artisan turbo:api AuditLog --fields="action:string,model_type:string,model_id:unsignedBigInteger,changes:json" --belongs-to=User --tests
php artisan turbo:api ApiLog --fields="method:string,url:string,status_code:integer,response_time:integer" --belongs-to=User --tests
```

### 2. Generated API Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── UserController.php
│   │   ├── CompanyController.php
│   │   ├── ProjectController.php
│   │   └── TaskController.php
│   ├── Requests/
│   │   ├── StoreUserRequest.php
│   │   ├── UpdateUserRequest.php
│   │   └── ...
│   └── Resources/
│       ├── UserResource.php
│       ├── CompanyResource.php
│       └── ...
├── Models/
│   ├── User.php
│   ├── Company.php
│   ├── Project.php
│   └── Task.php
└── Policies/
    ├── UserPolicy.php
    ├── CompanyPolicy.php
    └── ProjectPolicy.php
```

## Performance Considerations

### Batch Generation

```bash
#!/bin/bash
# generate-models.sh

# Core models
models=(
  "User:user"
  "Company:company" 
  "Project:project"
  "Task:task"
)

for model_schema in "${models[@]}"; do
  IFS=':' read -r model schema <<< "$model_schema"
  php artisan turbo:make "$model" --schema="$schema" --tests --factory --policies
done

echo "✅ All models generated successfully!"
```

### Memory Optimization

```bash
# For large projects, increase memory limit
export TURBOMAKER_MEMORY_LIMIT=1024M

# Generate models one by one to avoid memory issues
php artisan turbo:make Product --schema=product --tests --factory
php artisan turbo:make Order --schema=order --tests --factory
```

### Caching

```bash
# Clear caches before large generations
php artisan turbo:schema clear-cache
php artisan config:clear

# Generate with caching disabled for development
TURBOMAKER_CACHE_ENABLED=false php artisan turbo:make Product --schema=product
```

These examples show how Laravel TurboMaker can handle real-world application complexity while maintaining clean, maintainable code generation patterns.
