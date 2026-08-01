<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Engines;

use Intervention\Image\Alignment;
use Intervention\Image\Direction;
use Intervention\Image\ImageManager;
use Intervention\Image\Encoders\AvifEncoder;
use Intervention\Image\Encoders\GifEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\FontFactory;
use Intervention\Image\Modifiers\InsertModifier;
use Picqer\Barcode\BarcodeGeneratorPNG;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;
use UnnovateBrains\DocumentBuilder\Contracts\ImageEngine;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Support\StringContent;


final class InterventionImageEngine implements ImageEngine
{

    public function __construct(
        private readonly ImageManager $manager
    ) {}



    public function name(): string
    {
        return 'intervention';
    }



    public function type(): string
    {
        return 'image';
    }



    public function supports(
        string $engine
    ): bool {

        return strtolower($engine) === $this->name();
    }




    public function render(
        ExecutionPlan $plan,
        string $content,
        PipelineContext $context
    ): DocumentContent {


        $config =
            $plan->getImageConfig()
            ?? [];


        /*
        |--------------------------------------------------------------------------
        | Load source
        |--------------------------------------------------------------------------
        */

        $image =
            $this->resolveSource(
                $content,
                $config
            );



        /*
        |--------------------------------------------------------------------------
        | Resize
        |--------------------------------------------------------------------------
        */

        if (
            isset($config['resize_width'])
            ||
            isset($config['resize_height'])
        ) {

            $image->resize(
                $config['resize_width'] ?? $image->width(),
                $config['resize_height'] ?? $image->height()
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Fit
        |--------------------------------------------------------------------------
        */

        if (
            isset($config['fit_width'])
            &&
            isset($config['fit_height'])
        ) {

            $image->cover(
                $config['fit_width'],
                $config['fit_height']
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Crop
        |--------------------------------------------------------------------------
        */

        if (
            isset($config['crop_width'])
            &&
            isset($config['crop_height'])
        ) {

            $image->crop(
                $config['crop_width'],
                $config['crop_height'],
                $config['crop_x'] ?? 0,
                $config['crop_y'] ?? 0
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Rotation
        |--------------------------------------------------------------------------
        */

        if (isset($config['rotate'])) {

            $image->rotate(
                $config['rotate']
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Flip
        |--------------------------------------------------------------------------
        */

        if ($config['flip_horizontal'] ?? false) {

            $image->flip(
                Direction::HORIZONTAL
            );
        }


        if ($config['flip_vertical'] ?? false) {

            $image->flip(
                Direction::VERTICAL
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        if ($config['grayscale'] ?? false) {

            $image->grayscale();
        }


        if (isset($config['brightness'])) {

            $image->brightness(
                $config['brightness']
            );
        }


        if (isset($config['contrast'])) {

            $image->contrast(
                $config['contrast']
            );
        }


        if (isset($config['blur'])) {

            $image->blur(
                $config['blur']
            );
        }


        if (isset($config['sharpen'])) {

            $image->sharpen(
                $config['sharpen']
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Text
        |--------------------------------------------------------------------------
        */

        if (
            isset($config['text'])
        ) {

            $image->text(
                $config['text'],

                $config['text_x'] ?? 50,

                $config['text_y'] ?? 50,

                function (FontFactory $font) use ($config) {


                    if (
                        isset($config['font_file'])
                    ) {

                        $font->filename(
                            $config['font_file']
                        );
                    }


                    if (
                        isset($config['font_size'])
                    ) {

                        $font->size(
                            $config['font_size']
                        );
                    }


                    if (
                        isset($config['font_color'])
                    ) {

                        $font->color(
                            $config['font_color']
                        );
                    }
                }
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Image Watermark
        |--------------------------------------------------------------------------
        */

        $this->applyWatermark(
            $image,
            $config
        );



        /*
        |--------------------------------------------------------------------------
        | Encoding
        |--------------------------------------------------------------------------
        */

        $format =
            strtolower(
                $config['convert']
                    ??
                    config(
                        'document-builder.drivers.image.extension',
                        'png'
                    )
            );


        $plan->setExtension(
            $format
        );


        $quality =
            (int)($config['quality'] ?? 90);



        $encoded =
            match ($format) {


                'jpg',
                'jpeg'
                =>
                $image->encode(
                    new JpegEncoder(
                        quality: $quality
                    )
                ),


                'webp'
                =>
                $image->encode(
                    new WebpEncoder(
                        quality: $quality
                    )
                ),


                'avif'
                =>
                $image->encode(
                    new AvifEncoder(
                        quality: $quality
                    )
                ),


                'gif'
                =>
                $image->encode(
                    new GifEncoder()
                ),


                default
                =>
                $image->encode(
                    new PngEncoder()
                ),
            };



        return new StringContent(

            content: $encoded->toString(),

            type: 'image',

            filename: ($plan->getOutputFilename() ?? 'image')
                . '.'
                . $format,


            metadata: $plan->getMetadata()?->toArray()
                ?? [],


            extension: $format
        );
    }





    private function resolveSource(
        string $content,
        array $config
    ): ImageInterface {


        $source =
            $config['source']
            ?? null;


        if (
            is_array($source)
            &&
            isset($source['type'])
        ) {


            return match ($source['type']) {


                'file'
                =>
                $this->manager->decodePath(
                    $source['value']
                ),


                'url'
                =>
                $this->manager->decodeBinary(
                    file_get_contents(
                        $source['value']
                    )
                ),


                'base64'
                =>
                $this->manager->decodeBase64(
                    $source['value']
                ),


                'svg'
                =>
                $this->manager->decode(
                    $source['value']
                ),


                'text'
                =>
                $this->createTextImage(
                    $source['value'],
                    $config
                ),
                'qrcode'
                =>
                $this->createQrCode(
                    $source['value'],
                    $config
                ),


                'barcode'
                =>
                $this->createBarcode(
                    $source['value'],
                    $config
                ),


                default
                =>
                $this->manager->decodeBinary(
                    $content
                )
            };
        }



        if (is_file($content)) {

            return $this->manager->decodePath(
                $content
            );
        }


        return $this->manager->decodeBinary(
            $content
        );
    }


    private function createQrCode(
        string $data,
        array $config
    ): ImageInterface {


        $png =
            QrCode::format('png')
            ->size(
                $config['qr_size'] ?? 300
            )
            ->margin(
                $config['qr_margin'] ?? 10
            )
            ->generate(
                $data
            );


        return $this->manager->decode(
            $png
        );
    }


    private function createBarcode(
        string $data,
        array $config
    ): ImageInterface {


        $generator =
            new BarcodeGeneratorPNG();



        $format =
            match ($config['barcode_format'] ?? 'CODE128') {

                'EAN13'
                =>
                $generator::TYPE_EAN_13,


                'EAN8'
                =>
                $generator::TYPE_EAN_8,


                'UPC'
                =>
                $generator::TYPE_UPC_A,


                default
                =>
                $generator::TYPE_CODE_128,
            };



        $png =
            $generator->getBarcode(
                $data,
                $format,
                $config['barcode_width'] ?? 2,
                $config['barcode_height'] ?? 80
            );


        return $this->manager->decode(
            $png
        );
    }


    private function createTextImage(
        string $text,
        array $config
    ): ImageInterface {

        $image =
            $this->manager->createImage(
                $config['width'] ?? 800,
                $config['height'] ?? 400
            );

        /*
    |--------------------------------------------------------------------------
    | Background
    |--------------------------------------------------------------------------
    */

        $image->fill(
            $config['background'] ?? '#ffffff'
        );


        $image->text(

            $text,

            $config['text_x'] ?? 50,

            $config['text_y'] ?? 50,


            function (FontFactory $font) use ($config) {

                /*
    |--------------------------------------------------------------------------
    | Resolve font
    |--------------------------------------------------------------------------
    */

                $fontPath =
                    $config['font_file']
                    ?? $this->defaultFontPath();


                if (
                    $fontPath
                    &&
                    file_exists($fontPath)
                ) {

                    $font->filepath(
                        $fontPath
                    );
                }


                /*
    |--------------------------------------------------------------------------
    | Font size
    |--------------------------------------------------------------------------
    */

                if (isset($config['font_size'])) {

                    $font->size(
                        (float) $config['font_size']
                    );
                }


                /*
    |--------------------------------------------------------------------------
    | Font color
    |--------------------------------------------------------------------------
    */

                if (isset($config['font_color'])) {

                    $font->color(
                        $config['font_color']
                    );
                }
            }
        );


        return $image;
    }





    private function applyWatermark(
        ImageInterface $image,
        array $config
    ): void {


        if (
            empty($config['watermark'])
            ||
            !is_file($config['watermark'])
        ) {

            return;
        }



        $watermark =
            $this->manager->decodePath(
                $config['watermark']
            );



        if (
            isset($config['watermark_width'])
            &&
            isset($config['watermark_height'])
        ) {

            $watermark->resize(
                $config['watermark_width'],
                $config['watermark_height']
            );
        }



        $image->modify(

            new InsertModifier(

                image: $watermark,

                x: (int)($config['watermark_x'] ?? 10),

                y: (int)($config['watermark_y'] ?? 10),


                alignment: $this->resolveAlignment(
                    $config['watermark_position']
                        ??
                        'bottom-right'
                ),


                transparency: 1 -
                    (
                        ($config['watermark_opacity'] ?? 100)
                        / 100
                    )
            )
        );
    }



    private function defaultFontPath(): ?string
    {
        $path = dirname(__DIR__, 2)
            . '/resources/fonts/Roboto-Regular.ttf';



        return file_exists($path)
            ? $path
            : null;
    }

    private function resolveAlignment(
        string $position
    ): Alignment {


        return match ($position) {

            'top-left'
            => Alignment::TOP_LEFT,


            'top-right'
            => Alignment::TOP_RIGHT,


            'bottom-left'
            => Alignment::BOTTOM_LEFT,


            'bottom-right'
            => Alignment::BOTTOM_RIGHT,


            'center'
            => Alignment::CENTER,


            default
            => Alignment::BOTTOM_RIGHT,
        };
    }
}
