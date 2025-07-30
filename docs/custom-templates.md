# Custom Templates

Laravel TurboMaker uses stub files (templates) to generate code. You can customize these templates to match your project's coding standards, add custom functionality, or modify the generated structure.

## Understanding Stubs

Stubs are template files that contain placeholder variables that get replaced with actual values during code generation.

### Available Stubs

TurboMaker includes the following stub files:

```
stubs/
├── action.create.stub      # Create action classes
├── action.delete.stub      # Delete action classes  
├── action.get.stub         # Get/Read action classes
├── action.update.stub      # Update action classes
├── controller.api.stub     # API controllers
├── controller.stub         # Web controllers
├── factory.stub            # Model factories
├── migration.stub          # Database migrations
├── model.stub              # Eloquent models
├── observer.stub           # Model observers
├── policy.stub             # Authorization policies
├── request.store.stub      # Store form requests
├── request.update.stub     # Update form requests
├── resource.stub           # API resources
├── rule.exists.stub        # Exists validation rules
├── rule.unique.stub        # Unique validation rules
├── seeder.stub             # Database seeders
├── service.stub            # Service classes
├── test.feature.stub       # Feature tests
├── test.unit.stub          # Unit tests
├── view.create.stub        # Create views
├── view.edit.stub          # Edit views
├── view.index.stub         # Index views
└── view.show.stub          # Show views
```

## Customizing Stubs

### 1. Publish Stubs

First, publish the stub files to your project:

```bash
php artisan vendor:publish --tag=turbomaker-stubs
```

This copies all stub files to `resources/stubs/turbomaker/` in your project.

### 2. Modify Published Stubs

Edit the published stubs to match your needs. The files use placeholder variables that get replaced during generation.

### Common Placeholders

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{ class }}` | The class name | `Post` |
| `{{ namespace }}` | The namespace | `App\\Models` |
| `{{ variable }}` | Variable name (camelCase) | `post` |
| `{{ variables }}` | Plural variable name | `posts` |
| `{{ table }}` | Database table name | `posts` |
| `{{ fields }}` | Model fields | `title, content` |
| `{{ relationships }}` | Relationship methods | BelongsTo methods |
| `{{ uses }}` | Use statements | `use App\\Models\\User;` |

## Customization Examples

### Custom Model Template

**Original model.stub:**
```php
<?php

namespace {{ namespace }};

use Illuminate\Database\Eloquent\Model;
{{ uses }}

class {{ class }} extends Model
{
    protected $fillable = [
        {{ fields }}
    ];

    {{ relationships }}
}
```

**Customized model.stub with UUIDs and soft deletes:**
```php
<?php

namespace {{ namespace }};

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
{{ uses }}

class {{ class }} extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = [
        {{ fields }}
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    {{ relationships }}
}
```

### Custom Controller Template

**Enhanced controller.stub with dependency injection:**
```php
<?php

namespace {{ namespace }};

use App\Http\Controllers\Controller;
use App\Services\{{ class }}Service;
use App\Http\Requests\Store{{ class }}Request;
use App\Http\Requests\Update{{ class }}Request;
use Illuminate\Http\Request;

class {{ class }}Controller extends Controller
{
    public function __construct(
        private {{ class }}Service ${{ variable }}Service
    ) {}

    public function index()
    {
        ${{ variables }} = $this->{{ variable }}Service->getAll();
        
        return view('{{ variables }}.index', compact('{{ variables }}'));
    }

    public function create()
    {
        return view('{{ variables }}.create');
    }

    public function store(Store{{ class }}Request $request)
    {
        ${{ variable }} = $this->{{ variable }}Service->create($request->validated());
        
        return redirect()
            ->route('{{ variables }}.show', ${{ variable }})
            ->with('success', '{{ class }} created successfully.');
    }

    public function show({{ class }} ${{ variable }})
    {
        return view('{{ variables }}.show', compact('{{ variable }}'));
    }

    public function edit({{ class }} ${{ variable }})
    {
        return view('{{ variables }}.edit', compact('{{ variable }}'));
    }

    public function update(Update{{ class }}Request $request, {{ class }} ${{ variable }})
    {
        $this->{{ variable }}Service->update(${{ variable }}, $request->validated());
        
        return redirect()
            ->route('{{ variables }}.show', ${{ variable }})
            ->with('success', '{{ class }} updated successfully.');
    }

    public function destroy({{ class }} ${{ variable }})
    {
        $this->{{ variable }}Service->delete(${{ variable }});
        
        return redirect()
            ->route('{{ variables }}.index')
            ->with('success', '{{ class }} deleted successfully.');
    }
}
```

### Custom Test Template

**Enhanced test.feature.stub with comprehensive tests:**
```php
<?php

