<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Tests\Mocks;

use UnnovateBrains\DocumentBuilder\Contracts\TemplateRenderer;

final class MockTemplateRenderer implements TemplateRenderer
{
    public function render(
        string $engine,
        string $view,
        array $data = []
    ): string {

        $recordsJson = json_encode(
            $data['records'] ?? [],
            JSON_PRETTY_PRINT
        );

        return <<<HTML
<document-layout>
    <template>{$view}</template>
    <engine>{$engine}</engine>
    <data-payload>
        {$recordsJson}
    </data-payload>
</document-layout>
HTML;
    }
}