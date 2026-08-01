<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Storage;

use Illuminate\Contracts\Filesystem\Factory as StorageFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Contracts\StorageWorkspaceInterface;

/**
 * Class LaravelDocumentStorage
 * * The concrete implementation of DocumentStorage. 
 * It manages the underlying Laravel Filesystem and ensures all file operations
 * are strictly scoped to the correct tenant and school using the DocumentPathResolver.
 */
final class LaravelDocumentStorage implements DocumentStorage
{
    /**
     * @param StorageFactory $storageFactory Laravel's storage manager.
     * @param DocumentPathResolver $pathResolver Resolves relative paths to tenant-isolated paths.
     * @param string $defaultDisk The default filesystem disk to use (e.g., 'local', 's3').
     */
    public function __construct(
        private readonly StorageFactory $storageFactory,
        private readonly DocumentPathResolver $pathResolver,
        private readonly string $defaultDisk = 'local'
    ) {}

    /**
     * @inheritDoc
     */
    public function forContext(?string $tenantId, ?string $schoolId): self
    {
        // We return a new instance with a cloned and updated path resolver
        // to ensure context changes do not mutate the original instance during async jobs.
        return new self(
            $this->storageFactory,
            $this->pathResolver->withContext($tenantId, $schoolId),
            $this->defaultDisk
        );
    }

    /**
     * @inheritDoc
     */
    public function batchWorkspace(string $batchUuid): StorageWorkspaceInterface
    {
        // Use the resolver to get the isolated batch directory
        $basePath = $this->pathResolver->resolveBatchPath($batchUuid);

        return new LocalStorageWorkspace(
            $this->getDisk(),
            $batchUuid,
            $basePath
        );
    }

    /**
     * @inheritDoc
     */
    public function put(string $path, mixed $contents): string
    {
        $resolvedPath = $this->pathResolver->resolve($path);
        $this->getDisk()->put($resolvedPath, $contents);

        return $resolvedPath;
    }

    /**
     * @inheritDoc
     */
    public function get(string $path): string
    {
        return $this->getDisk()->get($this->pathResolver->resolve($path));
    }

    /**
     * @inheritDoc
     */
    public function exists(string $path): bool
    {
        return $this->getDisk()->exists($this->pathResolver->resolve($path));
    }

    /**
     * Open a read stream from storage.
     *
     * Supports local disks, S3, etc.
     */
    public function readStream(
        string $path
    ) {
        return $this->getDisk()->readStream(
            $path
        );
    }


    /**
     * Get file size in bytes.
     */
    public function size(
        string $path
    ): ?int {

        $resolved =
            $this->pathResolver->resolve($path);


        $disk =
            $this->getDisk();


        if (! $disk->exists($resolved)) {
            return null;
        }


        return $disk->size($resolved);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $path): bool
    {
        return $this->getDisk()->delete($this->pathResolver->resolve($path));
    }

    /**
     * @inheritDoc
     */
    public function deleteDirectory(string $path): bool
    {
        return $this->getDisk()->deleteDirectory($this->pathResolver->resolve($path));
    }

    /**
     * @inheritDoc
     */
    public function copy(string $from, string $to): bool
    {
        return $this->getDisk()->copy(
            $this->pathResolver->resolve($from),
            $this->pathResolver->resolve($to)
        );
    }

    /**
     * @inheritDoc
     */
    public function move(string $from, string $to): bool
    {
        return $this->getDisk()->move(
            $this->pathResolver->resolve($from),
            $this->pathResolver->resolve($to)
        );
    }

    /**
     * @inheritDoc
     */
    public function publicUrl(string $path): ?string
    {
        $resolvedPath = $this->pathResolver->resolve($path);
        $disk = $this->getDisk();

        return method_exists($disk, 'url') ? $disk->url($resolvedPath) : null;
    }

    public function resolvePath(
        string $path
    ): string {
        return $this->pathResolver->resolve($path);
    }



    /**
     * Retrieves the configured Laravel filesystem instance.
     * * @return Filesystem
     */
    private function getDisk(): Filesystem
    {
        return $this->storageFactory->disk($this->defaultDisk);
    }

    public function putContent(
        string $path,
        DocumentContent $content
    ): string {

        $resolved = $this->pathResolver->resolve($path);


        $content->writeTo(
            function ($data) use ($resolved) {

                $this->getDisk()->put(
                    $resolved,
                    $data
                );
            }
        );


        return $resolved;
    }

    /**
     * Return the physical filesystem path when supported.
     */
    public function physicalPath(
        string $path
    ): string {

        $resolved =
            $this->resolvePath($path);

        $disk =
            $this->getDisk();

        if (method_exists($disk, 'path')) {

            return $disk->path(
                $resolved
            );
        }

        return $resolved;
    }
}
