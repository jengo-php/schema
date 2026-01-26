<?php

declare(strict_types=1);

namespace Jengo\Schema\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Computed
{
    public function __construct(
        public string $name,
    ) {
    }
}
