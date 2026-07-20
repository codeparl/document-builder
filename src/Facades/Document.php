<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Facades;

use Illuminate\Support\Facades\Facade;
use UnnovateBrains\DocumentBuilder\DocumentBuilder;
use UnnovateBrains\DocumentBuilder\DocumentManager;

/**
 * Document Builder Facade
 *
 * @method static DocumentBuilder pdf()
 * @method static DocumentBuilder excel()
 * @method static DocumentBuilder csv()
 *
 * Batch helpers:
 *
 * @method static array status(string $batchId)
 * @method static void updateStatus(string $batchId, array $status)
 * @method static array plan(string $batchId)
 * @method static array chunks(string $batchId, string $type = 'pdf')
 * @method static string final(string $batchId, string $type = 'pdf')
 * @method static string merged(string $batchId, string $type = 'pdf')
 * @method static void cleanup(string $batchId)
 *
 * @see DocumentManager
 */
class Document extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DocumentManager::class;
    }


    /**
     * Start a fluent PDF builder session.
     */
    public static function pdf(): DocumentBuilder
    {
        return new DocumentBuilder('pdf');
    }


    public static function excel(): DocumentBuilder
    {
        return new DocumentBuilder('excel');
    }


    public static function csv(): DocumentBuilder
    {
        return new DocumentBuilder('csv');
    }
}
