<?php

declare(strict_types=1);

namespace Jengo\Schema\Graph;

use Jengo\Schema\Metadata\SchemaMetadata;

final class Node
{
    public function __construct(
        public SchemaMetadata $schema,
        public ?Node $parent = null,
        public ?Edge $edge = null,
        /**
         * @var list<Node>
         */
        public array $children = [],
    ) {
    }

    public function isRoot(): bool
    {
        return $this->parent === null;
    }

    public function isMany(): bool
    {
        return $this->edge?->many ?? false;
    }

    public function addChild(Node $node): void
    {
        $this->children[] = $node;
    }
}
