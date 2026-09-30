<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use Illuminate\Support\Str;

final class FileContent implements DocumentContent
{
    private readonly string $type;

    private  string $filename;


    public function __construct(
        private  string $path,

        ?string $type = null,

        ?string $filename = null,

        private readonly ?string $extension = 'pdf',

        private readonly array $metadata = [],

        /**
         * Indicates this path is a physical filesystem path
         * instead of a DocumentStorage path.
         */
        private readonly bool $physical = false
    ) {

        $this->type =
            $type
            ?? $extension
            ?? 'pdf';

        $this->path =  Str::beforeLast($this->path, '.') . '.' . $extension;

        $this->filename =
            $filename
            ?? basename($this->path);
        $this->filename = Str::beforeLast($this->filename, '.') . '.' . $extension;
    }



    public function value(): string
    {
        return $this->path;
    }



    public function writeTo(
        callable $writer
    ): void {


        if ($this->physical) {

            $stream = fopen(
                $this->path,
                'rb'
            );
        } else {

            $storage =
                app(DocumentStorage::class);


            $stream =
                $storage->readStream(
                    $this->path
                );
        }



        if ($stream === false) {

            throw new RuntimeException(
                "Unable to open document [$this->path]"
            );
        }



        try {

            $writer($stream);
        } finally {

            fclose($stream);
        }
    }



    public function size(): ?int
    {

        if ($this->physical) {

            return filesize(
                $this->path
            );
        }


        return app(DocumentStorage::class)
            ->size($this->path);
    }



    public function isStream(): bool
    {
        return false;
    }



    public function isFile(): bool
    {
        return true;
    }



    public function getPath(): string
    {
        return $this->path;
    }



    public function path(): string
    {
        return $this->path;
    }



    public function getType(): string
    {
        return $this->type;
    }



    public function getFilename(): string
    {
        return $this->filename;
    }



    public function getExtension(): string
    {
        return $this->extension;
    }



    public function getMetadata(): array
    {
        return $this->metadata;
    }



    public function toString(): string
    {

        if ($this->physical) {

            $content = file_get_contents(
                $this->path
            );


            if ($content === false) {

                throw new RuntimeException(
                    "Unable to read document [$this->path]"
                );
            }


            return $content;
        }



        return app(DocumentStorage::class)
            ->get($this->path);
    }
}
