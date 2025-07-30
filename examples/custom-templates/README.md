# Custom Templates Example

This example demonstrates how to customize Laravel TurboMaker's stub templates to match your project's coding standards, add custom functionality, or integrate with specific frameworks.

## Overview

We'll create custom templates for:
- **Enterprise Model** - With UUIDs, soft deletes, and auditing
- **Service Classes** - With dependency injection and logging
- **API Controllers** - With rate limiting and enhanced responses
- **Tailwind Views** - Using Tailwind CSS instead of Bootstrap
- **Advanced Tests** - With more comprehensive test cases

## Setup Custom Templates

### 1. Publish Default Stubs

```bash
# Create the stubs directory
mkdir -p resources/stubs/turbomaker

# Copy package stubs to your project (manual step)
# The stubs are located in vendor/grazulex/laravel-turbomaker/stubs/
cp vendor/grazulex/laravel-turbomaker/stubs/* resources/stubs/turbomaker/
```

### 2. Configure TurboMaker

Create or update `config/turbomaker.php`:

```php
<?php

return [
    'stubs' => [
        'path' => resource_path('stubs/turbomaker'),
        
        // Override specific templates
        'custom' => [
            'model' => resource_path('stubs/custom/enterprise-model.stub'),
            'service' => resource_path('stubs/custom/enterprise-service.stub'),
            'controller.api' => resource_path('stubs/custom/api-controller.stub'),
            'view.index' => resource_path('stubs/custom/tailwind-index.stub'),
            'test.feature' => resource_path('stubs/custom/comprehensive-test.stub'),
        ],
    ],
    
    'defaults' => [
        'generate_tests' => true,
        'generate_factory' => true,
        'use_uuids' => true,
        'soft_deletes' => true,
    ],
];
```

## Custom Template Examples

### 1. Enterprise Model Template

**resources/stubs/custom/enterprise-model.stub**

```php
<?php

declare(strict_types=1);

namespace {{ namespace }};

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;
use App\Traits\Cacheable;
{{ uses }}

/**
 * {{ class }} Model
 * 
 * @property string $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * 
 * @author {{ author }}
 * @version {{ version }}
 * @since {{ date }}
 */
class {{ class }} extends Model
{
    use HasFactory, SoftDeletes, HasUuids, Auditable, Cacheable;

    /**
     * The table associated with the model.
     */
    protected $table = '{{ table }}';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        {{ fields }}
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'id' => 'string',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * The attributes that should be searched.
     */
    public static array $searchable = [
        // Add searchable fields here
    ];

    /**
     * The relationships that should be eager loaded.
     */
    protected $with = [
        // Add default relationships here
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Prevent lazy loading in development
        if (app()->environment('local')) {
            static::preventLazyLoading();
        }

        // Model events
        static::creating(function (self $model) {
            // Add creation logic here
        });

        static::updating(function (self $model) {
            // Add update logic here
        });
    }

    {{ relationships }}

    /**
     * Scope a query to search for records.
     */
    public function scopeSearch($query, string $term)
    {
        if (empty(static::$searchable)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            foreach (static::$searchable as $field) {
                $q->orWhere($field, 'like', "%{$term}%");
            }
        });
    }

    /**
     * Scope a query to filter by active status.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id'; // UUID
    }
}
```

### 2. Enterprise Service Template

**resources/stubs/custom/enterprise-service.stub**

