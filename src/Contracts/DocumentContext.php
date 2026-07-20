<?php

namespace UnnovateBrains\DocumentBuilder\Contracts;

/**
 * Interface DocumentContext
 *
 * Defines the contract for providing application-specific context (e.g., multitenancy,
 * localization, and authentication) to the document execution pipeline without coupling
 * the core package to external models or third-party tenancy libraries.
 *
 * @package UnnovateBrains\DocumentBuilder\Contracts
 */
interface DocumentContext
{
    /**
     * Get the identifier or model of the current tenant.
     *
     * @return mixed Returns null if the execution is not running within a tenant context.
     */
    public function tenant(): mixed;

    /**
     * Get the identifier or model of the specific school/organization division.
     *
     * @return mixed Returns null if no school context is available.
     */
    public function school(): mixed;

    /**
     * Get the identifier or model of the user who triggered the document generation.
     *
     * @return mixed Returns null if executed via system/automated processes.
     */
    public function user(): mixed;

    /**
     * Get the locale to be used for rendering and localizing the document content.
     *
     * @return string|null The locale string (e.g., 'en', 'fr') or null to fall back to system default.
     */
    public function locale(): ?string;

    /**
     * Get the timezone context for date/time fields within the document.
     *
     * @return string|null The timezone string (e.g., 'UTC', 'America/New_York') or null.
     */
    public function timezone(): ?string;

    /**
     * Determine if any meaningful context is currently present.
     *
     * @return bool True if at least one context property is populated, false otherwise.
     */
    public function hasContext(): bool;

    /**
     * Export all available context parameters as an associative array.
     *
     * Useful for serializing the context when pushing the Execution Plan to a queue.
     *
     * @return array<string, mixed>
     */
    public function all(): array;
}