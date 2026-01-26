<?php

declare(strict_types=1);

namespace Jengo\Schema\Hydration;

use Jengo\Schema\Graph\Node;
use Jengo\Schema\Query\DTO\BuilderResult;
use Jengo\Schema\Query\DTO\PaginationData;
use Jengo\Schema\Query\DTO\PaginationLink;
use Jengo\Schema\Query\DTO\QueryOptions;
use Jengo\Schema\Query\DTO\QueryResult;
use Jengo\Schema\Support\AliasGenerator;

final class Hydrator
{
    private array $data = [];

    private ?int $total = null;

    private QueryOptions $options;
    /**
     * Hydrate flat DB rows into nested structure
     */
    public static function hydrate(Node $rootNode, BuilderResult $builderResult, ?QueryOptions $options = null): QueryResult
    {
        $self = new self();

        $self->total = $builderResult->total;
        $self->options = $options ?? new QueryOptions();

        $self->data = self::hydrateNode($rootNode, $builderResult->rows);

        return $self->finish();
    }

    /**
     * Returns the query result from the request
     * @return QueryResult
     */
    private function finish(): QueryResult
    {
        return new QueryResult(
            data: $this->data,
            count: count($this->data),
            pagination: $this->resolvePagination(),
        );
    }

    /**
     * Resolves total count for the query
     * @return int
     */
    private function resolveTotal(): int
    {
        if ($this->total !== null) {
            return $this->total;
        }

        return count($this->data);
    }

    /**
     * Resolves pagination for the query
     * @return PaginationData
     */
    private function resolvePagination(): PaginationData
    {
        $page = $this->options->pagination->page;
        $limit = $this->options->pagination->limit;
        $total = $this->resolveTotal();

        $links = [];

        $totalPages = (int) ceil($total / $limit);

        if ($page > 1) {
            $links[] = new PaginationLink(
                label: 'prev',
                page: $page - 1,
                active: true,
                link: ''
            );
        }

        // TDOD: Add numbered page links

        if ($page < $totalPages) {
            $links[] = new PaginationLink(
                label: 'next',
                page: $page + 1,
                active: true,
                link: ''
            );
        }

        return new PaginationData(
            page: $page,
            limit: $limit,
            total: $total,
            links: $links,
        );
    }

    /**
     * Recursive hydration per node
     */
    private static function hydrateNode(Node $node, array $rows): array
    {
        $alias = AliasGenerator::for($node);

        // Group rows by primary key if node is HasMany
        $grouped = [];
        $key = 0;

        foreach ($rows as $row) {
            if ($node->isMany()) {
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [];
                }
                $grouped[$key][] = $row;
            } else {
                // BelongsTo / one-to-one: just keep single row
                $grouped[$key] = [$row];
            }
            $key++;
        }

        $result = [];

        foreach ($grouped as $key => $groupRows) {
            // Extract node fields
            $record = [];

            $pk = $node->schema->primaryKey;
            $col = "{$alias}__{$pk->name}";
            // add primary key
            $record[$pk->name] = $groupRows[0][$col] ?? null;

            foreach ($node->schema->fields as $field) {
                $col = "{$alias}__{$field->name}";
                $record[$field->name] = $groupRows[0][$col] ?? null;
            }

            // Recurse into children
            foreach ($node->children as $child) {
                $childData = self::hydrateNode($child, $groupRows);

                if ($child->isMany()) {
                    $record[$child->edge->relation->name] = array_values($childData);
                } else {
                    $record[$child->edge->relation->name] = reset($childData) ?: null;
                }
            }

            $result[$key] = $record;
        }

        return $result;
    }
}
