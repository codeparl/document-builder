<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Queue;

use Illuminate\Support\Str;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentQueue;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Queue\Jobs\PrepareDocumentJob;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlanSerializer;

final class DocumentQueueDispatcher
{
    public function __construct(
        private readonly DocumentStorage $storage,
        private readonly DocumentQueue $queue
    ) {}



    public function dispatch(
        ExecutionPlan $plan
    ): mixed {

        $batchId = (string) Str::uuid();


        $workspace =
            $this->storage->batchWorkspace($batchId);



        $workspace->putPlan(
            ExecutionPlanSerializer::serialize($plan)
        );


        $workspace->putStatus([
            'status' => 'queued',
            'batch_id' => $batchId,
        ]);



        return $this->queue->dispatch($plan);
    }
}
