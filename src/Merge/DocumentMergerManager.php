<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Merge;

use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentMerger;

final class DocumentMergerManager
{
    /**
     * @var array<string, DocumentMerger>
     */
    private array $mergers = [];


   

public function register(
    string $type,
    DocumentMerger $merger
): void {

    $this->mergers[$type] = $merger;
}


    public function merger(
        string $type
    ): DocumentMerger {

        foreach ($this->mergers as $merger) {

            if ($merger->supports($type)) {
                return $merger;
            }
        }


        throw new RuntimeException(
            "No merger registered for {$type}"
        );
    }
}