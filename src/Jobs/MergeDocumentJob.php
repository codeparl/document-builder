<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Merge\DocumentMergerManager;
use UnnovateBrains\DocumentBuilder\Support\DocumentPipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;

final class MergeDocumentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    public function __construct(
        public readonly string $batchUuid
    ) {}



    public function handle(
        DocumentStorage $storage,
        DocumentMergerManager $mergers
    ): void {


        $workspace =
            $storage->batchWorkspace(
                $this->batchUuid
            );



        /*
        |--------------------------------------------------------------------------
        | Restore execution plan
        |--------------------------------------------------------------------------
        */

        $plan =
            ExecutionPlan::fromArray(
                $workspace->plan()
            );



        /*
        |--------------------------------------------------------------------------
        | Locate generated chunks
        |--------------------------------------------------------------------------
        */

        $chunks =
            $workspace->renderedChunkPaths(
                $plan->getType()
            );



        /*
        |--------------------------------------------------------------------------
        | Resolve merger
        |--------------------------------------------------------------------------
        */

        $merger =
            $mergers->merger(
                $plan->getType()
            );



        /*
        |--------------------------------------------------------------------------
        | Merge chunks
        |--------------------------------------------------------------------------
        */

        $result =
            $merger->merge(
                $chunks,
                new DocumentPipelineContext($plan)
            );



        /*
        |--------------------------------------------------------------------------
        | Store final document
        |--------------------------------------------------------------------------
        */

        $workspace->putFinal(
            $result,
            $plan->getType()
        );



        /*
        |--------------------------------------------------------------------------
        | Remove temporary chunk artifacts
        |--------------------------------------------------------------------------
        |
        | At this point the final document already exists.
        | The intermediate files are no longer required.
        |
        */

        $workspace->cleanup();



        /*
        |--------------------------------------------------------------------------
        | Mark completed
        |--------------------------------------------------------------------------
        */

        $workspace->updateStatus([
            'status' => 'completed',
            'completed_at' => now()->toISOString(),
        ]);
    }
}
