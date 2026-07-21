<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use SchoolPalm\AppLogger\Facades\AppLogger;
use SchoolPalm\AppLogger\Context\AppContext;

final class DocumentLogger
{
    public static function info(
        string $message,
        array $context = []
    ): void {

        if (! class_exists(AppLogger::class)) {
            return;
        }


        AppLogger::info(
            $message,
            AppContext::make($context)
        );
    }


    public static function error(
        string $message,
        array $context = []
    ): void {

        if (! class_exists(AppLogger::class)) {
            return;
        }


        AppLogger::error(
            $message,
            AppContext::make($context)
        );
    }
}
