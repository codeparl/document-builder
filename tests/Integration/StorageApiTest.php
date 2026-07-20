<?php

use Illuminate\Contracts\Filesystem\Factory as StorageFactory;
use UnnovateBrains\DocumentBuilder\Storage\LaravelDocumentStorage;
use UnnovateBrains\DocumentBuilder\Storage\DocumentPathResolver;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContextResolver;

beforeEach(function () {
    $this->storageFactory = app(StorageFactory::class);
    //./vendor/bin/pest --filter=tests/Integration/StorageApiTest
    // Clean up
    $this->storageFactory->disk('local')->deleteDirectory('tenants');
    $this->storageFactory->disk('local')->deleteDirectory('secure-docs');

    // Create a mock for the Context Resolver
    $this->contextResolver = Mockery::mock(DocumentContextResolver::class);
    
    // Instantiate the resolver and storage
    $this->pathResolver = new DocumentPathResolver($this->contextResolver);
    $this->storage = new LaravelDocumentStorage($this->storageFactory, $this->pathResolver);
});

// ──────────────────────────────────────────────────────────────────────────
// 1. MULTI-TENANT ROUTING TESTS
// ──────────────────────────────────────────────────────────────────────────

it('routes general files to tenant sandboxes automatically', function () {
    // Arrange: Context
    $this->contextResolver->shouldReceive('tenantId')->andReturn('emma-high');
    $this->contextResolver->shouldReceive('schoolId')->andReturn('campus-north');

    $filename = 'student_report.pdf';
    $contents = '%PDF-tenant-isolated-binary';

    // Act
    $resolvedPath = $this->storage->put($filename, $contents);

    // Assert: Verify path structure
    expect($resolvedPath)->toBe('tenants/emma-high/schools/campus-north/student_report.pdf');
    expect($this->storageFactory->disk('local')->exists($resolvedPath))->toBeTrue();
});

it('clones context correctly when using forContext', function () {
    // Arrange: Setup initial mock
    $this->contextResolver->shouldReceive('tenantId')->andReturn('original-tenant');
    $this->contextResolver->shouldReceive('schoolId')->andReturn('original-school');

    // Act: Switch context
    $newStorage = $this->storage->forContext('new-tenant', 'new-school');
    $newStorage->put('test.txt', 'data');

    // Assert: Original storage remains untouched/uses original context
    // and new storage used the overridden context
    expect($this->storageFactory->disk('local')->exists('tenants/new-tenant/schools/new-school/test.txt'))->toBeTrue();
    expect($this->storageFactory->disk('local')->exists('tenants/original-tenant/schools/original-school/test.txt'))->toBeFalse();
});

// ──────────────────────────────────────────────────────────────────────────
// 2. BATCH WORKSPACE TESTS
// ──────────────────────────────────────────────────────────────────────────

it('initializes a batch workspace with correct pathing', function () {
    // Arrange: Context
    $this->contextResolver->shouldReceive('tenantId')->andReturn('t1');
    $this->contextResolver->shouldReceive('schoolId')->andReturn('s1');

    $batchUuid = 'uuid-12345';
    
    // Act
    $workspace = $this->storage->batchWorkspace($batchUuid);
    
    // Assert: Check workspace functionality
    $workspace->putManifest(['test' => 'data']);
    
    // Verify physical location (internal implementation detail of Workspace)
    $expectedPath = 'secure-docs/tenants/t1/schools/s1/batches/uuid-12345/manifest.json';
    expect($this->storageFactory->disk('local')->exists($expectedPath))->toBeTrue();
    
    // Verify interface contract
    expect($workspace->manifest())->toBe(['test' => 'data']);
});

it('cleans up the entire batch directory', function () {
    $this->contextResolver->shouldReceive('tenantId')->andReturn('t1');
    $this->contextResolver->shouldReceive('schoolId')->andReturn('s1');

    $workspace = $this->storage->batchWorkspace('cleanup-test');
    $workspace->putManifest(['status' => 'init']);
    
    $workspace->cleanup();

    expect($this->storageFactory->disk('local')->exists('secure-docs/tenants/t1/schools/s1/batches/cleanup-test'))->toBeFalse();
});