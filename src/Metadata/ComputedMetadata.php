<?php

declare(strict_types=1);

namespace Jengo\Schema\Metadata;

final class ComputedMetadata
{
    public function __construct(
        public string $name,
        public string $method,
    ) {
    }
}
