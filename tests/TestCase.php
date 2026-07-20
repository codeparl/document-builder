<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Orchestra\Testbench\TestCase as Orchestra;
use UnnovateBrains\DocumentBuilder\DocumentBuilderServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            DocumentBuilderServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        $app['config']->set('filesystems.default', 'local');

        $app['config']->set('queue.default', 'sync');

        $app['config']->set(
            'document-builder.storage.root',
            __DIR__ . '/storage'
        );

        // Add your package's view directory to the view locations
        View::addLocation(__DIR__ . '/../workbench/resources/views');
        Config::set(
            'filesystems.default',
            'local'
        );

        Config::set(
            'filesystems.disks.local.root',
            __DIR__ . '/../workbench/storage/app'
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}
