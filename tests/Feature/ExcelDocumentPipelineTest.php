<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Facades\Document;
use UnnovateBrains\DocumentBuilder\Jobs\GenerateChunkJob;
use UnnovateBrains\DocumentBuilder\Jobs\MergeDocumentJob;
use UnnovateBrains\DocumentBuilder\Queue\Jobs\FinalizeDocumentJob;
use UnnovateBrains\DocumentBuilder\Queue\Jobs\PrepareDocumentJob;
use UnnovateBrains\DocumentBuilder\Queue\Jobs\ProcessDocumentChunkJob;
use UnnovateBrains\DocumentBuilder\Queue\Jobs\QueueDocumentExecutionJob;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlanSerializer;
use UnnovateBrains\DocumentBuilder\Support\QueuedDocumentResult;
use Illuminate\Support\Str;

it('executes standard excel pipeline and stores final document', function () {

    $storage = app(DocumentStorage::class);


    $records = [
        [
            'name' => 'Hassan',
            'phone' => '0700000000',
        ],
        [
            'name' => 'John',
            'phone' => '0700000001',
        ],
    ];


    $result = Document::excel()
        ->fromArray($records)
        ->columns(['name', 'phone'])
        ->filename('students')
        ->sync()
        ->save();



    expect($result)
        ->toBeInstanceOf(
            DocumentResult::class
        );



    expect($result->isComplete())
        ->toBeTrue();



    expect($result->getType())
        ->toBe('xlsx');



    expect(
        $storage->exists(
            $result->getPath()
        )
    )
        ->toBeTrue();



    expect(
        $storage->get(
            $result->getPath()
        )
    )
        ->not
        ->toBeEmpty();
});

it('executes chunked excel pipeline using default merger fallback', function () {

    $storage = app(DocumentStorage::class);



    $records = collect(range(1, 50))
        ->map(fn($i) => [
            'name' => "Student {$i}",
            'phone' => "0700{$i}",
        ])
        ->all();



    $result = Document::excel()
        ->fromArray($records)
        ->chunk(20)
        ->filename('students_excel')
        ->sync()
        ->save();



    expect($result)
        ->toBeInstanceOf(
            DocumentResult::class
        );



    expect($result->isComplete())
        ->toBeTrue();



    expect($result->getFilename())
        ->toBe('students_excel.xlsx');



    expect(
        $storage->exists(
            'documents/students_excel.xlsx'
        )
    )
        ->toBeTrue();



    expect(
        $storage->get(
            'documents/students_excel.xlsx'
        )
    )
        ->not
        ->toBeEmpty();
});

it('dispatches queued excel document execution', function () {

    Queue::fake();


    $records = collect(range(1, 50))
        ->map(fn($i) => [
            'name' => "Student {$i}",
        ])
        ->all();



    $result = Document::excel()
        ->fromArray($records)
        ->chunk(20)
        ->filename('queued_excel')
        ->queue()
        ->dispatch();



    expect($result)
        ->toBeInstanceOf(
            QueuedDocumentResult::class
        );


    Queue::assertPushed(
        QueueDocumentExecutionJob::class,
        1
    );
});

it('completes queued excel generation and persists final xlsx output', function () {


    $batchId = (string) Str::uuid();


    $storage = app(DocumentStorage::class);


    $workspace =
        $storage->batchWorkspace($batchId);



    $records = collect(range(1, 50))
        ->map(fn($i) => [
            'name' => "Student {$i}",
            'phone' => "0700{$i}",
        ])
        ->all();



    $plan = Document::excel()
        ->fromArray($records)
        ->chunk(20)
        ->filename('queued_excel')
        ->compilePlan();



    $workspace->putPlan(
        ExecutionPlanSerializer::serialize($plan)
    );


    $workspace->putManifest([
        'batch_id' => $batchId,
        'type' => 'xlsx',
    ]);


    $workspace->putStatus([
        'status' => 'processing',
        'batch_id' => $batchId,
    ]);



    /*
    |--------------------------------------------------------------------------
    | Since Excel does not split,
    | one chunk should be created.
    |--------------------------------------------------------------------------
    */


    $workspace->putChunk(
        1,
        [
            'number' => 1,
            'records' => $records,
        ]
    );



    $job = new ProcessDocumentChunkJob(
        $batchId,
        1
    );


    app()->call([
        $job,
        'handle'
    ]);



    expect(
        $workspace->renderedExists(
            1,
            'xlsx'
        )
    )
        ->toBeTrue();



    /*
    |--------------------------------------------------------------------------
    | Finalize
    |--------------------------------------------------------------------------
    */


    $job = new FinalizeDocumentJob(
        $batchId
    );


    app()->call([
        $job,
        'handle'
    ]);



    expect(
        $storage->exists(
            'documents/queued_excel.xlsx'
        )
    )
        ->toBeTrue();



    expect(
        $storage->get(
            'documents/queued_excel.xlsx'
        )
    )
        ->not
        ->toBeEmpty();
});
