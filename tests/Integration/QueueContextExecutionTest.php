<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use UnnovateBrains\DocumentBuilder\Facades\Document;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Contracts\Renderer;
use UnnovateBrains\DocumentBuilder\Drivers\DriverManager;
use UnnovateBrains\DocumentBuilder\Drivers\Pdf\MpdfDriver;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockStudentSource;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockVirtualStorage;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockTemplateRenderer;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContextResolverRegistry;
use UnnovateBrains\DocumentBuilder\Contracts\ContextResolver;
use UnnovateBrains\DocumentBuilder\Contracts\TemplateRenderer;
use UnnovateBrains\DocumentBuilder\DocumentBuilder;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Queue\Jobs\PrepareDocumentJob;
use UnnovateBrains\DocumentBuilder\Support\SourceFactory;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockContextResolver;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockTenantEnvironment;





beforeEach(function () {
  app()->singleton(
        TemplateRenderer::class,
        MockTemplateRenderer::class
    );
  DocumentBuilder::registerSource(
        'mock_students',
        MockStudentSource::class
    );

});



afterEach(function () {



});



it('executes queued document generation with isolated tenant school context', function(){

    app()->singleton(
        DocumentStorage::class,
        MockVirtualStorage::class
    );


    

    $driverManager = app(DriverManager::class);

    $driverManager->register(
        new MpdfDriver()
    );



    $source = new MockStudentSource([
        [
            'id'=>1,
            'name'=>'John',
        ]
    ]);



    $batchId =
        Document::pdf()
        ->engine('mpdf')
        ->fromSource($source)
        ->view('transcripts.student')
        ->filename('queued_report')
        ->context([
            'tenant_id'=>'tenant-1',
            'school_id'=>'school-a',
            'locale'=>'en',
            'timezone'=>'Africa/Kampala'
        ])
        ->queue()
        ->save();



    expect($batchId)
        ->toBeString();


    /**
     * Simulate worker
     */
   (new PrepareDocumentJob($batchId))
    ->handle(
        app(DocumentStorage::class)
    );



    /**
     * Context should have been restored
     */
    expect(
        app(MockTenantEnvironment::class)
            ->current()
    )
    ->toBe([
        'tenant_id'=>null,
        'school_id'=>null,
    ]);



    $storage = app(DocumentStorage::class);



    expect(
        $storage->exists(
            "documents/queued_report.pdf"
        )
    )
    ->toBeTrue();


});


it('isolates queued documents by tenant and school context', function(){


    $environment =
        app(MockTenantEnvironment::class);



    $batchA =
        Document::pdf()
        ->fromSource(
            new MockStudentSource([
                [
                    'name'=>'John'
                ]
            ])
        )
        ->view('reports.student')
        ->context([
            'tenant_id'=>'tenant-a',
            'school_id'=>'school-a'
        ])
        ->queue()
        ->save();



    (new PrepareDocumentJob($batchA))
        ->handle(
            app(DocumentStorage::class)
        );



    expect(
        $environment->current()
    )
    ->toBe([
        'tenant_id'=>null,
        'school_id'=>null
    ]);





    $batchB =
        Document::pdf()
        ->fromSource(
            new MockStudentSource([
                [
                    'name'=>'Jane'
                ]
            ])
        )
        ->view('reports.student')
        ->context([
            'tenant_id'=>'tenant-b',
            'school_id'=>'school-b'
        ])
        ->queue()
        ->save();



    (new PrepareDocumentJob($batchB))
        ->handle(
            app(DocumentStorage::class)
        );



    expect(
        $environment->current()
    )
    ->toBe([
        'tenant_id'=>null,
        'school_id'=>null
    ]);

});