```php
<?php

declare(strict_types=1);

namespace {{ namespace }};

use App\Models\{{ class }};
use App\Events\{{ class }}Created;
use App\Events\{{ class }}Updated;
use App\Events\{{ class }}Deleted;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * {{ class }} Service
 * 
 * Handles business logic for {{ class }} operations.
 * 
 * @author {{ author }}
 * @version {{ version }}
 * @since {{ date }}
 */
class {{ class }}Service
{
    /**
     * Cache key prefix for {{ class }} data.
     */
    private const CACHE_PREFIX = '{{ variable }}_';

    /**
     * Cache TTL in seconds (1 hour).
     */
    private const CACHE_TTL = 3600;

    /**
     * Get all {{ variables }} with optional filtering.
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = self::CACHE_PREFIX . 'all_' . md5(serialize($filters) . $perPage);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters, $perPage) {
            $query = {{ class }}::query();

            // Apply filters
            if (!empty($filters['search'])) {
                $query->search($filters['search']);
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['active']) && $filters['active']) {
                $query->active();
            }

            Log::info('Fetching {{ variables }}', ['filters' => $filters]);

            return $query->orderBy('created_at', 'desc')->paginate($perPage);
        });
    }

    /**
     * Find {{ variable }} by ID.
     */
    public function findById(string $id): ?{{ class }}
    {
        $cacheKey = self::CACHE_PREFIX . "find_{$id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return {{ class }}::find($id);
        });
    }

    /**
     * Create a new {{ variable }}.
     */
    public function create(array $data): {{ class }}
    {
        return DB::transaction(function () use ($data) {
            Log::info('Creating {{ variable }}', ['data' => $data]);

            ${{ variable }} = {{ class }}::create($data);

            // Clear relevant caches
            $this->clearCache();

            // Dispatch event
            {{ class }}Created::dispatch(${{ variable }});

            Log::info('{{ class }} created successfully', ['id' => ${{ variable }}->id]);

            return ${{ variable }};
        });
    }

    /**
     * Update an existing {{ variable }}.
     */
    public function update({{ class }} ${{ variable }}, array $data): {{ class }}
    {
        return DB::transaction(function () use (${{ variable }}, $data) {
            Log::info('Updating {{ variable }}', ['id' => ${{ variable }}->id, 'data' => $data]);

            $originalData = ${{ variable }}->toArray();
            ${{ variable }}->update($data);

            // Clear relevant caches
            $this->clearCache(${{ variable }}->id);

            // Dispatch event
            {{ class }}Updated::dispatch(${{ variable }}, $originalData);

            Log::info('{{ class }} updated successfully', ['id' => ${{ variable }}->id]);

            return ${{ variable }};
        });
    }

    /**
     * Delete a {{ variable }}.
     */
    public function delete({{ class }} ${{ variable }}): bool
    {
        return DB::transaction(function () use (${{ variable }}) {
            Log::info('Deleting {{ variable }}', ['id' => ${{ variable }}->id]);

            $result = ${{ variable }}->delete();

            if ($result) {
                // Clear relevant caches
                $this->clearCache(${{ variable }}->id);

                // Dispatch event
                {{ class }}Deleted::dispatch(${{ variable }});

                Log::info('{{ class }} deleted successfully', ['id' => ${{ variable }}->id]);
            } else {
                Log::error('Failed to delete {{ variable }}', ['id' => ${{ variable }}->id]);
            }

            return $result;
        });
    }

    /**
     * Restore a soft-deleted {{ variable }}.
     */
    public function restore(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            Log::info('Restoring {{ variable }}', ['id' => $id]);

            ${{ variable }} = {{ class }}::withTrashed()->find($id);
            
            if (!${{ variable }}) {
                Log::error('{{ class }} not found for restoration', ['id' => $id]);
                return false;
            }

            $result = ${{ variable }}->restore();

            if ($result) {
                $this->clearCache($id);
                Log::info('{{ class }} restored successfully', ['id' => $id]);
            }

            return $result;
        });
    }

    /**
     * Get {{ variable }} statistics.
     */
    public function getStatistics(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'statistics';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return [
                'total' => {{ class }}::count(),
                'active' => {{ class }}::active()->count(),
                'inactive' => {{ class }}::where('is_active', false)->count(),
                'deleted' => {{ class }}::onlyTrashed()->count(),
                'created_today' => {{ class }}::whereDate('created_at', today())->count(),
                'created_this_month' => {{ class }}::whereMonth('created_at', now()->month)->count(),
            ];
        });
    }

    /**
     * Search {{ variables }} with advanced filters.
     */
    public function search(string $term, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = {{ class }}::search($term);

        // Apply additional filters
        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                $query->where($key, $value);
            }
        }

        Log::info('Searching {{ variables }}', ['term' => $term, 'filters' => $filters]);

        return $query->paginate($perPage);
    }

    /**
     * Clear cache for {{ variable }}.
     */
    private function clearCache(?string $id = null): void
    {
        if ($id) {
            Cache::forget(self::CACHE_PREFIX . "find_{$id}");
        }

        // Clear list caches (this is a simple approach; in production, use cache tags)
        Cache::flush(); // In production, use more specific cache clearing
    }
}
```

