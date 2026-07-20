<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Tests\Mocks;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Contracts\StorageWorkspaceInterface;

final class MockVirtualStorage implements DocumentStorage
{
    /**
     * @var array<string, mixed>
     */
    public array $filesystem = [];
private ?string $tenantId = null;

private ?string $schoolId = null;

    /**
     * @var array<string, MockStorageWorkspace>
     */
    private array $workspaces = [];


    private function normalizePath(string $path): string
    {
        return ltrim(trim($path), '/');
    }


    public function put(string $path, mixed $contents): string
    {
        $normalized = $this->resolvePath($path);

        $this->filesystem[$normalized] = $contents;

        return $normalized;
    }


    public function get(string $path): string
    {
        return $this->filesystem[$this->resolvePath($path)] ?? '';
    }


    public function exists(string $path): bool
    {
        return array_key_exists(
            $this->resolvePath($path),
            $this->filesystem
        );
    }


    public function delete(string $path): bool
    {
        $path = $this->resolvePath($path);

        if (!isset($this->filesystem[$path])) {
            return false;
        }

        unset($this->filesystem[$path]);

        return true;
    }


    public function copy(string $from, string $to): bool
    {
        $from = $this->resolvePath($from);
        $to = $this->resolvePath($to);

        if (!isset($this->filesystem[$from])) {
            return false;
        }

        $this->filesystem[$to] = $this->filesystem[$from];

        return true;
    }


    public function move(string $from, string $to): bool
    {
        if (!$this->copy($from, $to)) {
            return false;
        }

        unset(
            $this->filesystem[$this->resolvePath($from)]
        );

        return true;
    }


    public function publicUrl(string $path): ?string
    {
        return 'https://virtual-storage.local/'
            .$this->resolvePath($path);
    }


    public function batchWorkspace(
        string $batchUuid
    ): StorageWorkspaceInterface {

        return $this->workspaces[$batchUuid]
            ??= new MockStorageWorkspace(
                $batchUuid,
                $this
            );
    }

    private function resolvePath(string $path): string
{
    $path = $this->normalizePath($path);

    if ($this->tenantId && $this->schoolId) {

        return "tenants/{$this->tenantId}/schools/{$this->schoolId}/{$path}";
    }

    return $path;
}

   public function forContext(
    ?string $tenantId,
    ?string $schoolId
): self {

    $clone = clone $this;

    $clone->tenantId = $tenantId;
    $clone->schoolId = $schoolId;

    return $clone;
}


    public function deleteDirectory(string $path): bool
    {
        $prefix = $this->resolvePath($path).'/';

        $deleted = false;

        foreach (array_keys($this->filesystem) as $file) {

            if (str_starts_with($file, $prefix)) {

                unset($this->filesystem[$file]);

                $deleted = true;
            }
        }

        return $deleted;
    }
}