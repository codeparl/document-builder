<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use UnnovateBrains\DocumentBuilder\Chunking\ChunkExecutorManager;
use UnnovateBrains\DocumentBuilder\Chunking\DefaultChunkProcessor;
use UnnovateBrains\DocumentBuilder\Chunking\QueueChunkExecutor;
use UnnovateBrains\DocumentBuilder\Chunking\SyncChunkExecutor;
use UnnovateBrains\DocumentBuilder\Context\ContextHandlerRegistry;
use UnnovateBrains\DocumentBuilder\Context\DefaultContextHandler;
use UnnovateBrains\DocumentBuilder\Context\NullDocumentContextResolver;
use UnnovateBrains\DocumentBuilder\Contracts\ChunkProcessor;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentBatchRepository;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContextHandlerRegistry;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContextResolver;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentQueue;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Contracts\TemplateRenderer;
use UnnovateBrains\DocumentBuilder\Drivers\DriverManager;
use UnnovateBrains\DocumentBuilder\Drivers\Excel\PhpSpreadsheetDriver;
use UnnovateBrains\DocumentBuilder\Drivers\Image\InterventionImageDriver;
use UnnovateBrains\DocumentBuilder\Drivers\Pdf\MpdfDriver; // Added
use UnnovateBrains\DocumentBuilder\Engines\InterventionImageEngine;
use UnnovateBrains\DocumentBuilder\Engines\MpdfEngine;
use UnnovateBrains\DocumentBuilder\Engines\PhpSpreadsheetEngine;
use UnnovateBrains\DocumentBuilder\Merge\DefaultMerger;
use UnnovateBrains\DocumentBuilder\Merge\DocumentMergerManager;
use UnnovateBrains\DocumentBuilder\Merge\PdfMerger;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\DocumentPipeline;
use UnnovateBrains\DocumentBuilder\Pipelines\DocumentPipelineProcessor;
use UnnovateBrains\DocumentBuilder\Queue\DocumentQueueManager;
use UnnovateBrains\DocumentBuilder\Rendering\Engines\BladeTemplateEngine;
use UnnovateBrains\DocumentBuilder\Rendering\Engines\HtmlTemplateEngine;
use UnnovateBrains\DocumentBuilder\Rendering\TemplateRenderManager;
use UnnovateBrains\DocumentBuilder\Repositories\StorageDocumentBatchRepository;
use UnnovateBrains\DocumentBuilder\Services\DocumentPathGenerator;
use UnnovateBrains\DocumentBuilder\Storage\DocumentPathResolver;
use UnnovateBrains\DocumentBuilder\Storage\LaravelDocumentStorage;
use UnnovateBrains\DocumentBuilder\Support\ImageDriverResolver;
use UnnovateBrains\DocumentBuilder\Support\SourceFactory;
use UnnovateBrains\DocumentBuilder\Support\SourceRegistry;

final class DocumentBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {

        $this->mergeConfigFrom(
            __DIR__ . '/config/document-builder.php',
            'document-builder'
        );



        $this->app->singleton(
            DocumentPathResolver::class,
            function ($app) {
                return new DocumentPathResolver(
                    $app->make(DocumentContextResolver::class)
                );
            }
        );


        $this->app->singleton(
            DocumentStorage::class,
            function ($app) {
                return new LaravelDocumentStorage(
                    storageFactory: $app->make(
                        \Illuminate\Contracts\Filesystem\Factory::class
                    ),
                    pathResolver: $app->make(
                        DocumentPathResolver::class
                    ),
                    defaultDisk: 'local'
                );
            }
        );


        $this->app->singleton(
            DocumentPathGenerator::class,
            function ($app) {
                return new DocumentPathGenerator(
                    $app->make(DocumentPathResolver::class)
                );
            }
        );
        /*
    |--------------------------------------------------------------------------
    | Driver Management
    |--------------------------------------------------------------------------
    */

        $this->app->singleton(
            DriverManager::class,
            fn() => new DriverManager()
        );


        /*
    |--------------------------------------------------------------------------
    | Pipeline Core
    |--------------------------------------------------------------------------
    */

        $this->app->singleton(
            DocumentPipeline::class,
            function ($app) {
                return new DocumentPipelineProcessor($app);
            }
        );


