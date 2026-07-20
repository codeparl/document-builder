<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Services;

use Closure;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentTransformer as TransformerContract;

final class DocumentTransformerManager
{

    /**
     * @param array<int, callable|TransformerContract> $transformers
     */
    public function transform(
        iterable $records,
        array $transformers,
        PipelineContext $context
    ): array {


        $result = [];


        foreach ($records as $record) {


            $data = $this->normalize(
                $record
            );


            foreach ($transformers as $transformer) {


                if ($transformer instanceof TransformerContract) {

                    $data =
                        $transformer->transform(
                            $data,
                            $context
                        );


                    continue;
                }



                if (is_callable($transformer)) {

                    $data =
                        $transformer(
                            $data,
                            $context
                        );
                }
            }



            $result[] = $data;
        }


        return $result;
    }



    private function normalize(
        mixed $record
    ): array {


        if (
            is_object($record) &&
            method_exists($record, 'toDocumentArray')
        ) {

            return $record->toDocumentArray();
        }



        if (
            is_object($record) &&
            method_exists($record, 'toArray')
        ) {

            return $record->toArray();
        }



        return (array) $record;
    }
}