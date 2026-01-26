<?php

declare(strict_types=1);

namespace Jengo\Schema\Query\DTO;

use Jengo\Schema\Query\Enums\SortOrder;

final class SortOptions
{
    public function __construct(
        public readonly string $column = 'created_at',
        public readonly SortOrder $direction = SortOrder::ASC,
    ) {
    }
}
