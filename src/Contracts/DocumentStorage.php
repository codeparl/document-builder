<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

/**
 * Tenant-scoped, disk-agnostic document storage.
 *
 * All paths accepted by this interface are **storage-relative**. They are
 * resolved internally against the active tenant/school context and disk.
 * Callers MUST NOT pass pre-resolved paths.
 *
 * Implementations MUST be immutable with respect to context: forContext()
 * and forDisk() return new instances and never mutate the receiver.
 *
 * Implementations MUST be streaming-capable: readStream(), writeStream(),
 * putFromPath() and copyToPath() are required. Any adapter that cannot
 * honour them cannot satisfy this contract.
 */
interface DocumentStorage
{
    // ---------------------------------------------------------------- context

    public function forContext(?string $tenantId, ?string $schoolId): static;

    public function forDisk(string $disk): static;

    public function currentDisk(): string;

    // -------------------------------------------------------------- metadata

    public function exists(string $path): bool;

    public function size(string $path): ?int;

    // ---------------------------------------------------------------- read

    /**
     * Read the entire file into memory.
     *
     * Convenience wrapper. Prefer readStream() for anything that may exceed
     * a few MB. Throws when the file does not exist.
     */
    public function get(string $path): string;

    /**
     * Open a read stream. The caller owns the stream and MUST close it.
     *
     * @return resource
     */
    public function readStream(string $path): mixed;

    // --------------------------------------------------------------- write

    /**
     * Store the given contents, replacing any existing file.
     *
     * Convenience wrapper. Prefer writeStream() for large payloads.
     * The $path is storage-relative and round-trips through exists()/get().
     */
    public function put(string $path, mixed $contents): void;

    /**
     * Write from a caller-owned readable stream. The caller retains
     * ownership of the stream and is responsible for closing it.
     *
     * @param resource $stream
     */
    public function writeStream(string $path, mixed $stream): void;

    /**
     * Upload a local file by path. Must stream — never load the file into
     * memory as a string.
     */
    public function putFromPath(string $path, string $localPath): void;

    /**
     * Stream a stored file down to a local path. Must stream — never
     * materialize the file as a string.
     */
    public function copyToPath(string $path, string $localPath): void;

    // -------------------------------------------------------------- delete

    public function delete(string $path): bool;

    public function deleteDirectory(string $directory): bool;

    // ---------------------------------------------------------- copy / move

    public function copy(string $from, string $to): bool;

    public function move(string $from, string $to): bool;

    // ---------------------------------------------------------------- urls

    public function publicUrl(string $path): ?string;

    // -------------------------------------------- document-builder extensions

    /**
     * Initialize an isolated storage workspace for a document generation batch.
     */
    public function batchWorkspace(string $batchUuid): StorageWorkspaceInterface;

    /**
     * Write a DocumentContent payload to a path.
     */
    public function putContent(string $path, DocumentContent $content): void;

    /**
     * Return the physical filesystem path when the disk supports it, or
     * null otherwise (e.g. S3).
     */
    public function physicalPath(string $path): ?string;
}
