<?php

declare(strict_types=1);


use UnnovateBrains\DocumentBuilder\DocumentBuilder;
use UnnovateBrains\DocumentBuilder\Contracts\ChunkExecutor;
use UnnovateBrains\DocumentBuilder\Contracts\Source;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Chunking\SyncChunkExecutor;
use UnnovateBrains\DocumentBuilder\Merge\DocumentMergerManager;
use UnnovateBrains\DocumentBuilder\Pipelines\Stages\ChunkingStage;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockDocumentMerger;
use UnnovateBrains\DocumentBuilder\Chunking\ChunkExecutorManager;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\DocumentPipeline;
use UnnovateBrains\DocumentBuilder\Support\DocumentPipelineContext;
use UnnovateBrains\DocumentBuilder\Contracts\ChunkProcessor;
use UnnovateBrains\DocumentBuilder\Support\ChunkResult;
use UnnovateBrains\DocumentBuilder\Support\DocumentMetadata;

beforeEach(function () {
    

    $this->app->singleton(
        DocumentMergerManager::class,
        function () {

            $manager = new DocumentMergerManager();

            $manager->register(
                'pdf',
                new MockDocumentMerger()
            );

            return $manager;
        }
    );

});

it('splits source into chunks and executes chunk executor', function () {
$executor = new class implements ChunkExecutor {

    public array $receivedChunks = [];

    public function execute(
        PipelineContext $context,
        array $chunks,
        Closure $next
    ): mixed {

        $this->receivedChunks = $chunks;

        $context->setState(
            'test_chunks',
            $chunks
        );

        // Stop here—we're only testing ChunkingStage.
        return $context;
    }
};

$manager = new ChunkExecutorManager();

$manager->register(
    'queue',
    $executor
);

$stage = new ChunkingStage(
    $manager
);


    $builder = new DocumentBuilder('pdf');


    $plan = $builder
        ->fromArray([
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
            ['id' => 5],
        ])
        ->chunk(2)
        ->sync()
        ->compilePlan();



    $context = new DocumentPipelineContext(
        $plan
    );


    $context->setRecords([
        ['id' => 1],
        ['id' => 2],
        ['id' => 3],
        ['id' => 4],
        ['id' => 5],
    ]);



    $stage->handle(
        $context,
        fn($ctx) => $ctx
    );



    expect(
        $context->getState('test_chunks')
    )
    ->toHaveCount(3);


    expect(
        $context->getState('test_chunks')[0]
    )
    ->toBe([
        ['id' => 1],
        ['id' => 2],
    ]);


    expect(
        $context->getState('test_chunks')[2]
    )
    ->toBe([
        ['id' => 5],
    ]);

});

it('processes every chunk synchronously', function () {

    $processed = [];

$processor = new class implements ChunkProcessor {

    public array $calls = [];


    public function process(
        PipelineContext $context,
        array $chunk,
        int $number
    ): ChunkResult {

        $this->calls[] = [
            'number' => $number,
            'chunk' => $chunk,
        ];


        return new ChunkResult(
            number: $number,
            path: "chunks/chunk-{$number}.pdf",
            type: 'pdf',
            filename: "chunk-{$number}.pdf",
            metadata: new DocumentMetadata([
                'chunk' => $number,
            ])
        );
    }
};
    $executor = new SyncChunkExecutor(
        $processor
    );

    $builder = new DocumentBuilder('pdf');

    $plan = $builder
        ->fromArray([])
        ->chunk(2)
        ->compilePlan();

    $context = new DocumentPipelineContext($plan);

    $chunks = [
        [
            ['id' => 1],
            ['id' => 2],
        ],
        [
            ['id' => 3],
            ['id' => 4],
        ],
        [
            ['id' => 5],
        ],
    ];

    $nextCalled = false;

    $executor->execute(
        $context,
        $chunks,
        function ($ctx) use (&$nextCalled) {

            $nextCalled = true;

            return $ctx;
        }
    );

    expect($processor->calls)->toHaveCount(3);

    expect($processor->calls[0]['number'])->toBe(1);
    expect($processor->calls[1]['number'])->toBe(2);
    expect($processor->calls[2]['number'])->toBe(3);

   $results = $context->getState('chunk_results');


expect($results)
    ->toHaveCount(3);


expect($results[0])
    ->toBeInstanceOf(ChunkResult::class);


expect($results[0]->number)
    ->toBe(1);


expect($results[0]->path)
    ->toBe('chunks/chunk-1.pdf');


expect($results[2]->number)
    ->toBe(3);

    expect($nextCalled)->toBeTrue();
});