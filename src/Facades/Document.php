<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Facades;

use Illuminate\Support\Facades\Facade;
use RuntimeException;
use UnnovateBrains\DocumentBuilder\DocumentBuilder;
use UnnovateBrains\DocumentBuilder\DocumentManager;

/**
 * @method static DocumentBuilder pdf()
 * @method static DocumentBuilder excel()
 * @method static DocumentBuilder csv()
 * @method static DocumentBuilder word()
 * @method static DocumentBuilder html()
 * @method static DocumentBuilder image()
 *
 * @see DocumentManager
 */
class Document extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DocumentManager::class;
    }



    public static function pdf(): DocumentBuilder
    {
        return static::builder('pdf');
    }



    public static function excel(): DocumentBuilder
    {
        return static::builder('xlsx');
    }



    public static function csv(): DocumentBuilder
    {
        return static::builder('csv');
    }



    public static function word(): DocumentBuilder
    {
        return static::builder('docx');
    }



    public static function html(): DocumentBuilder
    {
        return static::builder('html');
    }



    public static function image(): DocumentBuilder
    {
        return static::builder('png');
    }



    /**
     * Create document builder with configured default engine.
     */
    protected static function builder(
        string $type
    ): DocumentBuilder {


        $engine =
            config(
                "document-builder.drivers.{$type}.engine"
            );



        if (!$engine) {

            throw new RuntimeException(
                "No default engine configured for document type [{$type}]"
            );
        }



        return new DocumentBuilder(
            type: $type,
            engine: $engine
        );
    }
}
