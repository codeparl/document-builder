<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Concerns;

trait PdfBuilderTrait
{

    /**
     * Set PDF page size.
     *
     * Example:
     *
     * ->pageSize('A4')
     */
    public function pageSize(
        string $size
    ): self {

        return $this->setDriverOption(
            'pdf',
            'page_size',
            $size
        );
    }



    /**
     * Set PDF orientation.
     *
     * Example:
     *
     * ->orientation('landscape')
     */
    public function orientation(
        string $orientation
    ): self {

        return $this->setDriverOption(
            'pdf',
            'orientation',
            strtolower($orientation)
        );
    }



    /**
     * Configure PDF margins.
     *
     * Example:
     *
     * ->margins([
     *     'top'=>10,
     *     'right'=>10,
     * ])
     */
    public function margins(
        array $margins
    ): self {

        return $this->setDriverOption(
            'pdf',
            'margins',
            $margins
        );
    }



    /**
     * Enable/disable header rendering.
     */
    public function header(
        bool $enabled = true
    ): self {

        return $this->setDriverOption(
            'pdf',
            'header',
            $enabled
        );
    }



    /**
     * Enable/disable footer rendering.
     */
    public function footer(
        bool $enabled = true
    ): self {

        return $this->setDriverOption(
            'pdf',
            'footer',
            $enabled
        );
    }



    /**
     * Define PDF header content.
     */
    public function headerContent(
        string $content
    ): self {

        return $this->setDriverOption(
            'pdf',
            'header_content',
            $content
        );
    }



    /**
     * Define PDF footer content.
     */
    public function footerContent(
        string $content
    ): self {

        return $this->setDriverOption(
            'pdf',
            'footer_content',
            $content
        );
    }



    /**
     * Enable watermark.
     */
    public function watermark(
        string $text
    ): self {

        return $this->setDriverOption(
            'pdf',
            'watermark',
            $text
        );
    }



    /**
     * PDF metadata.
     *
     * Example:
     *
     * ->pdfMetadata([
     *    'author'=>'SchoolPalm'
     * ])
     */
    public function pdfMetadata(
        array $metadata
    ): self {

        return $this->setDriverOption(
            'pdf',
            'metadata',
            $metadata
        );
    }



    /**
     * Raw PDF configuration escape hatch.
     *
     * Example:
     *
     * ->pdfConfig([
     *     'compress'=>true
     * ])
     */
    public function pdfConfig(
        array $config
    ): self {

        return $this->setDriverOptions(
            'pdf',
            $config
        );
    }
}
