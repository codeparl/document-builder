<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use UnnovateBrains\DocumentBuilder\Facades\Document;
use UnnovateBrains\DocumentBuilder\DocumentManager;
use UnnovateBrains\DocumentBuilder\Drivers\DriverManager;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Drivers\Pdf\MpdfDriver;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockStudentSource;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockVirtualStorage;
use UnnovateBrains\DocumentBuilder\Contracts\Renderer;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockTemplateRenderer;

beforeEach(function () {

    $this->testViewPath = __DIR__ . '/../views';

    if (!File::exists($this->testViewPath . '/transcripts')) {
        File::makeDirectory(
            $this->testViewPath . '/transcripts',
            0755,
            true
        );
    }

    File::put(
        $this->testViewPath . '/transcripts/student.blade.php',
        '
        <h1>Student Report</h1>
        <p>Term: {{ $term }}</p>
        <p>School: {{ $context["school_id"] ?? "unknown" }}</p>
        '
    );

    View::addLocation($this->testViewPath);
});


afterEach(function () {

    if (File::exists($this->testViewPath)) {
        File::deleteDirectory($this->testViewPath);
    }

});


it('pipes document context through the entire pipeline and builds a valid document result', function () {

    /**
     * Storage binding
     */
    app()->singleton(
        DocumentStorage::class,
        MockVirtualStorage::class
    );


    /**
     * Renderer binding
     */
    app()->singleton(
        Renderer::class,
        MockTemplateRenderer::class
    );


    /**
     * Register PDF driver
     */
    $driverManager = app(DriverManager::class);

    $driverManager->register(
        new MpdfDriver()
    );


    $manager = app(DocumentManager::class);

    $storage = app(DocumentStorage::class);


    /**
     * Source data
     */
    $students = [
        [
            'id' => 1,
            'name' => 'John Doe',
            'grade' => 'A+',
        ],
    ];


    $source = new MockStudentSource($students);



    /**
     * Build document using public fluent API
     */
    $plan = Document::pdf()
        ->engine('mpdf')
        ->fromSource($source)
        ->view('transcripts.student', [
            'term' => 'First Term',
        ])
        ->filename('john_doe_pdf')
        ->metadata([
            'compiler' => 'Pest Test',
        ])
        ->context([
            'tenant_id' => 'abc-123',
            'school_id' => 'school-001',
            'locale' => 'en',
            'timezone' => 'Africa/Kampala',
        ])
        ->compilePlan();



    /**
     * Verify context survived builder compilation
     */
    expect($plan->getContext())
        ->toMatchArray([
            'tenant_id' => 'abc-123',
            'school_id' => 'school-001',
            'locale' => 'en',
            'timezone' => 'Africa/Kampala',
        ]);



    /**
     * Execute pipeline
     */
    $result = $manager->generate($plan);



    /**
     * Result assertions
     */
    expect($result)
        ->not->toBeNull();


    expect($result->getContent())
        ->toStartWith('%PDF-');


    expect($result->getPath())
        ->toBe('documents/john_doe_pdf.pdf');


    expect($result->getMetadata()->get('compiler'))
        ->toBe('Pest Test');



    /**
     * Storage assertion
     */
    expect(
        $storage->exists(
            'documents/john_doe_pdf.pdf'
        )
    )->toBeTrue();

});