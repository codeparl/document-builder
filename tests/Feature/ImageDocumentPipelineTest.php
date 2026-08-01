<?php

declare(strict_types=1);

use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Facades\Document;


it('saves a basic image document', function () {

    dump(file_exists(base_path(
        'tests/Fixtures/sample.jpg'
    )));
    $result =
        Document::image()

        ->fromImage(
            base_path(
                'tests/Fixtures/sample.jpg'
            )
        )

        ->convert('png')

        ->save();



    expect($result->extension())
        ->toBe('png');


    expect($result->filename())
        ->toEndWith('.png');



    $storage =
        app(DocumentStorage::class);



    expect(
        $storage->exists(
            $result->path()
        )
    )->toBeTrue();
});





it('saves a resized image document', function () {


    $result =
        Document::image()

        ->fromImage(
            base_path(
                'tests/Fixtures/sample.jpg'
            )
        )

        ->resize(
            500,
            500
        )

        ->convert('png')

        ->save();



    expect($result->extension())
        ->toBe('png');



    $storage =
        app(DocumentStorage::class);



    expect(
        $storage->exists(
            $result->path()
        )
    )->toBeTrue();
});





it('converts image format and saves output', function () {


    $result =
        Document::image()

        ->fromImage(
            base_path(
                'tests/Fixtures/sample.jpg'
            )
        )

        ->convert('webp')

        ->quality(80)

        ->save();



    expect($result->extension())
        ->toBe('webp');



    expect($result->filename())
        ->toEndWith('.webp');



    $storage =
        app(DocumentStorage::class);



    expect(
        $storage->exists(
            $result->path()
        )
    )->toBeTrue();
});





it('applies image effects', function () {


    $result =
        Document::image()

        ->fromImage(
            base_path(
                'tests/Fixtures/sample.jpg'
            )
        )

        ->grayscale()

        ->brightness(20)

        ->contrast(10)

        ->blur(5)

        ->convert('png')

        ->save();



    $storage =
        app(DocumentStorage::class);



    expect(
        $storage->exists(
            $result->path()
        )
    )->toBeTrue();
});





it('creates image from text source', function () {


    $result =
        Document::image()

        ->fromText(
            'SchoolPalm Test Image'
        )
        ->font('cursive')
        ->fontSize(36)
        ->fontColor('#130a7c')
        ->size(
            800,
            400
        )
        ->background('#ffffff')
        ->filename('hassan')
        ->convert('png')
        ->sync()
        ->save();


    $content  = $result->getContent();

    expect($content->getExtension())
        ->toBe('png');



    $storage =
        app(DocumentStorage::class);


    expect(
        $storage->exists(
            $content->getPath()
        )
    )->toBeTrue();
});

it('creates qr code image', function () {


    $result =
        Document::image()

        ->qrCode(
            'https://schoolpalm.com'
        )

        ->convert('png')

        ->sync()

        ->save();



    expect($result->getContent()->getExtension())
        ->toBe('png');
});

it('creates barcode image', function () {


    $result =
        Document::image()

        ->barcode(
            'SCHOOLPALM-001'
        )

        ->barcodeFormat(
            'CODE128'
        )

        ->size(
            600,
            150
        )

        ->convert('png')
        ->filename('barcode')

        ->sync()

        ->save();



    expect($result->getContent()->getExtension())
        ->toBe('png');


    $storage =
        app(DocumentStorage::class);



    expect(
        $storage->exists(
            $result->getPath()
        )
    )->toBeTrue();
});



it('supports image conversion pipeline', function () {


    $result =
        Document::image()

        ->fromImage(
            base_path(
                'tests/Fixtures/sample.jpg'
            )
        )

        ->resize(
            300,
            300
        )

        ->convert('webp')

        ->save();



    $storage =
        app(DocumentStorage::class);



    expect(
        $storage->exists(
            $result->path()
        )
    )->toBeTrue();



    expect($result->mime())
        ->toContain('image');
});