        /*
    |--------------------------------------------------------------------------
    | Queue Manager
    |--------------------------------------------------------------------------
    */

        $this->app->singleton(
            DocumentQueue::class,
            DocumentQueueManager::class
        );


        /*
    |--------------------------------------------------------------------------
    |  Context handler 
    |--------------------------------------------------------------------------
    */

        $this->app->singleton(
            DocumentContextHandlerRegistry::class,
            function () {
                return new ContextHandlerRegistry();
            }
        );


        // let us bind this  DocumentContextResolver


        $this->app->singleton(
            DocumentContextResolver::class,
            function ($app) {
                return new NullDocumentContextResolver();
            }

        );



        /*
    |--------------------------------------------------------------------------
    | Document Manager Facade Service
    |--------------------------------------------------------------------------
    */

        $this->app->singleton(
            DocumentManager::class,
            function ($app) {

                return new DocumentManager(
                    $app->make(DocumentPipeline::class),
                    $app->make(DriverManager::class),
                    $app->make(DocumentStorage::class),
                    $app->make(DocumentQueue::class)
                );
            }
        );


        /*
    |--------------------------------------------------------------------------
    | Template Rendering
    |--------------------------------------------------------------------------
    */

        $this->app->singleton(
            TemplateRenderer::class,
            function ($app) {

                $manager = new TemplateRenderManager();


                $manager->registerEngine(
                    'blade',
                    new BladeTemplateEngine(
                        $app->make(
                            \Illuminate\Contracts\View\Factory::class
                        )
                    )
                );


                $manager->registerEngine(
                    'html',
                    new HtmlTemplateEngine()
                );


                return $manager;
            }
        );


        /*
    |--------------------------------------------------------------------------
    | Context + Source Registries
    |--------------------------------------------------------------------------
    */

        $this->app->singleton(
            SourceRegistry::class,
            fn() => new SourceRegistry()
        );


        /*
    |--------------------------------------------------------------------------
    | Chunk Processing
    |--------------------------------------------------------------------------
    */

        $this->app->singleton(
            ChunkExecutorManager::class,
            function ($app) {

                $manager = new ChunkExecutorManager();


                $manager->register(
                    'sync',
                    $app->make(
                        SyncChunkExecutor::class
                    )
                );


                $manager->register(
                    'queue',
                    $app->make(
                        QueueChunkExecutor::class
                    )
                );


                return $manager;
            }
        );


        $this->app->bind(
            ChunkProcessor::class,
            DefaultChunkProcessor::class
        );


        $this->app->singleton(
            ImageManager::class,
            function () {

                return ImageDriverResolver::manager();
            }
        );
        // Resolve the manager from the container once boot starts
        // 1. Register production system core drivers

        $driverManager = $this->app->make(
            DriverManager::class
        );

        //register document drivers and their engines 

        $driverManager->register(
            new MpdfDriver(
                $this->app->make(MpdfEngine::class)
            )
        );

        $driverManager->register(
            new PhpSpreadsheetDriver(
                $this->app->make(PhpSpreadsheetEngine::class)
            )
        );
        $driverManager->register(

            new InterventionImageDriver(
                $this->app->make(
                    InterventionImageEngine::class
                )
            )

        );

        $this->app->singleton(
            DocumentMergerManager::class,
            function () {

                $manager = new DocumentMergerManager(new DefaultMerger);

                $manager->register(
                    'pdf',
                    new PdfMerger()
                );

                return $manager;
            }
        );

        $this->app->bind(
            DocumentBatchRepository::class,
            StorageDocumentBatchRepository::class
        );

        $this->app->singleton(
            SourceFactory::class,
            function () {
                return new SourceFactory(new SourceRegistry);
            }
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/document-builder.php'
            => config_path('document-builder.php'),
        ], 'document-builder-config');



        // Register Facade Alias dynamically for older setups or standalone package use
        $this->app->booted(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('DocumentStorage', \UnnovateBrains\DocumentBuilder\Facades\Storage::class);
        });

        //register default context handler here
        $this->app
            ->make(DocumentContextHandlerRegistry::class)
            ->register(
                $this->app->make(DefaultContextHandler::class)
            );
    }
}
