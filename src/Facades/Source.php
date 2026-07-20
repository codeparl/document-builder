<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Facades;

use Illuminate\Support\Facades\Facade;
use UnnovateBrains\DocumentBuilder\Support\SourceFactory;

/**
 * @method static \UnnovateBrains\DocumentBuilder\Contracts\Source make(array $payload)
 *
 * @see \UnnovateBrains\DocumentBuilder\Support\SourceFactory
 */
final class Source extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SourceFactory::class;
    }
}
