<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Class PipelineException
 *
 * Base exception thrown when a critical error occurs during the execution 
 * or traversal of the document generation pipeline stages.
 */
class PipelineException extends RuntimeException
{
    /**
     * Wrap a generic runtime error from a specific stage.
     */
    public static function stageFailed(string $stage, Throwable $previous): self
    {
        return new self(
            sprintf('Pipeline execution failed at stage [%s]: %s', $stage, $previous->getMessage()),
            0,
            $previous
        );
    }

    /**
     * Triggered when a context state variation is completely invalid.
     */
    public static function invalidContext(string $message): self
    {
        return new self(sprintf('Invalid pipeline context: %s', $message));
    }
}