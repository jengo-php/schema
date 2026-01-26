<?php

declare(strict_types=1);

namespace Jengo\Schema\Attributes\Relations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class BelongsTo
{
    public function __construct(
        public string $schema,
        public string $from,
        public array $select = [],
    ) {
    }
}