### 3. Enhanced API Controller Template

**resources/stubs/custom/api-controller.stub**

```php
<?php

declare(strict_types=1);

namespace {{ namespace }};

use App\Http\Controllers\Controller;
use App\Models\{{ class }};
use App\Services\{{ class }}Service;
use App\Http\Requests\Store{{ class }}Request;
use App\Http\Requests\Update{{ class }}Request;
use App\Http\Resources\{{ class }}Resource;
use App\Http\Resources\{{ class }}Collection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;

/**
 * {{ class }} API Controller
 * 
 * Handles API endpoints for {{ class }} operations.
 * 
 * @group {{ class }} Management
 * 
 * @author {{ author }}
 * @version {{ version }}
 * @since {{ date }}
 */
class {{ class }}Controller extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private {{ class }}Service ${{ variable }}Service
    ) {
        $this->middleware('auth:sanctum');
        $this->middleware('throttle:api')->only(['store', 'update', 'destroy']);
    }

    /**
     * Display a listing of {{ variables }}.
     * 
     * @group {{ class }} Management
     * 
     * @queryParam page integer Page number. Example: 1
     * @queryParam per_page integer Items per page (max 100). Example: 15
     * @queryParam search string Search term. Example: "test"
     * @queryParam status string Filter by status. Example: "active"
     * @queryParam sort_by string Sort field. Example: "created_at"
     * @queryParam sort_direction string Sort direction (asc|desc). Example: "desc"
     * 
     * @response 200 {
     *   "data": [{"id": "uuid", "name": "Example"}],
     *   "links": {"first": "...", "last": "...", "prev": null, "next": "..."},
     *   "meta": {"current_page": 1, "last_page": 10, "per_page": 15, "total": 150}
     * }
     */
    public function index(Request $request): JsonResponse
    {
        // Rate limiting
        $key = 'api:{{ variables }}:index:' . $request->user()->id;
        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }
        RateLimiter::hit($key, 60);

        $filters = $request->only(['search', 'status', 'active']);
        $perPage = min($request->get('per_page', 15), 100);

        ${{ variables }} = $this->{{ variable }}Service->getAll($filters, $perPage);

        return response()->json([
            'data' => {{ class }}Resource::collection(${{ variables }}),
            'meta' => [
                'current_page' => ${{ variables }}->currentPage(),
                'last_page' => ${{ variables }}->lastPage(),
                'per_page' => ${{ variables }}->perPage(),
                'total' => ${{ variables }}->total(),
                'from' => ${{ variables }}->firstItem(),
                'to' => ${{ variables }}->lastItem(),
            ],
            'links' => [
                'first' => ${{ variables }}->url(1),
                'last' => ${{ variables }}->url(${{ variables }}->lastPage()),
                'prev' => ${{ variables }}->previousPageUrl(),
                'next' => ${{ variables }}->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created {{ variable }}.
     * 
     * @group {{ class }} Management
     * 
     * @bodyParam name string required The name of the {{ variable }}. Example: "New {{ class }}"
     * 
     * @response 201 {"data": {"id": "uuid", "name": "New {{ class }}"}}
     * @response 422 {"message": "The given data was invalid.", "errors": {"name": ["The name field is required."]}}
     */
    public function store(Store{{ class }}Request $request): JsonResponse
    {
        ${{ variable }} = $this->{{ variable }}Service->create($request->validated());

        return response()->json([
            'message' => '{{ class }} created successfully.',
            'data' => new {{ class }}Resource(${{ variable }}),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified {{ variable }}.
     * 
     * @group {{ class }} Management
     * 
     * @urlParam {{ variable }} string required The ID of the {{ variable }}. Example: "uuid"
     * 
     * @response 200 {"data": {"id": "uuid", "name": "Example {{ class }}"}}
     * @response 404 {"message": "{{ class }} not found."}
     */
    public function show(string $id): JsonResponse
    {
        ${{ variable }} = $this->{{ variable }}Service->findById($id);

        if (!${{ variable }}) {
            return response()->json([
                'message' => '{{ class }} not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => new {{ class }}Resource(${{ variable }}),
        ]);
    }

    /**
     * Update the specified {{ variable }}.
     * 
     * @group {{ class }} Management
     * 
     * @urlParam {{ variable }} string required The ID of the {{ variable }}. Example: "uuid"
     * @bodyParam name string The name of the {{ variable }}. Example: "Updated {{ class }}"
     * 
     * @response 200 {"data": {"id": "uuid", "name": "Updated {{ class }}"}}
     * @response 404 {"message": "{{ class }} not found."}
     * @response 422 {"message": "The given data was invalid.", "errors": {"name": ["The name field is required."]}}
     */
    public function update(Update{{ class }}Request $request, string $id): JsonResponse
    {
        ${{ variable }} = $this->{{ variable }}Service->findById($id);

        if (!${{ variable }}) {
            return response()->json([
                'message' => '{{ class }} not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $updated{{ class }} = $this->{{ variable }}Service->update(${{ variable }}, $request->validated());

        return response()->json([
            'message' => '{{ class }} updated successfully.',
            'data' => new {{ class }}Resource($updated{{ class }}),
        ]);
    }

    /**
     * Remove the specified {{ variable }}.
     * 
     * @group {{ class }} Management
     * 
     * @urlParam {{ variable }} string required The ID of the {{ variable }}. Example: "uuid"
     * 
     * @response 200 {"message": "{{ class }} deleted successfully."}
     * @response 404 {"message": "{{ class }} not found."}
     */
    public function destroy(string $id): JsonResponse
    {
        ${{ variable }} = $this->{{ variable }}Service->findById($id);

        if (!${{ variable }}) {
            return response()->json([
                'message' => '{{ class }} not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->{{ variable }}Service->delete(${{ variable }});

        return response()->json([
            'message' => '{{ class }} deleted successfully.',
        ]);
    }

    /**
     * Get {{ variable }} statistics.
     * 
     * @group {{ class }} Management
     * 
     * @response 200 {"data": {"total": 100, "active": 80, "inactive": 20}}
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->{{ variable }}Service->getStatistics();

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Search {{ variables }}.
     * 
     * @group {{ class }} Management
     * 
     * @queryParam q string required Search term. Example: "test"
     * @queryParam filters array Additional filters. Example: {"status": "active"}
     * 
     * @response 200 {"data": [{"id": "uuid", "name": "Test {{ class }}"}]}
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'filters' => 'array',
        ]);

        ${{ variables }} = $this->{{ variable }}Service->search(
            $request->get('q'),
            $request->get('filters', []),
            $request->get('per_page', 15)
        );

        return response()->json([
            'data' => {{ class }}Resource::collection(${{ variables }}),
            'meta' => [
                'total' => ${{ variables }}->total(),
                'per_page' => ${{ variables }}->perPage(),
                'current_page' => ${{ variables }}->currentPage(),
            ],
        ]);
    }
}
```

