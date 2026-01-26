<?php

declare(strict_types=1);

namespace Jengo\Schema\Query;

use Jengo\Schema\Graph\Node;
use Jengo\Schema\Support\AliasGenerator;

final class QueryPlan
{
    public Node $root;

    /**
     * @var array<string,string> mapping node path => alias
     */
    public array $aliases = [];

    /**
     * @var array<string,array> select fields by alias
     */
    public array $selects = [];

    public function __construct(Node $root)
    {
        $this->root = $root;
        $this->generatePlan($root);
    }

    private function generatePlan(Node $node, array $path = []): void
    {
        $path[] = $node->edge?->relation->name ?? 'root';

        $alias = AliasGenerator::for($node);
        $this->aliases[implode('.', $path)] = $alias;

        $isRoot = implode('.', $path) === 'root';
        // Gather fields for this node
        $fields = [];

        foreach ($node->schema->fields as $field) {
            if ($field->derived)
                continue;

            $fields[] = "{$alias}.{$field->name} AS {$alias}__{$field->name}";
        }

        // include primary key
        $fields[] = "{$alias}.{$node->schema->primaryKey->name} AS {$alias}__{$node->schema->primaryKey->name}";

        $this->selects[$alias] = $fields;

        // Recurse into children
        foreach ($node->children as $child) {
            $this->generatePlan($child, $path);
        }
    }
}
