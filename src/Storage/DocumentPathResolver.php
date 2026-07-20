<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Storage;

use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContextResolver;

/**
 * Class DocumentPathResolver
 * * Centralizes all path string manipulation to enforce strict tenant and school isolation.
 * By keeping this logic here, the rest of the storage layer never has to manually 
 * concatenate tenant IDs into directory strings.
 */
final class DocumentPathResolver
{
    private ?string $explicitTenantId = null;
    private ?string $explicitSchoolId = null;

    /**
     * @param DocumentContextResolver $contextResolver Resolves the automatic runtime context (e.g., from Auth/Session).
     */
    public function __construct(
         private readonly ?DocumentContextResolver $contextResolver = null
    ) {}

    /**
     * Overrides the automatic context with explicit identifiers.
     * Crucial for background queue workers that do not have an active HTTP session.
     *
     * @param string|null $tenantId
     * @param string|null $schoolId
     * @return self A cloned instance with the explicitly applied context.
     */
    public function withContext(?string $tenantId, ?string $schoolId): self
    {
        $clone = clone $this;
        $clone->explicitTenantId = $tenantId;
        $clone->explicitSchoolId = $schoolId;
        
        return $clone;
    }

    /**
     * Resolves general destination paths into the tenant/school namespace.
     * * @param string $path The requested relative path (e.g., 'reports/final.pdf').
     * @return string The fully qualified isolated path.
     */
 public function resolve(string $path): string
{
    $path = ltrim($path, '/');


    if (
        str_starts_with($path, 'tenants/')
        || str_starts_with($path, 'secure-docs/')
    ) {
        return $path;
    }


    $context = $this->resolveIdentifiers();


    if ($context === null) {
        return $path;
    }


    [$tenantId, $schoolId] = $context;


    return "tenants/{$tenantId}/schools/{$schoolId}/{$path}";
}

    /**
     * Resolves the base root directory for an isolated batch workspace.
     * Note: Internal batch routing (chunks, rendered files) is handled by the Workspace class.
     *
     * @param string $batchUuid The unique execution batch identifier.
     * @return string The isolated base path for the workspace.
     */
  public function resolveBatchPath(string $batchUuid): string
{
    $context = $this->resolveIdentifiers();


    if ($context === null) {
        return "batches/{$batchUuid}";
    }


    [$tenantId, $schoolId] = $context;


    return "secure-docs/tenants/{$tenantId}/schools/{$schoolId}/batches/{$batchUuid}";
}

    /**
     * Retrieves the active tenant and school identifiers.
     *
     * @return array{0: string, 1: string} A tuple containing [tenantId, schoolId].
     * @throws RuntimeException If either identifier cannot be resolved.
     */
 private function resolveIdentifiers(): ?array
{
    $tenantId = $this->explicitTenantId;

    $schoolId = $this->explicitSchoolId;


    if ($tenantId === null && $this->contextResolver) {
        $tenantId = $this->contextResolver->tenantId();
    }


    if ($schoolId === null && $this->contextResolver) {
        $schoolId = $this->contextResolver->schoolId();
    }


    if (!$tenantId || !$schoolId) {
        return null;
    }


    return [
        (string) $tenantId,
        (string) $schoolId
    ];
}
}