### 4. Tailwind CSS View Template

**resources/stubs/custom/tailwind-index.stub**

```blade
@extends('layouts.app')

@section('title', '{{ class }} Management')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ class }} Management</h1>
                    <p class="mt-2 text-sm text-gray-700">Manage your {{ variables }} efficiently</p>
                </div>
                @can('create', App\Models\{{ class }}::class)
                    <div class="flex space-x-3">
                        <a href="{{ route('{{ variables }}.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create {{ class }}
                        </a>
                    </div>
                @endcan
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Filters</h3>
            </div>
            <form method="GET" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <input type="text" 
                               name="search" 
                               id="search" 
                               value="{{ request('search') }}"
                               placeholder="Search {{ variables }}..."
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" 
                                id="status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <div class="flex space-x-2">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Filter
                            </button>
                            <a href="{{ route('{{ variables }}.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150">
                                Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Data Table -->
        <div class="bg-white shadow overflow-hidden rounded-lg">
            @if(${{ variables }}->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach(${{ variables }} as ${{ variable }})
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ ${{ variable }}->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ ${{ variable }}->name }}
                                        </div>
                                        @if(${{ variable }}->description)
                                            <div class="text-sm text-gray-500">
                                                {{ Str::limit(${{ variable }}->description, 50) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(${{ variable }}->is_active ?? true)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ ${{ variable }}->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            @can('view', ${{ variable }})
                                                <a href="{{ route('{{ variables }}.show', ${{ variable }}) }}" 
                                                   class="text-blue-600 hover:text-blue-900">
                                                    View
                                                </a>
                                            @endcan
                                            
                                            @can('update', ${{ variable }})
                                                <a href="{{ route('{{ variables }}.edit', ${{ variable }}) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900">
                                                    Edit
                                                </a>
                                            @endcan
                                            
                                            @can('delete', ${{ variable }})
                                                <form action="{{ route('{{ variables }}.destroy', ${{ variable }}) }}" 
                                                      method="POST" 
                                                      class="inline-block"
                                                      onsubmit="return confirm('Are you sure you want to delete this {{ variable }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-900">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(${{ variables }}->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                @if(${{ variables }}->previousPageUrl())
                                    <a href="{{ ${{ variables }}->previousPageUrl() }}" 
                                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Previous
                                    </a>
                                @endif
                                @if(${{ variables }}->nextPageUrl())
                                    <a href="{{ ${{ variables }}->nextPageUrl() }}" 
                                       class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Next
                                    </a>
                                @endif
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing {{ ${{ variables }}->firstItem() }} to {{ ${{ variables }}->lastItem() }} of {{ ${{ variables }}->total() }} results
                                    </p>
                                </div>
                                <div>
                                    {{ ${{ variables }}->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No {{ variables }} found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new {{ variable }}.</p>
                    @can('create', App\Models\{{ class }}::class)
                        <div class="mt-6">
                            <a href="{{ route('{{ variables }}.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Create {{ class }}
                            </a>
                        </div>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add any JavaScript functionality here
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit search form on input (with debounce)
        const searchInput = document.getElementById('search');
        if (searchInput) {
            let timeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    if (this.value.length >= 3 || this.value.length === 0) {
                        this.form.submit();
                    }
                }, 500);
            });
        }
    });
</script>
@endpush
```

