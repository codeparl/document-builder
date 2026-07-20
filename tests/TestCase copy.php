<?php

namespace UnnovateBrains\DocumentBuilder\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Tests\Mocks\FakeContextResolver;
use UnnovateBrains\DocumentBuilder\Context\ContextResolverRegistry;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContextResolverRegistry;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Contracts\Renderer;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockVirtualStorage;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockTemplateRenderer;
use UnnovateBrains\DocumentBuilder\Drivers\DriverManager;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockPdfDriver;
use UnnovateBrains\DocumentBuilder\DocumentBuilderServiceProvider;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockContextResolver;
use UnnovateBrains\DocumentBuilder\Tests\Mocks\MockTenantEnvironment;




class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            DocumentBuilderServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Bind the DocumentStorage interface to our Mock Virtual Storage
        $this->app->singleton(DocumentStorage::class, function ($app) {
            return new MockVirtualStorage();
        });

        // 2. Bind the Renderer interface to our Mock Template Renderer
        $this->app->singleton(Renderer::class, function ($app) {
            return new MockTemplateRenderer();
        });

        app()->singleton(
            MockTenantEnvironment::class,
            fn() => new MockTenantEnvironment()
        );

                   app()->singleton(
    DocumentContextResolverRegistry::class,
    ContextResolverRegistry::class
);
        app(DocumentContextResolverRegistry::class)
            ->register(
                new MockContextResolver()
            );

 
    }
}
