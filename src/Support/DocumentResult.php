<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentExecutionResult;

/**
 * Represents the final result of a document execution.
 *
 * DocumentResult is the immutable artifact descriptor produced by:
 *
 * - GenerateDocumentStage for normal executions.
 * - MergeStage for chunked executions.
 *
 * It does not decide how document content is stored in memory.
 * The content is delegated to a DocumentContent implementation.
 *
 * Supported content representations:
 *
 * - StringContent
 *      For small documents where the binary content is already available.
 *
 * - StreamContent
 *      For large documents where reading the entire binary into memory
 *      would be expensive.
 *
 * Example:
 *
 * DocumentResult
 *      |
 *      +-- metadata
 *      +-- filename
 *      +-- storage path
 *      +-- DocumentContent
 *              |
 *              +-- StringContent
 *              |
 *              +-- StreamContent
 */
final class DocumentResult implements DocumentExecutionResult
{
    public function __construct(
        private readonly DocumentContent $content,
        private readonly string $path,
        private readonly string $filename,
        private readonly string $type,
        private readonly DocumentMetadata $metadata
    ) {}


    /**
     * Returns the document content representation.
     *
     * Consumers should not assume that content exists as a string.
     * Large documents may return a stream-based representation.
     */
    public function getContent(): DocumentContent
    {
        return $this->content;
    }


    /**
     * Indicates whether the document artifact is complete.
     *
     * A complete result means:
     *
     * - Generation has finished.
     * - The artifact can be persisted.
     * - OutputStage can safely write it to storage.
     */
    public function isComplete(): bool
    {
        return true;
    }


    /**
     * Final storage path.
     *
     * This path is already resolved by:
     *
     * - GenerateDocumentStage
     * - MergeStage
     *
     * OutputStage only persists the artifact.
     */
    public function getPath(): string
    {
        return $this->path;
    }


    /**
     * Original document filename.
     */
    public function getFilename(): string
    {
        return $this->filename;
    }


    /**
     * Document format type.
     *
     * Examples:
     *
     * - pdf
     * - xlsx
     * - docx
     */
    public function getType(): string
    {
        return $this->type;
    }


    /**
     * Metadata attached during document generation.
     *
     * Contains information such as:
     *
     * - execution details
     * - audit information
     * - generation timestamps
     * - custom metadata
     */
    public function getMetadata(): DocumentMetadata
    {
        return $this->metadata;
    }
}
