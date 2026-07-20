<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Exceptions;

use Throwable;

final class RendererException extends DocumentException
{
    public static function viewNotFound(string $view, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('Failed to render document layout. View template [%s] could not be found or resolved.', $view),
            0,
            $previous
        );
    }

    public static function compilationFailed(string $view, string $reason, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('Engine error encountered while compiling layout view [%s]: %s', $view, $reason),
            0,
            $previous
        );
    }
}