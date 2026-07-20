<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Repositories;

use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentBatchRepository;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;

final class StorageDocumentBatchRepository implements DocumentBatchRepository
{
    public function __construct(
        private readonly DocumentStorage $storage
    ) {
    }


    public function create(
        array $data
    ): array {

        $id = $data['id'];


        $batch = array_merge(
            [
                'id' => $id,
                'status' => 'pending',
                'created_at' => now()->toISOString(),
            ],
            $data
        );


        $this->storage->put(
            "batches/{$id}/batch.json",
            json_encode(
                $batch,
                JSON_THROW_ON_ERROR
            )
        );


        return $batch;
    }



    public function find(
        string $id
    ): ?array {

        $path = "batches/{$id}/batch.json";


        if (!$this->storage->exists($path)) {
            return null;
        }


        return json_decode(
            $this->storage->get($path),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }



    public function update(
        string $id,
        array $data
    ): void {

        $current = $this->find($id);


        if ($current === null) {
            throw new RuntimeException(
                "Batch {$id} not found"
            );
        }


        $this->create(
            array_merge(
                $current,
                $data,
                [
                    'updated_at'=>now()->toISOString()
                ]
            )
        );
    }



    public function delete(
        string $id
    ): void {

        $this->storage->deleteDirectory(
            "batches/{$id}"
        );
    }
}