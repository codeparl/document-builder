<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Exceptions;

final class StorageException extends DocumentException
{
    public static function writeFailed(string $path, string $reason = ''): self
    {
        return new self(sprintf(
            'Storage write operation failed at target path [%s]. Reason: %s',
            $path,
            $reason ?: 'Unknown disk error.'
        ));
    }

    public static function fileNotFound(string $path): self
    {
        return new self(sprintf('Requested document asset not found at path [%s].', $path));
    }
}