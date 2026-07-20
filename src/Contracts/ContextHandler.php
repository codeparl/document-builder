<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

/**
 * Interface ContextResolver
 *
 * Defines the lifecycle for restoring and cleaning up an execution context
 * around a document generation pipeline.
 *
 * DocumentBuilder itself remains context-agnostic. Applications and packages
 * may implement this contract to establish any environment required before
 * document processing begins and safely tear it down afterwards.
 *
 * Common implementations include:
 *
 * - Multi-tenancy initialization
 * - School or organization selection
 * - Locale and timezone switching
 * - User impersonation
 * - Custom application state restoration
 */
interface ContextHandler
{
    /**
     * Enter the execution context.
     *
     * This method is invoked before any source resolution or document
     * generation occurs. Implementations should initialize any required
     * application state so subsequent pipeline stages execute within the
     * correct context.
     *
     * Examples:
     * - Initialize tenant database connection.
     * - Switch active school.
     * - Set authenticated user.
     * - Configure application locale.
     */
    public function enter(
        PipelineContext $context
    ): void;

    /**
     * Leave the execution context.
     *
     * This method is invoked after pipeline execution completes, regardless
     * of whether it succeeds or fails. Implementations should release any
     * resources and restore the application to a clean state.
     *
     * Examples:
     * - End tenant context.
     * - Restore default database connection.
     * - Clear impersonated user.
     * - Reset application state.
     */
    public function leave(
        PipelineContext $context
    ): void;
}