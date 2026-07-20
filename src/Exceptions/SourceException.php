<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Exceptions;

final class SourceException extends DocumentException
{
    public static function unresolvable(string $reason): self
    {
        return new self(sprintf('Data source validation failed: %s', $reason));
    }

    public static function emptySource(): self
    {
        return new self('The provided data source contains zero elements and cannot be processed.');
    }
}