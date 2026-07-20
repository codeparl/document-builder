<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Facades;

use Illuminate\Support\Facades\Facade;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;

/**
 * @method static string put(string $path, string $contents, ?string $disk = null)
 * @method static string get(string $path, ?string $disk = null)
 * @method static bool exists(string $path, ?string $disk = null)
 * @method static bool delete(string $path, ?string $disk = null)
 * @method static bool deleteDirectory(string $path, ?string $disk = null)
 * @method static string temporaryPath(string $filename)
 * @method static string outputPath(string $filename)
 * @method static string|null url(string $path, ?string $disk = null)
 * @method static \UnnovateBrains\DocumentBuilder\Storage\LaravelDocumentStorage forContext(?string $tenantId, ?string $schoolId)
 * * @see \UnnovateBrains\DocumentBuilder\Storage\LaravelDocumentStorage
 */
class Storage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DocumentStorage::class;
    }
}