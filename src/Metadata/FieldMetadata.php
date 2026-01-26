<?php

declare(strict_types=1);

namespace Jengo\Schema\Metadata;

final class FieldMetadata
{
    public function __construct(
        public string $name,
        public bool $searchable = false,
        public bool $derived = false,
    ) {
    }
}
