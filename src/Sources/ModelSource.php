<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Sources;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\Source;

final class ModelSource implements Source
{
    public function __construct(
        private readonly Model $model
    ) {}


    /**
     * Resolve the single model record.
     *
     * @return iterable<Model>
     */
    public function resolve(): iterable
    {
        return [$this->model];
    }


    /**
     * A model source always contains one record.
     */
    public function count(): int
    {
        return 1;
    }


    /**
     * A valid model instance is never empty.
     */
    public function isEmpty(): bool
    {
        return false;
    }


    /**
     * Serialize model identity for queue transport.
     *
     * We only store the information required to reload the model.
     *
     * @return array<string,mixed>
     */
    public function serialize(): array
    {
        return [
            'type' => 'model',
            'model' => get_class($this->model),
            'id' => $this->model->getKey(),
        ];
    }


    /**
     * Restore model from queue payload.
     *
     * @param array<string,mixed> $data
     */
    public static function deserialize(array $data): Source
    {
        $modelClass = $data['model'] ?? null;


        if (!$modelClass || !is_a($modelClass, Model::class, true)) {
            throw new RuntimeException(
                'Invalid model source definition.'
            );
        }


        $model = $modelClass::query()
            ->findOrFail($data['id']);


        return new self($model);
    }
}