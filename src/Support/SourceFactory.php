<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\Source;
use UnnovateBrains\DocumentBuilder\Sources\ArraySource;
use UnnovateBrains\DocumentBuilder\Sources\JsonSource;
use UnnovateBrains\DocumentBuilder\Sources\CollectionSource;
use UnnovateBrains\DocumentBuilder\Sources\ModelSource;
use UnnovateBrains\DocumentBuilder\Sources\QuerySource;

final class SourceFactory
{
    /**
     * @var array<string,class-string<Source>>
     */
    private array $customSources = [];


    public function __construct(
        private readonly SourceRegistry $registry
    ) {}

    /**
     * Register application/package source types.
     *
     * Example:
     *
     * $factory->register(
     *     'students',
     *     StudentSource::class
     * );
     */
    public function register(
        string $type,
        string $sourceClass
    ): void {

        $this->customSources[$type] = $sourceClass;
    }



    /**
     * Restore source from serialized queue payload.
     *
     * @param array<string,mixed> $payload
     */
    public function restore(
        array $payload
    ): Source {

        $type = $payload['type'] ?? null;


        if (!$type) {
            throw new RuntimeException(
                'Source type is missing.'
            );
        }



        return match ($type) {


            'array' =>
            new ArraySource(
                $payload['items'] ?? []
            ),



            'json' =>
            new JsonSource(
                $payload['data'] ?? '{}'
            ),



            'collection' =>
            new CollectionSource(
                collect($payload['data'] ?? [])
            ),



            'model' =>
            $this->restoreModel(
                $payload
            ),



            'query' =>
            $this->restoreQuery(
                $payload
            ),



            default =>
            $this->restoreCustom(
                $type,
                $payload
            )
        };
    }



    /**
     * Restore application defined sources.
     */
    private function restoreCustom(
        string $type,
        array $payload
    ): Source {

        $sourceClass =
            $this->registry->get($type);


        return $sourceClass::deserialize(
            $payload
        );
    }



    private function restoreModel(
        array $payload
    ): Source {

        $modelClass =
            $payload['model'];


        return new ModelSource(
            $modelClass::findOrFail(
                $payload['id']
            )
        );
    }



    private function restoreQuery(
        array $payload
    ): Source {

        $model =
            new $payload['model'];


        $query =
            $model->newQuery();



        if (!empty($payload['with'])) {

            $query->with(
                $payload['with']
            );
        }



        if (!empty($payload['order_by'])) {

            $query->orderBy(
                $payload['order_by'],
                $payload['direction'] ?? 'asc'
            );
        }


        return new QuerySource(
            $query
        );
    }
}