use App\Models\{{ class }};
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('{{ class }} CRUD Operations', function () {
    it('can list {{ variables }}', function () {
        {{ class }}::factory(3)->create();

        $response = $this->get(route('{{ variables }}.index'));

        $response->assertStatus(200);
        $response->assertViewIs('{{ variables }}.index');
        $response->assertViewHas('{{ variables }}');
    });

    it('can show create form', function () {
        $response = $this->get(route('{{ variables }}.create'));

        $response->assertStatus(200);
        $response->assertViewIs('{{ variables }}.create');
    });

    it('can create a {{ variable }}', function () {
        $data = {{ class }}::factory()->make()->toArray();

        $response = $this->post(route('{{ variables }}.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('{{ table }}', $data);
    });

    it('can show a {{ variable }}', function () {
        ${{ variable }} = {{ class }}::factory()->create();

        $response = $this->get(route('{{ variables }}.show', ${{ variable }}));

        $response->assertStatus(200);
        $response->assertViewIs('{{ variables }}.show');
        $response->assertViewHas('{{ variable }}', ${{ variable }});
    });

    it('can show edit form', function () {
        ${{ variable }} = {{ class }}::factory()->create();

        $response = $this->get(route('{{ variables }}.edit', ${{ variable }}));

        $response->assertStatus(200);
        $response->assertViewIs('{{ variables }}.edit');
        $response->assertViewHas('{{ variable }}', ${{ variable }});
    });

    it('can update a {{ variable }}', function () {
        ${{ variable }} = {{ class }}::factory()->create();
        $updatedData = {{ class }}::factory()->make()->toArray();

        $response = $this->put(route('{{ variables }}.update', ${{ variable }}), $updatedData);

        $response->assertRedirect();
        $this->assertDatabaseHas('{{ table }}', array_merge(['id' => ${{ variable }}->id], $updatedData));
    });

    it('can delete a {{ variable }}', function () {
        ${{ variable }} = {{ class }}::factory()->create();

        $response = $this->delete(route('{{ variables }}.destroy', ${{ variable }}));

        $response->assertRedirect();
        $this->assertDatabaseMissing('{{ table }}', ['id' => ${{ variable }}->id]);
    });
});

describe('{{ class }} Validation', function () {
    it('requires valid data for creation', function () {
        $response = $this->post(route('{{ variables }}.store'), []);

        $response->assertSessionHasErrors();
    });

    it('requires valid data for updates', function () {
        ${{ variable }} = {{ class }}::factory()->create();

        $response = $this->put(route('{{ variables }}.update', ${{ variable }}), []);

        $response->assertSessionHasErrors();
    });
});
```

### Custom View Template

**Enhanced view.index.stub with modern UI:**
```blade
@extends('layouts.app')

@section('title', '{{ class }} Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ class }} Management</h1>
        <a href="{{ route('{{ variables }}.create') }}" 
           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Create New {{ class }}
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ID
                    </th>
                    <!-- Add your columns here -->
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Created At
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse(${{ variables }} as ${{ variable }})
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ ${{ variable }}->id }}
                    </td>
                    <!-- Add your columns here -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ ${{ variable }}->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <a href="{{ route('{{ variables }}.show', ${{ variable }}) }}" 
                           class="text-blue-600 hover:text-blue-900">View</a>
                        <a href="{{ route('{{ variables }}.edit', ${{ variable }}) }}" 
                           class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        <form action="{{ route('{{ variables }}.destroy', ${{ variable }}) }}" 
                              method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="text-red-600 hover:text-red-900"
                                    onclick="return confirm('Are you sure?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                        No {{ variables }} found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(${{ variables }}->hasPages())
        <div class="mt-6">
            {{ ${{ variables }}->links() }}
        </div>
    @endif
</div>
@endsection
```

## Configuration

### Stub Path Configuration

Create a configuration file to specify custom stub paths:

**config/turbomaker.php:**
```php
<?php

return [
    'stubs' => [
        'path' => resource_path('stubs/turbomaker'),
        'custom' => [
            'model' => resource_path('stubs/custom/model.stub'),
            'controller' => resource_path('stubs/custom/controller.stub'),
        ],
    ],
];
```

### Environment-Specific Stubs

You can have different stubs for different environments:

```
resources/stubs/
├── turbomaker/           # Default stubs
├── production/           # Production-specific stubs
└── development/          # Development-specific stubs
```

## Advanced Customization

### Custom Placeholder Variables

You can add custom logic to replace additional placeholders by extending the generators.

**Example: Adding custom placeholders in a service provider:**

```php
use Grazulex\LaravelTurbomaker\Generators\BaseGenerator;

class TurbomakerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        BaseGenerator::macro('replaceCustomPlaceholders', function ($stub, $data) {
            return str_replace([
                '{{ author }}',
                '{{ version }}',
                '{{ date }}',
            ], [
                config('app.author', 'Unknown'),
                config('app.version', '1.0.0'),
                now()->format('Y-m-d'),
            ], $stub);
        });
    }
}
```

### Template Inheritance

Create base templates that other templates can extend:

**base.controller.stub:**
```php
<?php

namespace {{ namespace }};

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class Base{{ class }}Controller extends Controller
{
    // Common controller logic
}
```

**extended.controller.stub:**
```php
<?php

namespace {{ namespace }};

use App\Http\Controllers\Base{{ class }}Controller;

class {{ class }}Controller extends Base{{ class }}Controller
{
    // Specific controller logic
}
```

## Best Practices

### 1. Keep Templates Simple
Don't add too much complexity to templates. Keep them focused and maintainable.

### 2. Version Control Templates
Always version control your custom templates to track changes.

### 3. Test Templates
Create test modules with your custom templates to ensure they work correctly.

### 4. Document Customizations
Document any custom placeholders or template logic for team members.

### 5. Backup Before Customizing
Always backup the original templates before making changes.

## Examples

See the [examples/custom-templates](../examples/custom-templates/) directory for:
- Complete custom template sets
- Real-world customization examples
- Template inheritance patterns
- Configuration examples