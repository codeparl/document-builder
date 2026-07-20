<?php

use UnnovateBrains\DocumentBuilder\Contracts\TemplateRenderer;
use UnnovateBrains\DocumentBuilder\Pipelines\Stages\RenderTemplateStage;
use UnnovateBrains\DocumentBuilder\Support\DocumentPipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
//./vendor/bin/pest tests/Integration/RenderTemplateStageTest.php
it('renders templates to HTML text without mixing concerns with the document engine', function () {
    // Arrange
    $plan = new ExecutionPlan(
        type: 'pdf',
        engine: 'mpdf',               
        templateEngine: 'blade',     
        source: null, 
        view: 'reports.student-grades',
        viewData: ['school' => 'Emma High School'],
        chunkSize: null,
        shouldMerge: false,
        outputFilename: 'transcript',
        disk: 'local',
        shouldQueue: false
    );

    $context = new DocumentPipelineContext($plan);
    $context->setRecords([['name' => 'Alex', 'grade' => 'A']]);

    $mockRenderer = Mockery::mock(TemplateRenderer::class);
    $mockRenderer->shouldReceive('render')
        ->once()
        ->with('blade', 'reports.student-grades', [
            'school' => 'Emma High School',
            'records' => [['name' => 'Alex', 'grade' => 'A']]
        ])
        ->andReturn('<html><body><h1>Emma High School</h1></body></html>');

    $stage = new RenderTemplateStage($mockRenderer);

    // Act
    $resultContext = $stage->handle($context, function ($ctx) { return $ctx; });

    // Assert
    expect($resultContext->getRenderedContent())->toBe('<html><body><h1>Emma High School</h1></body></html>');
});