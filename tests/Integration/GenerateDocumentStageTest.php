<?php

use UnnovateBrains\DocumentBuilder\Contracts\DocumentEngine;
use UnnovateBrains\DocumentBuilder\Pipelines\Stages\GenerateDocumentStage;
use UnnovateBrains\DocumentBuilder\Support\DocumentPipelineContext;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;
use UnnovateBrains\DocumentBuilder\Support\DocumentMetadata;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;

it('successfully delegates compilation to the matching document engine strategy', function () {
    // Arrange: Create a standard PDF execution plan
    $plan = new ExecutionPlan(
        type: 'pdf',
        engine: 'mpdf',
        templateEngine: 'blade',
        source: null, 
        view: 'reports.invoice',
        viewData: [],
        chunkSize: null,
        shouldMerge: false,
        outputFilename: 'invoice',
        disk: 'local',
        shouldQueue: false
    );

    $context = new DocumentPipelineContext($plan);
    $context->setRenderedContent('<html><body><h1>Invoice</h1></body></html>');

    // Create a mock DocumentResult payload that the engine will return
    $mockMetadata = Mockery::mock(DocumentMetadata::class);
    $fakeResult = new DocumentResult(
        content: '%PDF-1.4-FAKE-BINARY-DATA...', // The raw binary stream
        path: '/tmp/invoice.pdf',
        filename: 'invoice.pdf',
        type: 'pdf',
        metadata: $mockMetadata
    );

    // Mock the engine to ensure it matches 'pdf' and 'mpdf'
    $mockEngine = Mockery::mock(DocumentEngine::class);
    $mockEngine->shouldReceive('type')->andReturn('pdf');
    $mockEngine->shouldReceive('supports')->with('mpdf')->andReturn(true);
    
    // Ensure the engine's render method is called EXACTLY once with our exact parameters
    $mockEngine->shouldReceive('render')
        ->once()
        ->with($plan, '<html><body><h1>Invoice</h1></body></html>')
        ->andReturn($fakeResult);

    // Register our mocked engine into the stage
    $stage = new GenerateDocumentStage();
    $stage->registerEngine($mockEngine);

    // Act
    $resultContext = $stage->handle($context, function ($ctx) { return $ctx; });

    // Assert: Verify the context safely stashed both the object and the raw binary string
    expect($resultContext->getResult())->toBe($fakeResult)
        ->and($resultContext->getState('document_binary_contents'))->toBe('%PDF-1.4-FAKE-BINARY-DATA...');
});

it('throws a runtime exception if no registered engine matches the requested document type', function () {
    // Arrange: Ask for a CSV, but we won't register a CSV engine
    $plan = new ExecutionPlan(
        type: 'csv',
        engine: null, // Let it auto-resolve if possible
        templateEngine: null,
        source: null, 
        view: null,
        viewData: [],
        chunkSize: null,
        shouldMerge: false,
        outputFilename: 'export',
        disk: 'local',
        shouldQueue: false
    );

    $context = new DocumentPipelineContext($plan);
    
    // Stage has zero engines registered
    $stage = new GenerateDocumentStage(); 

    // Act & Assert
    expect(fn() => $stage->handle($context, function ($ctx) { return $ctx; }))
        ->toThrow(
            RuntimeException::class, 
            'No valid document engine strategy registered capable of building type [csv].'
        );
});