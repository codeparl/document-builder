<?php

declare(strict_types=1);

use UnnovateBrains\DocumentBuilder\Services\DocumentTransformer;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentTransformer as DocumentTransformerContract;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;


it('applies closure transformers sequentially', function () {

    $service = new DocumentTransformer();


    $result = $service->apply(
        [
            [
                'name' => 'john',
                'score' => 10,
            ],
        ],
        [
            function ($record) {

                $record['score'] += 5;

                return $record;
            },

            function ($record) {

                $record['score'] *= 2;

                return $record;
            }
        ],
        mock(PipelineContext::class)
    );


    expect($result)
        ->toBe([
            [
                'name' => 'john',
                'score' => 30,
            ],
        ]);
});



it('applies class based transformers', function () {

    $service = new DocumentTransformer();


    $transformer = new class implements DocumentTransformerContract {

        public function transform(
            mixed $records,
            PipelineContext $context
        ): mixed {

            $records['uppercase'] =
                strtoupper($records['name']);

            return $records;
        }
    };


    $result = $service->apply(
        [
            [
                'name'=>'john'
            ],
        ],
        [
            $transformer
        ],
        mock(PipelineContext::class)
    );


    expect($result)
        ->toBe([
            [
                'name'=>'john',
                'uppercase'=>'JOHN',
            ]
        ]);
});



it('executes multiple transformers sequentially', function () {

    $service = new DocumentTransformer();


    $result = $service->apply(
        [
            [
                'name' => 'john',
                'score' => 10,
            ],
        ],
        [

            function ($record) {

                $record['score'] += 5;

                return $record;
            },


            function ($record) {

                $record['score'] *= 2;

                return $record;
            }

        ],
        mock(PipelineContext::class)
    );


    expect($result)
        ->toBe([
            [
                'name' => 'john',
                'score' => 30,
            ],
        ]);
});



it('passes pipeline context to closure transformers', function () {

    $service = new DocumentTransformer();


    $context = mock(PipelineContext::class);


    $result = $service->apply(
        [
            ['id'=>1],
        ],
        [

            function ($records, $receivedContext) use ($context) {

                expect($receivedContext)
                    ->toBe($context);


                return $records;
            }

        ],
        $context
    );


    expect($result)
        ->toBe([
            ['id'=>1]
        ]);
});