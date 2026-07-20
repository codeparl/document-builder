<?php

use UnnovateBrains\DocumentBuilder\Drivers\Pdf\MpdfDriver;
use UnnovateBrains\DocumentBuilder\Pipelines\DocumentPipelineProcessor;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentDriver;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockStudentSource;

it('compiles valid pdf binaries using real mpdf engine integration', function () {
    // 1. Arrange: Use named arguments to safely satisfy your constructor
    $source = new MockStudentSource([
        [
            'id' => 1,
            'name' => 'John Doe',
            'grade' => 'A+'
        ]
    ]);
    $plan = new ExecutionPlan(
        type: 'pdf',
        engine: 'mpdf',
        source: $source,
        view: 'transcripts.student',
        viewData: ['term' => 'First Term'],
        chunkSize: null,
        shouldMerge: false,
        outputFilename: 'real_student_transcript',
        disk: 'local',
        shouldQueue: false
    );

    // Bind the real production driver to the interface
    app()->bind(DocumentDriver::class, MpdfDriver::class);

    // Setup the mock storage tracking
    $mockStorage = new \UnnovateBrains\DocumentBuilder\Tests\Mocks\MockVirtualStorage();
    app()->instance(DocumentStorage::class, $mockStorage);

    // 2. Act: Run the pipeline processor
    $pipeline = app(DocumentPipelineProcessor::class);
    $result = $pipeline->execute($plan);

    expect($result)->not->toBeNull();
    
    // Verify that mPDF actually spit out valid PDF file header markers
    expect($result->getContent())->toStartWith('%PDF-');
    
    // Verify that the file stage successfully dropped it down onto your disk wrapper
    expect($mockStorage->exists('documents/real_student_transcript.pdf'))->toBeTrue();
});