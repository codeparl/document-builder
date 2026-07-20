<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Exceptions;

final class InvalidDocumentTypeException extends DocumentException
{
    public static function unsupported(string $type, array $supported): self
    {
        return new self(sprintf(
            'The requested document format type [%s] is unsupported. Supported profiles are: [%s].',
            $type,
            implode(', ', $supported)
        ));
    }
}