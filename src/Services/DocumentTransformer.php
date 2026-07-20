<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Services;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentTransformer as TransformerContract;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

class DocumentTransformer
{
    public function apply(
        iterable $records,
        array $transformers,
        PipelineContext $context
    ): array {

        $result = [];

     

        foreach ($records as $record) {

            foreach ($transformers as $transformer) {

                if (is_callable($transformer)) {

                    $record = $transformer(
                        $record,
                        $context
                    );
                } elseif ($transformer instanceof TransformerContract) {

                    $record = $transformer->transform(
                        $record,
                        $context
                    );
                }


                if (!is_array($record)) {
                    throw new \RuntimeException(
                        'Document transformer must return an array record.'
                    );
                }
            }


            $result[] = $record;
        }


        return $result;
    }

        private function normalizeRecord(mixed $record): array
{
    if (is_array($record)) {
        return $record;
    }


    if (method_exists($record, 'toArray')) {
        return $record->toArray();
    }


    return $record;
}
}
