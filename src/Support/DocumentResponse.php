<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentResponse extends StreamedResponse
{
    protected bool $deleteAfterSend = false;

    public function __construct(
        callable $callback,
        int $status = 200,
        array $headers = [],
        protected ?string $filename = null,
        protected bool $isAttachment = true,
        protected ?string $storagePath = null,
        protected ?string $disk = null
    ) {
        if ($filename) {
            $disposition = $isAttachment ? 'attachment' : 'inline';
            $headers['Content-Disposition'] = sprintf('%s; filename="%s"', $disposition, $filename);
        }

        parent::__construct($callback, $status, $headers);
    }

    /**
     * Mark the temporary storage file for deletion after the stream completes.
     */
    public function deleteAfter(): self
    {
        $this->deleteAfterSend = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    /**
     * {@inheritdoc}
     */
    public function sendContent(): static
    {
        $response = parent::sendContent();

        // Only delete after the content has successfully streamed out
        if ($this->deleteAfterSend && $this->storagePath) {
            try {
                Storage::delete($this->storagePath);
            } catch (\Throwable $e) {
                // Fail silently if cleanup fails
            }
        }

        return $response;
    }
}
