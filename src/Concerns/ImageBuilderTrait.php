<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Concerns;

trait ImageBuilderTrait
{

    /*
    |--------------------------------------------------------------------------
    | Canvas & Dimensions
    |--------------------------------------------------------------------------
    */


    /**
     * Set output image width.
     */
    public function width(
        int $width
    ): self {

        return $this->setDriverOption(
            'image',
            'width',
            $width
        );
    }



    /**
     * Set output image height.
     */
    public function height(
        int $height
    ): self {

        return $this->setDriverOption(
            'image',
            'height',
            $height
        );
    }



    /**
     * Define image dimensions.
     */
    public function size(
        int $width,
        int $height
    ): self {

        return $this->setDriverOptions(
            'image',
            [
                'width' => $width,
                'height' => $height,
            ]
        );
    }



    /**
     * Create blank image canvas.
     *
     * Useful for:
     * - Certificates
     * - ID cards
     * - Posters
     * - Generated reports
     */
    public function createImage(
        int $width,
        int $height
    ): self {

        return $this->setDriverOption(
            'image',
            'source',
            [
                'type' => 'canvas',
                'width' => $width,
                'height' => $height,
            ]
        );
    }



    /**
     * Set canvas size.
     */
    public function canvas(
        int $width,
        int $height
    ): self {

        return $this->setDriverOptions(
            'image',
            [
                'canvas_width' => $width,
                'canvas_height' => $height,
            ]
        );
    }



    /**
     * Set background colour.
     */
    public function background(
        string $color
    ): self {

        return $this->setDriverOption(
            'image',
            'background',
            $color
        );
    }



