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

it('executes standard document pipeline and stores final document', function () {
    $result =  Document::pdf()
        ->fromArray(['name' => 'Hassan', 'phone' => '038374764'])
        ->view('students', [
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
        ->save();



    expect($result)
        ->toBeInstanceOf(
            DocumentResult::class
        );


    expect($result->isComplete())
        ->toBeTrue();



    expect($result->getContent())
        ->toBeInstanceOf(
            DocumentContent::class
        );



    expect($result->getContent()->size())
        ->toBeGreaterThan(0);



    Storage::disk('local')
        ->assertExists(
            $result->getPath()
        );
});

it('executes a chunked document pipeline and stores the merged document', function () {

    $storage = app(DocumentStorage::class);


    $records = collect(range(1, 50))
        ->map(fn($i) => [
            'name' => "Student {$i}",
            'phone' => "0700{$i}",
        ])
        ->all();



    $result = Document::pdf()
        ->engine('mpdf')
        ->fromArray($records)
        ->view('students')
        ->chunk(20)
        ->merge()
        ->filename('students')
        ->sync()
        ->save();



    expect($result)
        ->toBeInstanceOf(DocumentResult::class);



    expect($result->isComplete())
        ->toBeTrue();



    /*
    |--------------------------------------------------------------------------
    | Verify permanent storage
    |--------------------------------------------------------------------------
    */

    $path =
        $result->getPath();



    expect(
        $storage->exists($path)
    )
        ->toBeTrue();



    /*
    |--------------------------------------------------------------------------
    | Retrieve stored document
    |--------------------------------------------------------------------------
    |
    | Storage owns the final artifact now.
    |
    */

    $content =
        $storage->get(
            $path
        );



    expect($content)
        ->not
        ->toBeEmpty();



    /*
    |--------------------------------------------------------------------------
    | Verify result metadata
    |--------------------------------------------------------------------------
    */

    expect($result->getFilename())
        ->toBe('students.pdf');


    expect($result->getType())
        ->toBe('pdf');
});
it('dispatches the master queue document execution job', function () {
    Queue::fake();

    $records = collect(range(1, 50))
        ->map(fn($i) => ['name' => "Student {$i}", 'phone' => "0700{$i}"])
        ->all();

    $result = Document::pdf()
        ->engine('mpdf')
        ->fromArray($records)
        ->view('students')
        ->chunk(20)
        ->merge()
        ->filename('queued_students')
        ->queue()
        ->dispatch();

    expect($result)->toBeInstanceOf(QueuedDocumentResult::class);
    Queue::assertPushed(QueueDocumentExecutionJob::class, 1);
});
it('queues document execution', function () {

    Queue::fake();

    /*
    |--------------------------------------------------------------------------
    | Arrange
    |--------------------------------------------------------------------------
    */

    $records = collect(range(1, 50))
        ->map(fn($i) => [
            'name' => "Student {$i}",
        ])
        ->all();

    /*
    |--------------------------------------------------------------------------
    | Dispatch
    |--------------------------------------------------------------------------
    */

    $result = Document::pdf()
        ->engine('mpdf')
        ->fromArray($records)
        ->view('students')
        ->chunk(20)
        ->filename('chunk_test')
        ->merge()
        ->queue()
        ->dispatch();

    /*
    |--------------------------------------------------------------------------
    | Assert result
    |--------------------------------------------------------------------------
    */

    expect($result)
        ->toBeInstanceOf(
            \UnnovateBrains\DocumentBuilder\Support\QueuedDocumentResult::class
        );

    /*
    |--------------------------------------------------------------------------
    | Assert execution job queued
    |--------------------------------------------------------------------------
    */

    Queue::assertPushed(
        QueueDocumentExecutionJob::class,
        1
    );
});

it('completes queued chunked document generation and persists final pdf output', function () {

    /*
    |--------------------------------------------------------------------------
    | Create execution workspace
    |--------------------------------------------------------------------------
    */

    $batchId = (string) Str::uuid();


    $storage =
        app(DocumentStorage::class);


    $workspace =
        $storage->batchWorkspace($batchId);



    /*
    |--------------------------------------------------------------------------
    | Create document plan
    |--------------------------------------------------------------------------
    */

    $records = collect(range(1, 50))
        ->map(fn($i) => [
            'name' => "Student {$i}",
            'phone' => "0700{$i}",
        ])
        ->all();



    $plan = Document::pdf()
        ->engine('mpdf')
        ->fromArray($records)
        ->view('students')
        ->chunk(20)
        ->merge()
        ->filename('queued_students')
        ->compilePlan();



    /*
    |--------------------------------------------------------------------------
    | Store queue execution artifacts
    |--------------------------------------------------------------------------
    */

    $workspace->putPlan(
        ExecutionPlanSerializer::serialize($plan)
    );


    $workspace->putManifest([
        'batch_id' => $batchId,
        'type' => 'pdf',
    ]);


    $workspace->putStatus([
        'status' => 'processing',
        'batch_id' => $batchId,
    ]);



    /*
    |--------------------------------------------------------------------------
    | Simulate ChunkingStage
    |--------------------------------------------------------------------------
    */

    $chunks = array_chunk(
        $records,
        20
    );


    foreach ($chunks as $index => $chunk) {

        $number = $index + 1;


        $workspace->putChunk(
            $number,
            [
                'number' => $number,
                'records' => $chunk,
            ]
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Execute chunk workers
    |--------------------------------------------------------------------------
    */

    foreach ([1, 2, 3] as $chunk) {

        $job =
            new ProcessDocumentChunkJob(
                $batchId,
                $chunk
            );


        app()->call([
            $job,
            'handle'
        ]);
    }



    /*
    |--------------------------------------------------------------------------
    | Verify chunks generated before finalization
    |--------------------------------------------------------------------------
    */

    expect(
        $workspace->renderedExists(1, 'pdf')
    )->toBeTrue();


    expect(
        $workspace->renderedExists(2, 'pdf')
    )->toBeTrue();


    expect(
        $workspace->renderedExists(3, 'pdf')
    )->toBeTrue();



    /*
    |--------------------------------------------------------------------------
    | Execute finalizer
    |--------------------------------------------------------------------------
    |
    | Continues:
    |
    | CompileDriverStage
    |        |
    |        v
    | MergeStage
    |        |
    |        v
    | OutputStage
    |        |
    |        v
    | LeaveContextStage
    |        |
    |        v
    | Cleanup workspace
    |
    */

    $job =
        new FinalizeDocumentJob(
            $batchId
        );


    app()->call([
        $job,
        'handle'
    ]);



    /*
    |--------------------------------------------------------------------------
    | Verify permanent output exists
    |--------------------------------------------------------------------------
    |
    | OutputStage owns permanent storage.
    |
    */

    expect(
        $storage->exists(
            'documents/queued_students.pdf'
        )
    )
        ->toBeTrue();



    /*
    |--------------------------------------------------------------------------
    | Verify workspace cleanup
    |--------------------------------------------------------------------------
    |
    | Temporary artifacts should be removed:
    |
    | plan.json
    | chunks/
    | rendered/
    | final/
    |
    */

    expect(
        $workspace->statusExists()
    )
        ->toBeFalse();



    /*
    |--------------------------------------------------------------------------
    | Verify merged temporary artifact is gone
    |--------------------------------------------------------------------------
    */

    expect(
        fn() => $workspace->final('pdf')
    )
        ->toThrow(
            RuntimeException::class
        );
});

it('retrieves batch status through Document facade', function () {

    $batchId = (string) Str::uuid();


    $workspace =
        app(DocumentStorage::class)
        ->batchWorkspace($batchId);



    $workspace->putStatus([
        'status' => 'processing',
        'completed' => 5,
    ]);



    expect(
        Document::status($batchId)
    )
        ->toMatchArray([
            'status' => 'processing',
            'completed' => 5,
        ]);
});


it('manually merges existing batch chunks through document facade', function () {

    /*
    |--------------------------------------------------------------------------
    | Create batch workspace
    |--------------------------------------------------------------------------
    */

    $batchId = (string) Str::uuid();


    $storage =
        app(DocumentStorage::class);


    $workspace =
        $storage->batchWorkspace(
            $batchId
        );



    /*
    |--------------------------------------------------------------------------
    | Generate chunk documents
    |--------------------------------------------------------------------------
    |
    | Simulates completed chunk workers.
    |
    */

    $chunkOne =
        Document::pdf()
        ->fromArray([
            [
                'name' => 'Student One',
                'phone' => '0700000001',
            ]
        ])
        ->view('students')
        ->sync()
        ->save();



    $chunkTwo =
        Document::pdf()
        ->fromArray([
            [
                'name' => 'Student Two',
                'phone' => '0700000002',
            ]
        ])
        ->view('students')
        ->sync()
        ->save();



    /*
    |--------------------------------------------------------------------------
    | Store rendered chunks
    |--------------------------------------------------------------------------
    |
    | Workspace stores raw document content.
    |
    */

    $workspace->putRendered(
        1,
        $chunkOne->getContent()->toString(),
        'pdf'
    );


    $workspace->putRendered(
        2,
        $chunkTwo->getContent()->toString(),
        'pdf'
    );



    /*
    |--------------------------------------------------------------------------
    | Store batch metadata
    |--------------------------------------------------------------------------
    */

    $workspace->putManifest([
        'batch_id' => $batchId,
        'type' => 'pdf',
    ]);


    $workspace->putStatus([
        'status' => 'completed',
        'completed' => 2,
        'total_chunks' => 2,
    ]);



    /*
    |--------------------------------------------------------------------------
    | Execute manual merge
    |--------------------------------------------------------------------------
    */

    $result =
        Document::merge(
            $batchId,
            'pdf'
        );



    /*
    |--------------------------------------------------------------------------
    | Verify result
    |--------------------------------------------------------------------------
    */

    expect($result)
        ->toBeInstanceOf(
            DocumentResult::class
        );


    expect(
        $result->isComplete()
    )
        ->toBeTrue();



    /*
    |--------------------------------------------------------------------------
    | Verify permanent storage
    |--------------------------------------------------------------------------
    */

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
