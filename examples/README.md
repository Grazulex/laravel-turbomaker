# Laravel TurboMaker Examples

This directory contains practical examples demonstrating different use cases and patterns for Laravel TurboMaker.

## Available Examples

### 1. [Blog System](blog-system/)
A complete blog system showcasing:
- User authentication and relationships
- Post and comment management
- Category organization
- Comprehensive CRUD operations

**Commands used:**
```bash
php artisan turbo:make User --has-many=Post --has-many=Comment --policies --tests --factory
php artisan turbo:make Category --has-many=Post --tests --factory
php artisan turbo:make Post --belongs-to=User --belongs-to=Category --has-many=Comment --policies --tests --factory --seeder
php artisan turbo:make Comment --belongs-to=Post --belongs-to=User --policies --tests --factory
```

### 2. [API-Only Application](api-only/)
REST API development example featuring:
- API-first architecture
- JSON resources and collections
- Authentication and authorization
- Comprehensive API testing

**Commands used:**
```bash
php artisan turbo:api Product --tests --factory --policies
php artisan turbo:api Category --tests --factory
php artisan turbo:api Order --belongs-to=User --has-many=OrderItem --tests --policies
```

### 3. [E-commerce System](ecommerce/)
Advanced e-commerce platform demonstrating:
- Complex relationship hierarchies
- Product catalog management
- Order processing workflow
- Inventory tracking

**Commands used:**
```bash
php artisan turbo:make Product --belongs-to=Category --belongs-to=Brand --has-many=OrderItem --policies --tests --factory --observers
php artisan turbo:make Order --belongs-to=User --has-many=OrderItem --has-one=Payment --services --actions --observers --tests
```

### 4. [Configuration Examples](configuration/)
Different configuration setups for:
- Team development standards
- Environment-specific settings
- Custom file paths and namespaces
- Performance optimizations

### 5. [Custom Templates](custom-templates/)
Template customization examples:
- Custom stub files
- Extended base classes
- Framework-specific templates (Bootstrap, Tailwind)
- Company-specific coding standards

## Quick Start

Choose an example that matches your use case:

### For Learning TurboMaker
Start with the **Blog System** - it covers all basic concepts and relationships.

### For API Development
Use the **API-Only Application** example for REST API best practices.

### For Complex Applications
The **E-commerce System** shows advanced patterns and relationships.

### For Customization
Check **Custom Templates** to learn how to modify generated code.

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