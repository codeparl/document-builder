<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

/**
 * Interface DocumentStorage
 * Serves as the primary entry point for all filesystem operations within the Document Builder.
 * It abstracts the underlying storage mechanism (Local, S3, etc.) and provides methods for both 
 * general file management and isolated batch execution workspaces.
 */
interface DocumentStorage
{
    /**
     * Initializes an isolated storage workspace for a specific document generation batch.
     * This workspace abstracts away all path-building logic (e.g., chunks, manifests, rendered PDFs)
     * so that the pipeline queue workers do not need to manage directory structures.
     *
     * @param string $batchUuid The unique identifier for the document execution batch.
     * @return StorageWorkspaceInterface
     */
    public function batchWorkspace(string $batchUuid): StorageWorkspaceInterface;

    /**
     * Scopes the storage instance to a specific tenant and/or school context.
     * This is critical for queue workers or CLI commands where the contextual 
     * session (like an HTTP request) might not be automatically available.
     *
     * @param string|null $tenantId The unique identifier of the tenant.
     * @param string|null $schoolId The unique identifier of the school.
     * @return self Returns a cloned instance with the applied context.
     */
    public function forContext(?string $tenantId, ?string $schoolId): self;


    /**
 * Scopes the storage instance to a specific Laravel filesystem disk.
 *
 * Preserves the existing tenant and school context.
 *
 * @param string $disk The configured Laravel filesystem disk name.
 * @return self Returns a new storage instance using the selected disk.
 */
public function forDisk(string $disk): self;

    /**
     * Writes contents to a file at the specified relative path.
     * The path will be automatically resolved to the current tenant/school context.
     *
     * @param string $path The relative destination path (e.g., 'reports/final.pdf').
     * @param mixed $contents The file contents (string, stream, or binary data).
     * @return string The fully resolved path where the file was stored.
     */
    public function put(string $path, mixed $contents): string;

    /**
     * Retrieves the contents of a file at the specified relative path.
     *
     * @param string $path The relative path of the file to retrieve.
     * @return string The raw contents of the file.
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException If the file does not exist.
     */
    public function get(string $path): string;

    /**
     * Checks if a file exists at the specified relative path.
     *
     * @param string $path The relative path to check.
     * @return bool True if the file exists, false otherwise.
     */
    public function exists(string $path): bool;

    /**
     * Deletes a file at the specified relative path.
     *
     * @param string $path The relative path of the file to delete.
     * @return bool True on success, false on failure.
     */
    public function delete(string $path): bool;

    /**
     * Deletes a directory and all of its contents at the specified relative path.
     * The path will be automatically resolved to the current tenant/school context.
     *
     * @param string $directory The relative path of the directory to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteDirectory(string $directory): bool;

    /**
     * Copies a file from one relative path to another.
     *
     * @param string $from The relative path of the source file.
     * @param string $to The relative destination path.
     * @return bool True on success, false on failure.
     */
    public function copy(string $from, string $to): bool;

    /**
     * Moves (renames) a file from one relative path to another.
     *
     * @param string $from The relative path of the source file.
     * @param string $to The relative destination path.
     * @return bool True on success, false on failure.
     */
    public function move(string $from, string $to): bool;

    /**
     * Retrieves a publicly accessible URL for the file, if supported by the underlying disk.
     *
     * @param string $path The relative path of the file.
     * @return string|null The public URL, or null if the disk does not support public URLs.
     */
    public function publicUrl(string $path): ?string;


    public function resolvePath(string $path): string;

    public function putContent(
        string $path,
        DocumentContent $content
    ): string;

    /**
     * Return the physical filesystem path when supported.
     */
    public function physicalPath(
        string $path
    ): string;

    /**
     * Read a stored document as a stream.
     *
     * Useful for large files without loading
     * the whole file into memory.
     */
    public function readStream(
        string $path
    );



    /**
     * Get stored document size in bytes.
     */
    public function size(
        string $path
    ): ?int;
}
