<?php

namespace UnnovateBrains\DocumentBuilder\Drivers;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentDriver;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;

abstract class AbstractDriver implements DocumentDriver
{
    /**
     * Resolve a clean, fallback-safe filename from the plan.
     */
    protected function resolveFilename(ExecutionPlan $plan, string $extension): string
    {
        $filename = $plan->getOutputFilename() ?? ('document_' . uniqid(more_entropy: true));
        
        if (! str_ends_with(strtolower($filename), '.' . $extension)) {
            $filename .= '.' . $extension;
        }

        return $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $type, ?string $engine = null): bool
    {
        if (strtolower($type) !== strtolower($this->type())) {
            return false;
        }

        return $engine === null || strtolower($engine) === strtolower($this->engine());
    }
}