    /**
     * Enable transparent background.
     */
    public function transparent(
        bool $enabled = true
    ): self {

        return $this->setDriverOption(
            'image',
            'transparent',
            $enabled
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Source
    |--------------------------------------------------------------------------
    */


    /**
     * Load image from local file.
     *
     * Example:
     *
     * ->fromImage('/storage/photo.jpg')
     */
    public function fromImage(
        string $path
    ): self {

        return $this->setDriverOption(
            'image',
            'source',
            [
                'type' => 'file',
                'value' => $path,
            ]
        );
    }



    /**
     * Load image from remote URL.
     */
    public function fromUrl(
        string $url
    ): self {

        return $this->setDriverOption(
            'image',
            'source',
            [
                'type' => 'url',
                'value' => $url,
            ]
        );
    }



    /**
     * Load image from Base64 data.
     */
    public function fromBase64(
        string $data
    ): self {

        return $this->setDriverOption(
            'image',
            'source',
            [
                'type' => 'base64',
                'value' => $data,
            ]
        );
    }



    /**
     * Load SVG image.
     */
    public function fromSvg(
        string $svg
    ): self {

        return $this->setDriverOption(
            'image',
            'source',
            [
                'type' => 'svg',
                'value' => $svg,
            ]
        );
    }



    /**
     * Generate image from text.
     *
     * Mainly useful for:
     * - Testing
     * - Placeholder images
     * - Simple banners
     */
    public function fromText(
        string $text
    ): self {

        return $this->setDriverOption(
            'image',
            'source',
            [
                'type' => 'text',
                'value' => $text,
            ]
        );
    }



    /**
     * Alias for fromText().
     */
    public function from(
        string $text
    ): self {

        return $this->fromText($text);
    }



    /*
    |--------------------------------------------------------------------------
    | Resize & Scaling
    |--------------------------------------------------------------------------
    */


    /**
     * Resize image.
     */
    public function resize(
        int $width,
        int $height
    ): self {

        return $this->setDriverOptions(
            'image',
            [
                'resize_width' => $width,
                'resize_height' => $height,
            ]
        );
    }



    /**
     * Fit image inside dimensions.
     */
    public function fit(
        int $width,
        int $height
    ): self {

        return $this->setDriverOptions(
            'image',
            [
                'fit_width' => $width,
                'fit_height' => $height,
            ]
        );
    }



    /**
     * Contain image inside canvas.
     */
    public function contain(
        int $width,
        int $height
    ): self {

        return $this->setDriverOptions(
            'image',
            [
                'contain_width' => $width,
                'contain_height' => $height,
            ]
        );
    }



    /**
     * Scale image.
     */
    public function scale(
        float $factor
    ): self {

        return $this->setDriverOption(
            'image',
            'scale',
            $factor
        );
    }



    /**
     * Preserve aspect ratio.
     */
    public function keepAspectRatio(
        bool $enabled = true
    ): self {

        return $this->setDriverOption(
            'image',
            'keep_aspect_ratio',
            $enabled
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Crop
    |--------------------------------------------------------------------------
    */


    public function crop(
        int $width,
        int $height
    ): self {

        return $this->setDriverOptions(
            'image',
            [
                'crop_width' => $width,
                'crop_height' => $height,
            ]
        );
    }



    public function cropPosition(
        int $x,
        int $y
    ): self {

        return $this->setDriverOptions(
            'image',
            [
                'crop_x' => $x,
                'crop_y' => $y,
            ]
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Rotation
    |--------------------------------------------------------------------------
    */


    public function rotate(
        float $degrees
    ): self {

        return $this->setDriverOption(
            'image',
            'rotate',
            $degrees
        );
    }



    public function flipHorizontal(): self
    {
        return $this->setDriverOption(
            'image',
            'flip_horizontal',
            true
        );
    }



    public function flipVertical(): self
    {
        return $this->setDriverOption(
            'image',
            'flip_vertical',
            true
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Quality & Compression
    |--------------------------------------------------------------------------
    */


    public function quality(
        int $quality
    ): self {

        return $this->setDriverOption(
            'image',
            'quality',
            $quality
        );
    }



    public function optimize(
        bool $enabled = true
    ): self {

        return $this->setDriverOption(
            'image',
            'optimize',
            $enabled
        );
    }



    public function progressive(
        bool $enabled = true
    ): self {

        return $this->setDriverOption(
            'image',
            'progressive',
            $enabled
        );
    }



    public function stripMetadata(
        bool $enabled = true
    ): self {

        return $this->setDriverOption(
            'image',
            'strip_metadata',
            $enabled
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Conversion
    |--------------------------------------------------------------------------
    */


    /**
     * Convert output format.
     *
     * Example:
     *
     * ->convert('webp')
     */
    public function convert(
        string $type
    ): self {

        return $this->setDriverOption(
            'image',
            'convert',
            strtolower($type)
        );
    }



    /**
     * Set generated filename.
     */
    public function filename(
        string $name
    ): self {

        return $this->setDriverOption(
            'image',
            'filename',
            $name
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Watermark
    |--------------------------------------------------------------------------
    */


    public function watermark(
        string $path
    ): self {

        return $this->setDriverOption(
            'image',
            'watermark',
            $path
        );
    }



    public function watermarkOpacity(
        int $opacity
    ): self {

        return $this->setDriverOption(
            'image',
            'watermark_opacity',
            $opacity
        );
    }



    public function watermarkPosition(
        string $position
    ): self {

        return $this->setDriverOption(
            'image',
            'watermark_position',
            $position
        );
    }



    public function watermarkOffset(
        int $x,
        int $y
    ): self {

        return $this->setDriverOptions(
            'image',
            [
                'watermark_x' => $x,
                'watermark_y' => $y,
            ]
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Text Rendering
    |--------------------------------------------------------------------------
    */


    public function text(
        string $text
    ): self {

        return $this->setDriverOption(
            'image',
            'text',
            $text
        );
    }



    /**
     * Configure text rendering.
     *
     * Example:
     *
     * [
     *  'x'=>100,
     *  'y'=>50,
     *  'size'=>40,
     *  'color'=>'#000000'
     * ]
     */
    public function textOptions(
        array $options
    ): self {

        return $this->setDriverOption(
            'image',
            'text_options',
            $options
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */


    public function grayscale(): self
    {
        return $this->setDriverOption(
            'image',
            'grayscale',
            true
        );
    }



    public function sepia(): self
    {
        return $this->setDriverOption(
            'image',
            'sepia',
            true
        );
    }



    public function blur(
        int $amount = 5
    ): self {

        return $this->setDriverOption(
            'image',
            'blur',
            $amount
        );
    }



    public function sharpen(
        int $amount = 10
    ): self {

        return $this->setDriverOption(
            'image',
            'sharpen',
            $amount
        );
    }



    public function brightness(
        int $level
    ): self {

        return $this->setDriverOption(
            'image',
            'brightness',
            $level
        );
    }



    public function contrast(
        int $level
    ): self {

        return $this->setDriverOption(
            'image',
            'contrast',
            $level
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Raw Configuration
    |--------------------------------------------------------------------------
    */


    public function imageConfig(
        array $config
    ): self {

        return $this->setDriverOptions(
            'image',
            $config
        );
    }


    public function font(
        string $font
    ): self {
        return $this->setDriverOption(
            'image',
            'font_file',
            $font
        );
    }


    public function fontSize(
        int $size
    ): self {
        return $this->setDriverOption(
            'image',
            'font_size',
            $size
        );
    }


    public function fontColor(
        string $color
    ): self {
        return $this->setDriverOption(
            'image',
            'font_color',
            $color
        );
    }


    /*
|--------------------------------------------------------------------------
| QR Code
|--------------------------------------------------------------------------
*/


    public function qrCode(
        string $data
    ): self {

        return $this->setDriverOption(
            'image',
            'source',
            [
                'type' => 'qrcode',
                'value' => $data,
            ]
        );
    }


    public function qrSize(
        int $size
    ): self {

        return $this->setDriverOption(
            'image',
            'qr_size',
            $size
        );
    }


    public function qrMargin(
        int $margin
    ): self {

        return $this->setDriverOption(
            'image',
            'qr_margin',
            $margin
        );
    }



    /*
|--------------------------------------------------------------------------
| Barcode
|--------------------------------------------------------------------------
*/


    public function barcode(
        string $data
    ): self {

        return $this->setDriverOption(
            'image',
            'source',
            [
                'type' => 'barcode',
                'value' => $data,
            ]
        );
    }



    public function barcodeFormat(
        string $format
    ): self {

        return $this->setDriverOption(
            'image',
            'barcode_format',
            strtoupper($format)
        );
    }



    public function barcodeHeight(
        int $height
    ): self {

        return $this->setDriverOption(
            'image',
            'barcode_height',
            $height
        );
    }



    public function barcodeWidth(
        int $width
    ): self {

        return $this->setDriverOption(
            'image',
            'barcode_width',
            $width
        );
    }
}
