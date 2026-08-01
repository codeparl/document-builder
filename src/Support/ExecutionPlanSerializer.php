<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use UnnovateBrains\DocumentBuilder\Facades\Source;

final class ExecutionPlanSerializer
{


    /**
     * Serialize execution plan for storage/queue.
     *
     * The result must be completely JSON serializable because it
     * is stored inside workspace/plan.json.
     */
    public static function serialize(
        ExecutionPlan $plan
    ): array {

        return [

            'type' =>
            $plan->getType(),
            'extension' => $plan->extension(),

            'engine' =>
            $plan->getEngine(),


            'template_engine' =>
            $plan->getTemplateEngine(),


            /*
            |--------------------------------------------------------------------------
            | Source serialization
            |--------------------------------------------------------------------------
            |
            | Source objects cannot survive queue serialization.
            |
            | Example:
            |
            | ArraySource
            | QuerySource
            | ModelSource
            |
            | are converted into payloads.
            |
            */
            'source' =>
            $plan->getSource()?->serialize(),



            'view' =>
            $plan->getView(),


            'view_data' =>
            $plan->getViewData(),



            'chunk_size' =>
            $plan->getChunkSize(),



            'should_merge' =>
            $plan->shouldMerge(),



            'output_filename' =>
            $plan->getOutputFilename(),



            'output_path' =>
            $plan->getOutputPath(),



            'disk' =>
            $plan->getDisk(),



            'should_queue' =>
            $plan->shouldQueue(),



            'driver_config' =>
            $plan->getDriverConfig(),



            'metadata' =>
            $plan->getMetadata()?->toArray(),



            'context' =>
            $plan->getContext(),


            /*
            |--------------------------------------------------------------------------
            | Chunk executor
            |--------------------------------------------------------------------------
            */
            'chunk_executor' =>
            $plan->getChunkExecutor(),

        ];
    }





    /**
     * Restore execution plan from stored payload.
     *
     * Used by queue workers.
     */
    public static function unserialize(
        array $payload
    ): ExecutionPlan {


        /*
        |--------------------------------------------------------------------------
        | Restore source
        |--------------------------------------------------------------------------
        |
        | plan.json contains:
        |
        | {
        |   "source":{
        |       "type":"array",
        |       "data":[]
        |   }
        | }
        |
        */
        $source = null;


        if (
            isset($payload['source'])
            &&
            is_array($payload['source'])
        ) {

            $source =
                Source::restore(
                    $payload['source']
                );
        }



        return new ExecutionPlan(


            type: $payload['type'],

            extension: $payload['extension'],

            engine: $payload['engine'] ?? null,



            templateEngine: $payload['template_engine']
                ?? 'blade',



            source: $source,



            view: $payload['view']
                ?? null,



            viewData: $payload['view_data']
                ?? [],



            outputPath: $payload['output_path']
                ?? null,



            chunkSize: $payload['chunk_size']
                ?? null,



            shouldMerge: $payload['should_merge']
                ?? false,



            outputFilename: $payload['output_filename']
                ?? null,



            disk: $payload['disk']
                ?? 'local',



            shouldQueue: $payload['should_queue']
                ?? false,



            metadata: isset($payload['metadata'])
                ? new DocumentMetadata(
                    $payload['metadata']
                )
                : new DocumentMetadata(),



            context: $payload['context']
                ?? [],



            driverConfig: $payload['driver_config']
                ?? [],

        );
    }
}
