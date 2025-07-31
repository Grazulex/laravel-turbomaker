# Laravel TurboMaker Examples

This directory contains practical examples demonstrating different use cases and patterns for Laravel TurboMaker. Each example showcases real-world scenarios with complete command sequences and generated code samples.

## Available Examples

### 1. [Blog System](blog-system/)
A complete blog system showcasing:
- User authentication and relationships
- Post and comment management with policies
- Category organization
- Comprehensive CRUD operations with views
- Complete testing suite

**Commands used:**
```bash
php artisan turbo:make User --has-many=Post --has-many=Comment --policies --tests --factory
php artisan turbo:make Category --has-many=Post --tests --factory --seeder
php artisan turbo:make Post --belongs-to=User --belongs-to=Category --has-many=Comment --policies --tests --factory --seeder --views
php artisan turbo:make Comment --belongs-to=Post --belongs-to=User --policies --tests --factory
```

**Features demonstrated:**
- Model relationships (belongsTo, hasMany)
- Authorization with policies
- Complete test coverage
- Database factories and seeders
- CRUD views

### 2. [API-Only Application](api-only/)
REST API development example featuring:
- API-first architecture with no views
- JSON resources and collections
- Authentication and authorization
- Comprehensive API testing
- Request validation

**Commands used:**
```bash
php artisan turbo:api Product --tests --factory --policies
php artisan turbo:api Category --tests --factory
php artisan turbo:api Order --belongs-to=User --has-many=OrderItem --tests --policies
php artisan turbo:api Review --belongs-to=Product --belongs-to=User --tests
```

**Features demonstrated:**
- API-only module generation
- API resources for JSON responses
- Request validation classes
- Policy-based authorization
- Feature testing for APIs

### 3. [E-commerce System](ecommerce/)
Advanced e-commerce platform demonstrating:
- Complex relationship hierarchies
- Product catalog management
- Order processing workflow
- Inventory tracking
- Multi-level authorization

**Commands used:**
```bash
php artisan turbo:make Product --belongs-to=Category --belongs-to=Brand --has-many=OrderItem --policies --tests --factory --observers
php artisan turbo:make Order --belongs-to=User --has-many=OrderItem --has-one=Payment --services --actions --observers --tests
php artisan turbo:make OrderItem --belongs-to=Order --belongs-to=Product --tests --factory
php artisan turbo:make Payment --belongs-to=Order --policies --tests --observers
```

**Features demonstrated:**
- Complex multi-model relationships
- Service layer architecture with actions
- Model observers for events
- Business logic separation
- Advanced authorization patterns

### 4. [Configuration Examples](configuration/)
Different configuration setups for:
- Team development standards
- Environment-specific settings
- Custom file paths and namespaces
- Performance optimizations
- Custom stub templates

**Features demonstrated:**
- Environment-based configuration
- Team collaboration settings
- Custom path configuration
- Performance tuning
- Template customization

### 5. [Custom Templates](custom-templates/)
Template customization examples:
- Custom stub files for company standards
- Extended base classes
- Framework-specific templates (Bootstrap, Tailwind)
- Company-specific coding standards
- Advanced template inheritance

**Features demonstrated:**
- Stub file customization
- Template inheritance patterns
- Framework integration
- Coding standard enforcement
- Reusable template libraries

## Command Features Matrix

This table shows which TurboMaker features are demonstrated in each example:

| Feature | Blog System | API-Only | E-commerce | Configuration | Custom Templates |
|---------|-------------|----------|------------|---------------|------------------|
| **Basic Generation** | ✅ | ✅ | ✅ | ✅ | ✅ |
| Model relationships | ✅ | ✅ | ✅ | ❌ | ❌ |
| API resources | ✅ | ✅ | ✅ | ❌ | ✅ |
| Authorization policies | ✅ | ✅ | ✅ | ❌ | ❌ |
| Tests (Feature/Unit) | ✅ | ✅ | ✅ | ❌ | ✅ |
| Database factories | ✅ | ✅ | ✅ | ❌ | ❌ |
| Database seeders | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Advanced Features** |  |  |  |  |  |
| Action classes | ❌ | ❌ | ✅ | ❌ | ✅ |
| Service classes | ❌ | ❌ | ✅ | ❌ | ✅ |
| Model observers | ❌ | ❌ | ✅ | ❌ | ❌ |
| Validation rules | ❌ | ❌ | ❌ | ❌ | ✅ |
| **Configuration** |  |  |  |  |  |
| Custom paths | ❌ | ❌ | ❌ | ✅ | ❌ |
| Environment settings | ❌ | ❌ | ❌ | ✅ | ❌ |
| Performance tuning | ❌ | ❌ | ❌ | ✅ | ❌ |
| **Templates** |  |  |  |  |  |
| Custom stubs | ❌ | ❌ | ❌ | ❌ | ✅ |
| Template inheritance | ❌ | ❌ | ❌ | ❌ | ✅ |
| Framework integration | ❌ | ❌ | ❌ | ❌ | ✅ |

## Quick Start Guide

### For Learning TurboMaker
**Start with:** [Blog System](blog-system/) - covers all basic concepts and relationships.

1. Follow the step-by-step commands
2. Examine the generated code structure
3. Run migrations and seeders
4. Test the generated functionality

### For API Development
**Use:** [API-Only Application](api-only/) - REST API best practices.

1. Generate API-only modules
2. Review API resource classes
3. Test API endpoints
4. Understand authentication patterns

### For Complex Applications
**Explore:** [E-commerce System](ecommerce/) - advanced patterns and relationships.

1. Study complex relationship hierarchies
2. Learn service layer patterns
3. Understand event-driven architecture
4. Review business logic separation

### For Team Setup
**Check:** [Configuration Examples](configuration/) - team collaboration patterns.

1. Review team configuration examples
2. Set up environment-specific settings
3. Configure custom paths
4. Optimize for performance

### For Customization
**Study:** [Custom Templates](custom-templates/) - modify generated code.

1. Publish stub templates
2. Customize for your coding standards
3. Create reusable template libraries
4. Integrate with your framework choices

## Running Examples

Each example includes:
- Step-by-step generation commands
- Complete generated code samples
- Database migrations and seeders
- Test suites
- Documentation

To try an example:

1. Navigate to the example directory
2. Follow the README instructions
3. Run the provided commands
4. Review the generated code

## Contributing Examples

To contribute a new example:

1. Create a new directory under `examples/`
2. Include a detailed README with:
   - Overview and use case
   - Step-by-step commands
   - Generated code samples
   - Testing instructions
3. Provide complete working code
4. Document any customizations or special considerations

## Example Structure

Each example follows this structure:

```
example-name/
├── README.md              # Detailed instructions
├── commands.md            # All TurboMaker commands used
├── generated-code/        # Sample generated files
│   ├── Models/
│   ├── Controllers/
│   ├── tests/
│   └── ...
├── database/              # Migrations and seeders
├── config/                # Configuration files (if custom)
└── documentation/         # Additional documentation
```

## Tips for Using Examples

1. **Start Simple**: Begin with basic examples before attempting complex ones
2. **Understand the Commands**: Review the commands used to understand the options
3. **Customize**: Adapt examples to your specific requirements
4. **Test Everything**: Run tests to verify the generated code works
5. **Learn Patterns**: Study the relationship patterns and code organization

Happy coding! 🚀