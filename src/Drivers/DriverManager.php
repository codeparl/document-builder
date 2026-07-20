<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Drivers;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentDriver;
use UnnovateBrains\DocumentBuilder\Exceptions\EngineNotFoundException;

class DriverManager
{
    /**
     * Internal multi-dimensional driver storage mapping.
     * Format: [$type][$engine] => DocumentDriver
     *
     * @var array<string, array<string, DocumentDriver>>
     */
    protected array $drivers = [];

    public function register(DocumentDriver $driver): self
    {
        $type = strtolower($driver->type());
        $engine = strtolower($driver->engine());
        
        $this->drivers[$type][$engine] = $driver;

        return $this;
    }

    public function unregister(string $type, string $engine): self
    {
        unset($this->drivers[strtolower($type)][strtolower($engine)]);
        return $this;
    }

    public function has(string $type, ?string $engine = null): bool
    {
        $type = strtolower($type);
        if (! isset($this->drivers[$type])) {
            return false;
        }

        if ($engine === null) {
            return count($this->drivers[$type]) > 0;
        }

        return isset($this->drivers[$type][strtolower($engine)]);
    }

    public function driver(string $type, ?string $engine = null): DocumentDriver
    {
        $type = strtolower($type);
        
        if (! $this->has($type)) {
            throw EngineNotFoundException::forEngine($engine ?? 'default', $type);
        }

        // Default to the first registered engine driver for this type if none specified
       // Replace your old engine null check with this:
if ($engine === null || $engine === '' || strtolower($engine) === 'default') {
    $engine = (string) array_key_first($this->drivers[$type]);
}

        $engine = strtolower(trim($engine));

        if (! isset($this->drivers[$type][$engine])) {
            throw EngineNotFoundException::forEngine($engine, $type);
        }

        return $this->drivers[$type][$engine];
    }

    public function drivers(): array
    {
        return $this->drivers;
    }
}