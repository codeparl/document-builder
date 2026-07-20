<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use UnnovateBrains\DocumentBuilder\Contracts\Source;

final class ExecutionPlanFactory
{
    /**
     * Rebuild an ExecutionPlan from persisted workspace data.
     *
     * Queue workers store execution plans as plain arrays inside the batch
     * workspace. This factory converts that serialized blueprint back into
     * a fully executable ExecutionPlan instance.
     *
     * Responsibilities:
     *
     * - Restore document configuration
     * - Restore output settings
     * - Restore metadata
     * - Restore execution context
     * - Delegate source rebuilding to SourceFactory
     *
     * Source restoration is delegated because every Source implementation
     * has different reconstruction requirements.
     *
     * @param array<string,mixed> $data
     *
     * @return ExecutionPlan
     */
    public static function fromArray(
        array $data
    ): ExecutionPlan {

        return new ExecutionPlan(

            type:
                $data['type'],


            engine:
                $data['engine'] ?? null,


            templateEngine:
                $data['template_engine'] ?? null,


            source:
                self::resolveSource(
                    $data['source'] ?? null
                ),


            view:
                $data['view'] ?? null,


            viewData:
                $data['view_data'] ?? [],


            outputPath:
                $data['output_path'] ?? null,


            chunkSize:
                $data['chunk_size'] ?? null,


            shouldMerge:
                $data['should_merge'] ?? false,


            outputFilename:
                $data['output_filename'] ?? null,


            disk:
                $data['disk'] ?? null,


            /*
             |--------------------------------------------------------------------------
             | Queue restoration
             |--------------------------------------------------------------------------
             |
             | This plan has been restored from a batch workspace.
             | It is no longer the original builder execution request.
             |
             */
            shouldQueue:
                false,


            metadata:
                isset($data['metadata'])
                    ? new DocumentMetadata(
                        $data['metadata']
                    )
                    : null,


            context:
                $data['context'] ?? [],


            driverConfig:
                $data['driver_config'] ?? []
        );
    }



    /**
     * Restore a serialized document source.
     *
     * Delegates reconstruction to SourceFactory.
     *
     * @param array<string,mixed>|null $payload
     *
     * @return Source|null
     */
    private static function resolveSource(
        ?array $payload
    ): ?Source {

        if (!$payload) {
            return null;
        }


        return app(SourceFactory::class)
            ->restore($payload);
    }
}