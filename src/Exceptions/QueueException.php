<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Exceptions;

use Throwable;

final class QueueException extends DocumentException
{
    public static function dispatchFailed(string $jobClass, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('Failed to defer execution plan. Job [%s] could not be pushed to the queue bus.', $jobClass),
            0,
            $previous
        );
    }
}