## Using Custom Templates

### Generate with Custom Templates

```bash
# This will use your custom templates
php artisan turbo:make Product --services --tests --factory

# The generated files will use:
# - Enterprise model template (UUIDs, soft deletes, auditing)
# - Enterprise service template (logging, caching, events)
# - Enhanced API controller (rate limiting, comprehensive responses)
# - Tailwind views (modern, responsive design)
```

### Testing Custom Templates

Create a test script to verify your templates:

**test-custom-templates.sh**

```bash
#!/bin/bash

echo "Testing custom templates..."

# Generate test models with different options
php artisan turbo:make TestProduct --services --api --tests --factory --force
php artisan turbo:make TestCategory --views --tests --force
php artisan turbo:make TestOrder --belongs-to=User --services --tests --force

echo "Custom templates test completed!"
echo "Check the generated files in:"
echo "- app/Models/"
echo "- app/Services/"
echo "- app/Http/Controllers/Api/"
echo "- resources/views/"
echo "- tests/"
```

## Template Variables Reference

All templates can use these variables:

| Variable | Description | Example |
|----------|-------------|---------|
| `{{ class }}` | Class name | `Product` |
| `{{ namespace }}` | Full namespace | `App\Models` |
| `{{ variable }}` | camelCase variable | `product` |
| `{{ variables }}` | Plural camelCase | `products` |
| `{{ table }}` | Database table | `products` |
| `{{ fields }}` | Model fillable fields | `'name', 'price'` |
| `{{ relationships }}` | Relationship methods | `public function user()...` |
| `{{ uses }}` | Use statements | `use App\Models\User;` |
| `{{ author }}` | Author name | From config |
| `{{ version }}` | Version | From config |
| `{{ date }}` | Current date | `2024-01-15` |

## Best Practices

1. **Start Small**: Begin with one template and gradually customize others
2. **Version Control**: Always commit templates to version control
3. **Test Thoroughly**: Generate test models to verify templates work
4. **Document Changes**: Document any custom variables or logic
5. **Team Standards**: Ensure all team members use the same templates
6. **Backup Originals**: Keep copies of original templates before customizing

This custom templates example provides enterprise-grade code generation that matches professional development standards!