<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

final class ImageDriverResolver
{

    public static function manager(): ImageManager
    {

        $preferred =
            config(
                'document-builder.image.driver',
                'imagick'
            );


        if (
            $preferred === 'imagick'
            &&
            self::imagickAvailable()
        ) {

            return new ImageManager(
                new ImagickDriver()
            );
        }


        return new ImageManager(
            new GdDriver()
        );
    }

    public static function name(): string
    {
        return extension_loaded('imagick')
            ? 'imagick'
            : 'gd';
    }

    private static function imagickAvailable(): bool
    {
        return extension_loaded('imagick');
    }
}
