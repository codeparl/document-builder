<?php

declare(strict_types=1);

use UnnovateBrains\DocumentBuilder\Facades\Document;
use UnnovateBrains\DocumentBuilder\DocumentManager;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Contracts\Renderer;
use UnnovateBrains\DocumentBuilder\DocumentBuilder;
use UnnovateBrains\DocumentBuilder\Pipelines\Stages\TransformStage;
use UnnovateBrains\DocumentBuilder\Services\DocumentTransformer;
use UnnovateBrains\DocumentBuilder\Support\DocumentPipelineContext;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockStudent;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockStudentSource;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockTemplateRenderer;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockVirtualStorage;

beforeEach(function () {

    app()->singleton(
        DocumentStorage::class,
        MockVirtualStorage::class
    );

    app()->singleton(
        Renderer::class,
        MockTemplateRenderer::class
    );

    app()->singleton(
    \UnnovateBrains\DocumentBuilder\Contracts\ChunkExecutor::class,
    \UnnovateBrains\DocumentBuilder\Chunking\SyncChunkExecutor::class
);

});

// it('transforms records after chunking before rendering', function () {

//     $source = new MockStudentSource([

//         new MockStudent('John'),

//         new MockStudent('Mary'),

//     ]);

//     $plan = Document::pdf()
//         ->engine('mock')
//         ->fromSource($source)
//         ->view('ignored')
//         ->chunk(1)
//         ->compilePlan();

//     $result = app(DocumentManager::class)
//         ->generate($plan);

//     expect($result)->not->toBeNull();

//     $content = $result->getContent();

//     expect($content)

//         ->toContain('JOHN')

//         ->toContain('MARY')

//         ->toContain('"processed":true');
// });

it('passes records through document transformer service', function () {

    $transformer = Mockery::mock(DocumentTransformer::class);


    $transformer
        ->shouldReceive('apply')
        ->once()
        ->andReturn([
            [
                'name' => 'JOHN'
            ]
        ]);


    $stage = new TransformStage(
        $transformer
    );


    $plan = (new DocumentBuilder('pdf'))
        ->fromArray([
            ['name'=>'John']
        ])
        ->compilePlan();


    $context = new DocumentPipelineContext($plan);


    $context->setRecords([
        ['name'=>'John']
    ]);


    $stage->handle(
        $context,
        fn($ctx)=>$ctx
    );


    expect($context->getRecords())
        ->toBe([
            [
                'name'=>'JOHN'
            ]
        ]);
});