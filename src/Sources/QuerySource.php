<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Sources;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\Source;

final class QuerySource implements Source
{
    private array $with = [];

    private ?string $orderBy = null;

    private string $direction = 'asc';


    public function __construct(
        private readonly string $model
    ) {
    }


    /**
     * Resolve query records.
     *
     * The query is intentionally created here,
     * after pipeline context has been restored.
     *
     * @return iterable<mixed>
     */
    public function resolve(): iterable
    {
        return $this->buildQuery()->get();
    }


    /**
     * Count records without loading them.
     */
    public function count(): int
    {
        return $this->buildQuery()->count();
    }


    /**
     * Add eager loading relationships.
     */
    public function with(array $relations): self
    {
        $clone = clone $this;

        $clone->with = $relations;

        return $clone;
    }


    /**
     * Apply ordering.
     */
    public function orderBy(
        string $column,
        string $direction = 'asc'
    ): self {

        $clone = clone $this;

        $clone->orderBy = $column;
        $clone->direction = $direction;

        return $clone;
    }


    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }


    /**
     * Serialize query definition.
     *
     * Only store instructions required
     * to rebuild the query.
     */
    public function serialize(): array
    {
        return [
            'type' => 'query',

            'model' => $this->model,

            'with' => $this->with,

            'order_by' => $this->orderBy,

            'direction' => $this->direction,
        ];
    }


    /**
     * Restore query source from queue payload.
     *
     * We do not create the query here.
     *
     * The actual query must be created later
     * after ResolveContextStage has restored:
     *
     * - tenant database
     * - school database
     * - locale
     * - timezone
     *
     * @param array<string,mixed> $data
     */
    public static function deserialize(array $data): Source
    {
        $modelClass = $data['model'] ?? null;


        if (
            !is_string($modelClass) ||
            !class_exists($modelClass)
        ) {
            throw new RuntimeException(
                'Invalid queued query model.'
            );
        }


        if (
            !is_subclass_of(
                $modelClass,
                Model::class
            )
        ) {
            throw new RuntimeException(
                "{$modelClass} must extend Eloquent Model."
            );
        }


        $source = new self($modelClass);


        if (!empty($data['with'])) {

            $source = $source->with(
                $data['with']
            );
        }


        if (!empty($data['order_by'])) {

            $source = $source->orderBy(
                $data['order_by'],
                $data['direction'] ?? 'asc'
            );
        }


        return $source;
    }



    /**
     * Build the actual Eloquent query.
     *
     * IMPORTANT:
     * This runs only after ResolveContextStage.
     *
     * At this point tenancy switching has already happened.
     */
    private function buildQuery()
    {
        $query = ($this->model)::query();


        if (!empty($this->with)) {

            $query->with(
                $this->with
            );
        }


        if ($this->orderBy) {

            $query->orderBy(
                $this->orderBy,
                $this->direction
            );
        }


        return $query;
    }
}