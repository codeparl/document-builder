<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Storage;

use InvalidArgumentException;
use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContextResolver;

final class DocumentPathResolver
{
    private ?string $explicitTenantId = null;
    private ?string $explicitSchoolId = null;

    public function __construct(
        private readonly ?DocumentContextResolver $contextResolver = null,
    ) {}

    public function withContext(?string $tenantId, ?string $schoolId): self
    {
        $clone = clone $this;
        $clone->explicitTenantId = $tenantId;
        $clone->explicitSchoolId = $schoolId;

        return $clone;
    }

    /**
     * Resolve a storage-relative path into the active tenant/school namespace.
     *
     * @throws InvalidArgumentException when the path is malformed.
     * @throws RuntimeException when no tenant/school context is available,
     *                          or when a workspace path targets a different
     *                          tenant/school than the active context.
     */
    public function resolve(string $path): string
    {
        $path = ltrim($path, '/');

        if ($path === '') {
            throw new InvalidArgumentException('Storage path must not be empty.');
        }

        if (preg_match('#(^|/)\.\.(/|$)#', $path) === 1) {
            throw new InvalidArgumentException(
                "Storage path [{$path}] contains traversal segments."
            );
        }

        $context = $this->resolveIdentifiers();

        if ($context === null) {
            throw new RuntimeException(
                'Cannot resolve a storage path without an active tenant and school context.'
            );
        }

        [$tenantId, $schoolId] = $context;

        // Workspace paths are already tenant-scoped. Accept only when they
        // exactly match the active context — anything else is a cross-tenant
        // access attempt. This replaces the previous unconditional bypass.
        if (str_starts_with($path, 'secure-docs/')) {
            $expected = "secure-docs/tenants/{$tenantId}/schools/{$schoolId}/";

            if (! str_starts_with($path, $expected)) {
                throw new RuntimeException(
                    "Workspace path [{$path}] does not belong to the active tenant/school."
                );
            }

            return $path;
        }

        return "{$tenantId}/schools/{$schoolId}/{$path}";
    }

    public function resolveBatchPath(string $batchUuid): string
    {
        $context = $this->resolveIdentifiers();

        if ($context === null) {
            throw new RuntimeException(
                'Cannot resolve a batch workspace path without an active tenant and school context.'
            );
        }

        [$tenantId, $schoolId] = $context;

        return "secure-docs/tenants/{$tenantId}/schools/{$schoolId}/batches/{$batchUuid}";
    }

    private function resolveIdentifiers(): ?array
    {
        $tenantId = $this->explicitTenantId;
        $schoolId = $this->explicitSchoolId;

        if ($tenantId === null && $this->contextResolver !== null) {
            $tenantId = $this->contextResolver->tenantId();
        }

        if ($schoolId === null && $this->contextResolver !== null) {
            $schoolId = $this->contextResolver->schoolId();
        }

        if ($tenantId === null || $schoolId === null || $tenantId === '' || $schoolId === '') {
            return null;
        }

        return [$tenantId, $schoolId];
    }
}
