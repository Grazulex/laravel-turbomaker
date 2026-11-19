<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

uses()->group('commands');

function schemaExportTestDir(): string
{
    return sys_get_temp_dir().'/turbomaker-schema-export-test';
}

beforeEach(function () {
    $schemaPath = resource_path('schemas');
    File::deleteDirectory($schemaPath);
    File::makeDirectory($schemaPath, 0755, true);
    File::deleteDirectory(schemaExportTestDir());

    File::put($schemaPath.'/blog_post.schema.yml', <<<'YAML'
fields:
  title:
    type: string
    length: 200
    nullable: false
  body:
    type: text
    nullable: true
  status:
    type: enum
    values: ['draft', 'published']
    default: draft
  price:
    type: decimal
    nullable: false
  user_id:
    type: foreignId
    nullable: false

options:
  table: blog_posts
  timestamps: true
  soft_deletes: true

metadata:
  version: "2.0"
  description: "Blog posts"
YAML);
});

afterEach(function () {
    File::deleteDirectory(resource_path('schemas'));
    File::deleteDirectory(schemaExportTestDir());
});

it('exports a schema as an OpenAPI document to a file', function () {
    $file = schemaExportTestDir().'/openapi.json';

    $this->artisan('turbo:schema', ['action' => 'export', 'name' => 'BlogPost', '--format' => 'openapi', '--output' => $file])
        ->expectsOutput("✅ Schema 'BlogPost' exported as openapi")
        ->assertExitCode(0);

    expect(File::exists($file))->toBeTrue();

    $document = json_decode(File::get($file), true, flags: JSON_THROW_ON_ERROR);

    expect($document['openapi'])->toBe('3.0.3')
        ->and($document['info']['title'])->toBe('BlogPost API')
        ->and($document['info']['description'])->toBe('Blog posts')
        ->and($document['paths'])->toHaveKeys(['/blog-posts', '/blog-posts/{blog_post}'])
        ->and($document['paths']['/blog-posts'])->toHaveKeys(['get', 'post'])
        ->and($document['paths']['/blog-posts/{blog_post}'])->toHaveKeys(['get', 'put', 'patch', 'delete'])
        ->and($document['paths']['/blog-posts']['post']['responses'])->toHaveKeys(['201', '422'])
        ->and($document['paths']['/blog-posts/{blog_post}']['delete']['responses'])->toHaveKey('204');

    $model = $document['components']['schemas']['BlogPost'];
    expect($model['properties'])->toHaveKeys(['id', 'title', 'body', 'status', 'price', 'user_id', 'created_at', 'updated_at', 'deleted_at'])
        ->and($model['properties']['title'])->toBe(['type' => 'string', 'maxLength' => 200])
        ->and($model['properties']['body']['nullable'])->toBeTrue()
        ->and($model['properties']['status']['enum'])->toBe(['draft', 'published'])
        ->and($model['properties']['status']['default'])->toBe('draft')
        ->and($model['properties']['price'])->toBe(['type' => 'string', 'format' => 'decimal'])
        ->and($model['properties']['user_id'])->toBe(['type' => 'integer', 'format' => 'int64'])
        ->and($model['required'])->toBe(['id', 'title', 'status', 'price', 'user_id']);

    $store = $document['components']['schemas']['StoreBlogPostRequest'];
    expect($store['properties'])->not->toHaveKey('id')
        ->and($store['properties']['price'])->toBe(['type' => 'number'])
        ->and($store['required'])->toBe(['title', 'price', 'user_id']);

    $update = $document['components']['schemas']['UpdateBlogPostRequest'];
    expect($update)->not->toHaveKey('required');
});

it('uses openapi as the default export format', function () {
    $file = schemaExportTestDir().'/default.json';

    $this->artisan('turbo:schema', ['action' => 'export', 'name' => 'blog_post', '--output' => $file])
        ->assertExitCode(0);

    $document = json_decode(File::get($file), true, flags: JSON_THROW_ON_ERROR);

    expect($document)->toHaveKey('openapi');
});

it('prints the export to the console when no output file is given', function () {
    $this->withoutMockingConsoleOutput();

    $exitCode = Artisan::call('turbo:schema', ['action' => 'export', 'name' => 'BlogPost']);

    expect($exitCode)->toBe(0);

    $document = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);

    expect($document['openapi'])->toBe('3.0.3')
        ->and($document['servers'][0]['url'])->toEndWith('/api');
});

it('exports the schema as plain json', function () {
    $file = schemaExportTestDir().'/schema.json';

    $this->artisan('turbo:schema', ['action' => 'export', 'name' => 'BlogPost', '--format' => 'json', '--output' => $file])
        ->assertExitCode(0);

    $data = json_decode(File::get($file), true, flags: JSON_THROW_ON_ERROR);

    expect($data)->not->toHaveKey('openapi')
        ->and($data['fields'])->toHaveKeys(['title', 'body', 'status', 'price', 'user_id'])
        ->and($data['options']['table'])->toBe('blog_posts');
});

it('refuses to overwrite an export file without force', function () {
    $file = schemaExportTestDir().'/openapi.json';
    File::makeDirectory(schemaExportTestDir(), 0755, true);
    File::put($file, 'original');

    $this->artisan('turbo:schema', ['action' => 'export', 'name' => 'BlogPost', '--output' => $file])
        ->expectsOutput("File '{$file}' already exists. Use --force to overwrite.")
        ->assertExitCode(1);

    expect(File::get($file))->toBe('original');

    $this->artisan('turbo:schema', ['action' => 'export', 'name' => 'BlogPost', '--output' => $file, '--force' => true])
        ->assertExitCode(0);

    expect(File::get($file))->toContain('"openapi"');
});

it('rejects an unsupported export format', function () {
    $this->artisan('turbo:schema', ['action' => 'export', 'name' => 'BlogPost', '--format' => 'csv'])
        ->expectsOutput("Unsupported export format 'csv'. Supported formats: openapi, json")
        ->assertExitCode(1);
});

it('rejects a format disabled in the export configuration', function () {
    config()->set('turbomaker.export.formats', ['json']);

    $this->artisan('turbo:schema', ['action' => 'export', 'name' => 'BlogPost', '--format' => 'openapi'])
        ->expectsOutput("Export format 'openapi' is disabled. Add it to the 'turbomaker.export.formats' configuration.")
        ->assertExitCode(1);
});

it('requires a schema name for export', function () {
    $this->artisan('turbo:schema', ['action' => 'export'])
        ->expectsOutput('Schema name is required for export action')
        ->assertExitCode(1);
});

it('fails when the schema to export does not exist', function () {
    $this->artisan('turbo:schema', ['action' => 'export', 'name' => 'Missing'])
        ->expectsOutput("Schema 'Missing' not found")
        ->assertExitCode(1);
});
