<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Queue;

use Illuminate\Support\Str;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Contracts\Source;
use UnnovateBrains\DocumentBuilder\Queue\Jobs\PrepareDocumentJob;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;

final class QueueDocumentExecutor
{
    public function __construct(
        private readonly DocumentStorage $storage
    ) {}


    public function dispatch(ExecutionPlan $plan): string
    {
        $batchId = (string) Str::uuid();

        $workspace = $this->storage->batchWorkspace(
            $batchId
        );


        $workspace->putPlan(
            $this->serializePlan($plan)
        );


        $workspace->putManifest([
            'batch_id' => $batchId,
            'type' => $plan->getType(),
            'status' => 'queued',
            'created_at' => now()->toISOString(),
        ]);


        $workspace->putStatus([
            'status' => 'queued',
            'progress' => 0,
        ]);


        PrepareDocumentJob::dispatch(
            $batchId
        );


        return $batchId;
    }



    private function serializePlan(
        ExecutionPlan $plan
    ): array {

        return [

            'type' => $plan->getType(),

            'engine' => $plan->getEngine(),

            'template_engine' => $plan->getTemplateEngine(),

            'source' => $this->serializeSource(
                $plan->getSource()
            ),

            'view' => $plan->getView(),

            'view_data' => $plan->getViewData(),

            'chunk_size' => $plan->getChunkSize(),

            'should_merge' => $plan->shouldMerge(),

            'output_filename' => $plan->getOutputFilename(),

            'output_path' => $plan->getOutputPath(),

            'disk' => $plan->getDisk(),

            'metadata' => $plan->getMetadata()?->toArray(),

            'context' => $plan->getContext(),

            'driver_config' => $plan->getDriverConfig(),
        ];
    }

    private function serializeSource(?Source $source): ?array
    {
        if (!$source) {
            return null;
        }


        if (method_exists($source, 'toQueuePayload')) {

            return $source->toQueuePayload();
        }


        throw new \RuntimeException(
            'Source does not support queued execution: '
                . get_class($source)
        );
    }
}
