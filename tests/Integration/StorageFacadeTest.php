<?php

use UnnovateBrains\DocumentBuilder\Facades\Storage;
use UnnovateBrains\DocumentBuilder\Facades\Document;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use Illuminate\Contracts\Filesystem\Factory as StorageFactory;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use UnnovateBrains\DocumentBuilder\Pipelines\Stages\ResolveContextStage;
use UnnovateBrains\DocumentBuilder\Support\DocumentPipelineContext;

beforeEach(function () {
    // 1. Reset your storage directories
    app(StorageFactory::class)->disk('local')->deleteDirectory('documents');
    app(StorageFactory::class)->disk('local')->deleteDirectory('tenants');
    app(StorageFactory::class)->disk('local')->deleteDirectory('secure-docs');

    // 2. Set up dynamic mock directory for Blade views before the test runs
    $this->testViewPath = __DIR__ . '/../views';
    
    if (!File::exists($this->testViewPath . '/transcripts')) {
        File::makeDirectory($this->testViewPath . '/transcripts', 0755, true);
    }

    // Write a dummy blade file so $this->viewFactory->exists() returns true
    File::put(
        $this->testViewPath . '/transcripts/student.blade.php',
        '<h1>Student Report</h1>'
    );

    // Dynamic injection into Laravel's View engine
    View::addLocation($this->testViewPath);
});

// Clean up the temporary view files after the test completes
afterEach(function () {
    if (File::exists($this->testViewPath)) {
        File::deleteDirectory($this->testViewPath);
    }
});


it('allows developers to interact with storage using a simple filesystem api', function () {
    $path = Storage::put(
        'custom_reports/log.txt',
        'Audit execution payload'
    );

    expect($path)->toBe('custom_reports/log.txt');
    expect(Storage::exists('custom_reports/log.txt'))->toBeTrue();
    expect(Storage::get('custom_reports/log.txt'))->toBe('Audit execution payload');
});


it('supports tenant and school context scoping without exposing path logic', function () {
    $scopedStorage = Storage::forContext(
        'tenant-abc',
        'school-xyz'
    );

    $path = $scopedStorage->put(
        'logs/audit.txt',
        'Payload data'
    );

    expect($path)->toBe('tenants/tenant-abc/schools/school-xyz/logs/audit.txt');
    expect($scopedStorage->exists('logs/audit.txt'))->toBeTrue();
});


it('creates isolated batch workspaces', function () {
    $workspace = Storage::batchWorkspace('batch-123');

    expect($workspace->id())->toBe('batch-123');

    $workspace->putManifest([
        'type' => 'pdf',
        'engine' => 'mpdf',
    ]);

    expect($workspace->manifest())->toMatchArray([
        'type' => 'pdf',
        'engine' => 'mpdf',
    ]);
});


it('stores document chunks inside batch workspace', function () {
    $workspace = Storage::batchWorkspace('batch-123');

    $workspace->putInputChunk(1, [
        ['id' => 1, 'name' => 'John']
    ]);

    expect($workspace->inputChunk(1))->toMatchArray([
        ['id' => 1, 'name' => 'John']
    ]);
});


it('moves merged batch output to user supplied final path', function () {
    $workspace = Storage::batchWorkspace('batch-123');
    $workspace->putMerged('PDF CONTENT');

    Storage::move(
        $workspace->mergedPath(),
        'reports/users/123/students.pdf'
    );

    expect(Storage::exists('reports/users/123/students.pdf'))->toBeTrue();
});


it('allows document builder to define final output location', function () {
    $students = [
        [
            'id' => 1,
            'name' => 'John Doe'
        ]
    ];

    $result = Document::pdf()
        ->fromArray($students)
        ->view('transcripts.student')
        ->engine('mpdf')
        ->filename('students.pdf')
        ->saveTo('reports/students/students.pdf')
        ->save();

    expect($result->getFilename())->toBe('students.pdf');
    expect($result->getPath())->toBe('reports/students/students.pdf');
    expect(Storage::exists('reports/students/students.pdf'))->toBeTrue();
});

it('passes execution context through document builder pipeline', function () {

    $tenant = [
        'id' => 'tenant-abc',
        'name' => 'Demo Tenant',
    ];

    $school = [
        'id' => 'school-xyz',
        'name' => 'Demo School',
    ];

    $plan = Document::pdf()
        ->context([
            'tenant' => $tenant,
            'school' => $school,
            'locale' => 'en',
            'timezone' => 'Africa/Kampala',
        ])
        ->view('transcripts.student')
        ->compilePlan();


    expect($plan->getContext())
        ->toMatchArray([
            'tenant' => $tenant,
            'school' => $school,
            'locale' => 'en',
            'timezone' => 'Africa/Kampala',
        ]);
});



it('resolves document context inside pipeline stage', function () {

    $plan = Document::pdf()
        ->context([
            'locale' => 'fr',
            'timezone' => 'Africa/Kampala',
            'tenant' => [
                'id' => 'tenant-abc',
                'name' => 'Demo Tenant',
            ],
            'school' => [
                'id' => 'school-xyz',
                'name' => 'Demo School',
            ],
        ])
        ->compilePlan();


    $pipelineContext = new DocumentPipelineContext($plan);


    app(ResolveContextStage::class)
        ->handle(
            $pipelineContext,
            fn ($context) => $context
        );


    expect(app()->getLocale())
        ->toBe('fr');


    expect($pipelineContext->getState('document_context'))
        ->toMatchArray([
            'locale' => 'fr',
            'timezone' => 'Africa/Kampala',
            'tenant' => [
                'id' => 'tenant-abc',
                'name' => 'Demo Tenant',
            ],
            'school' => [
                'id' => 'school-xyz',
                'name' => 'Demo School',
            ],
        ]);
});