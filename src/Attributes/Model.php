<?php

declare(strict_types=1);

namespace Jengo\Schema\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Model
{
    public function __construct(
        public string $model,
        public ?string $entity = null,
    ) {
    }
}
