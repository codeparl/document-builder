<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\Source;

final class SourceRegistry
{
    /**
     * @var array<string,class-string<Source>>
     */
    private array $sources = [];


    /**
     * Register a source type.
     */
    public function register(
        string $type,
        string $sourceClass
    ): void {

        if (
            !is_subclass_of(
                $sourceClass,
                Source::class
            )
        ) {
            throw new RuntimeException(
                "{$sourceClass} must implement Source"
            );
        }


        $this->sources[$type] = $sourceClass;
    }



    /**
     * Determine if source exists.
     */
    public function has(
        string $type
    ): bool {

        return isset(
            $this->sources[$type]
        );
    }



    /**
     * Get source class.
     */
    public function get(
        string $type
    ): string {

        if (!$this->has($type)) {

            throw new RuntimeException(
                "Source {$type} is not registered."
            );
        }


        return $this->sources[$type];
    }



    /**
     * Get all registered sources.
     */
    public function all(): array
    {
        return $this->sources;
    }
}