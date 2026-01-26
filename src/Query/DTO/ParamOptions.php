<?php

declare(strict_types=1);

namespace Jengo\Schema\Query\DTO;

use phpDocumentor\Reflection\PseudoTypes\False_;


final class ParamOptions
{
    public function __construct(
        /** @var list<string> */
        public readonly array $params = [],
        /** @var callable[] */
        public readonly array $callbacks = [],
        public readonly bool $isOr = false,
    ) {
    }
}
