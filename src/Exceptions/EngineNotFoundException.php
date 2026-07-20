<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Exceptions;

/**
 * Class EngineNotFoundException
 *
 * Thrown when the dynamic driver resolution registry cannot locate a structural compilation
 * driver matching the developer's requested format type or vendor rendering engine.
 *
 * @package UnnovateBrains\DocumentBuilder\Exceptions
 */
final class EngineNotFoundException extends DocumentException
{
    /**
     * Create a new exception instance for an unresolvable engine/type combination.
     *
     * @param string $engine The name of the missing or unmapped rendering engine.
     * @param string $type The document type format requested (e.g., 'pdf', 'excel').
     * @return self
     */
    public static function forEngine(string $engine, string $type): self
    {
        return new self(sprintf(
            'The document rendering engine [%s] could not be resolved for format type [%s]. ' .
            'Verify that the required driver is registered within your DriverManager container.',
            $engine,
            $type
        ));
    }
}