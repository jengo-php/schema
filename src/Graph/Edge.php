<?php

declare(strict_types=1);

namespace Jengo\Schema\Graph;

use Jengo\Schema\Metadata\RelationMetadata;

final class Edge
{
    public function __construct(
        public RelationMetadata $relation,
        public bool $many,
    ) {
    }
}
