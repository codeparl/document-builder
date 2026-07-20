<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Tests\Mocks;

use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\StorageWorkspaceInterface;

final class MockStorageWorkspace implements StorageWorkspaceInterface
{
    public function __construct(
        private readonly string $batchUuid,
        private readonly MockVirtualStorage $storage
    ) {
    }


    public function id(): string
    {
        return $this->batchUuid;
    }


    public function putPlan(array $plan): void
    {
        $this->storage->put(
            $this->path('plan.json'),
            json_encode($plan)
        );
    }


    public function plan(): array
    {
        return json_decode(
            $this->storage->get(
                $this->path('plan.json')
            ),
            true
        );
    }


    public function putStatus(array $status): void
    {
        $this->storage->put(
            $this->path('status.json'),
            json_encode($status)
        );
    }


    public function updateStatus(array $status): void
    {
        $current = [];

        if ($this->statusExists()) {
            $current = $this->status();
        }

        $this->putStatus(
            array_merge(
                $current,
                $status
            )
        );
    }


    public function status(): array
    {
        return json_decode(
            $this->storage->get(
                $this->path('status.json')
            ),
            true
        );
    }


    public function statusExists(): bool
    {
        return $this->storage->exists(
            $this->path('status.json')
        );
    }


    public function putChunk(
        int $chunk,
        mixed $data
    ): void {

        $this->storage->put(
            $this->path("chunks/{$chunk}.json"),
            json_encode($data)
        );
    }


    public function chunk(
        int $chunk
    ): mixed {

        return json_decode(
            $this->storage->get(
                $this->path("chunks/{$chunk}.json")
            ),
            true
        );
    }


    public function putRendered(
        int $chunk,
        mixed $contents,
        string $type
    ): void {

        $extension = $this->extension($type);

        $this->storage->put(
            $this->path(
                "rendered/{$chunk}.{$extension}"
            ),
            $contents
        );
    }


    public function renderedChunkPaths(
        string $type
    ): array {

        $extension = $this->extension($type);

        $paths = [];

        foreach ($this->storage->filesystem as $path => $content) {

            $prefix =
                $this->path('rendered/');


            if (
                str_starts_with($path, $prefix)
                &&
                str_ends_with($path, ".{$extension}")
            ) {

                $paths[] = $path;
            }
        }


        usort(
            $paths,
            function ($a, $b) {

                preg_match('/(\d+)\./', $a, $ma);
                preg_match('/(\d+)\./', $b, $mb);

                return ($ma[1] ?? 0)
                    <=>
                    ($mb[1] ?? 0);
            }
        );


        return $paths;
    }


    public function putFinal(
        mixed $contents,
        string $type
    ): void {

        $extension = $this->extension($type);

        $this->storage->put(
            $this->path(
                "final/document.{$extension}"
            ),
            $contents
        );
    }


    public function final(
        string $type
    ): string {

        $extension = $this->extension($type);

        return $this->storage->get(
            $this->path(
                "final/document.{$extension}"
            )
        );
    }


    public function cleanup(): void
    {
        $this->storage->deleteDirectory(
            $this->path('')
        );
    }


    private function path(string $path): string
    {
        return "batches/{$this->batchUuid}/{$path}";
    }


    private function extension(string $type): string
    {
        return match(strtolower($type)) {

            'pdf' => 'pdf',

            'xlsx',
            'spreadsheet',
            'excel' => 'xlsx',

            'docx',
            'word' => 'docx',

            'csv' => 'csv',

            default => $type,
        };
    }

    public function putManifest(array $manifest): void
{
    $this->storage->put(
        $this->path('manifest.json'),
        json_encode($manifest)
    );
}


public function manifest(): array
{
    return json_decode(
        $this->storage->get(
            $this->path('manifest.json')
        ),
        true
    );
}


public function renderedExists(
    int $chunk,
    string $type
): bool {

    $extension = $this->extension($type);

    return $this->storage->exists(
        $this->path(
            "rendered/{$chunk}.{$extension}"
        )
    );
}


/**
 * Legacy compatibility.
 *
 * Prefer putFinal() going forward.
 */
public function putMerged(
    mixed $contents,
    string $extension = 'pdf'
): void {

    $this->storage->put(
        $this->path(
            "merged/final.{$extension}"
        ),
        $contents
    );
}


/**
 * Legacy compatibility.
 *
 * Prefer final() going forward.
 */
public function merged(
    string $extension = 'pdf'
): string {

    return $this->storage->get(
        $this->path(
            "merged/final.{$extension}"
        )
    );
}
}