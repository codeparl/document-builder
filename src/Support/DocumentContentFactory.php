<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;

final class DocumentContentFactory
{
    /**
     * Maximum binary size kept in memory.
     */
    private int $memoryLimit;

    public function __construct(
        int $memoryLimit = 10 * 1024 * 1024 // 10MB
    ) {
        $this->memoryLimit = $memoryLimit;
    }

    /**
     * Convert any generated document output into a DocumentContent object.
     *
     * Supported input types:
     *
     * - DocumentContent
     * - Existing file path
     * - Stream resource
     * - Binary string
     */
    public function make(
        mixed $content,
        string $type = 'pdf',
        string $filename = 'document.pdf',
        string $extension = 'pdf',
        array $metadata = []
    ): DocumentContent {

        /*
        |--------------------------------------------------------------------------
        | Already normalized
        |--------------------------------------------------------------------------
        */
        if ($content instanceof DocumentContent) {
            return $content;
        }

        /*
        |--------------------------------------------------------------------------
        | Existing file
        |--------------------------------------------------------------------------
        */
        if (
            is_string($content)
            && is_file($content)
        ) {
            return new FileContent(
                path: $content,
                type: $type,
                filename: $filename,
                extension: $extension,
                metadata: $metadata
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Existing stream
        |--------------------------------------------------------------------------
        */
        if (is_resource($content)) {
            $stats = fstat($content);
            $size = $stats['size'] ?? null;

            return new StreamContent(
                stream: $content,
                size: $size,
                type: $type,
                filename: $filename,
                extension: $extension,
                metadata: $metadata
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Raw binary output
        |--------------------------------------------------------------------------
        */
        if (is_string($content)) {
            return $this->fromBinary(
                $content,
                $type,
                $filename,
                $extension,
                $metadata
            );
        }

        throw new RuntimeException(
            'Unsupported document content type: '
                . get_debug_type($content)
        );
    }

    /**
     * Decide whether binary content should stay in memory
     * or be converted into a stream.
     */
    private function fromBinary(
        string $binary,
        string $type,
        string $filename,
        string  $extension,
        array $metadata
    ): DocumentContent {
        $binaryLength = strlen($binary);

        /*
        |--------------------------------------------------------------------------
        | Small documents
        |--------------------------------------------------------------------------
        */
        if ($binaryLength <= $this->memoryLimit) {
            return new StringContent(
                content: $binary,
                type: $type,
                filename: $filename,
                extension: $extension,
                metadata: $metadata
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Large documents
        |--------------------------------------------------------------------------
        |
        | Move binary content into a temporary stream.
        | The pipeline will consume it without keeping
        | another large string copy in memory.
        |
        */
        $stream = tmpfile();

        if ($stream === false) {
            throw new RuntimeException(
                'Unable to create temporary document stream.'
            );
        }

        fwrite(
            $stream,
            $binary
        );

        rewind($stream);

        return new StreamContent(
            stream: $stream,
            size: $binaryLength,
            type: $type,
            filename: $filename,
            extension: $extension,
            metadata: $metadata
        );
    }
}
