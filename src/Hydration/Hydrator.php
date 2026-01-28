<?php

declare(strict_types=1);

namespace Jengo\Schema\Hydration;

use Jengo\Schema\Graph\Node;
use Jengo\Schema\Query\DTO\BuilderResult;
use Jengo\Schema\Query\DTO\PaginationData;
use Jengo\Schema\Query\DTO\PaginationLink;
use Jengo\Schema\Query\DTO\PaginationLinksData;
use Jengo\Schema\Query\DTO\QueryOptions;
use Jengo\Schema\Query\DTO\QueryResult;
use Jengo\Schema\Query\QueryPlan;
use Jengo\Schema\Support\AliasGenerator;
use Jengo\Schema\Support\PaginationUtils;

final class Hydrator
{
    private array $data = [];

    private ?int $total = null;

    private QueryOptions $options;
    private QueryPlan $plan;
    /**
     * Hydrate flat DB rows into nested structure
     */
    public static function hydrate(Node $rootNode, BuilderResult $builderResult, QueryOptions $options, QueryPlan $plan): QueryResult
    {
        $self = new self();

        $self->total = $builderResult->total;
        $self->options = $options ?? new QueryOptions();
        $self->plan = $plan;

        $self->data = self::hydrateNode($rootNode, $builderResult->rows, $plan);

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

        $links = PaginationUtils::generateLinks(
            data: new PaginationLinksData(
                page: $page,
                limit: $limit,
                total: $total
            ),
            number: $this->options->pagination->linksMax,
            group: $this->options->pagination->group,
            withQuery: $this->options->pagination->withQuery
        );

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
    private static function hydrateNode(Node $node, array $rows, QueryPlan $plan): array
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
                // check if column is supposed to be selected
                $selects = $plan->selectsRaw[$alias];

                if (!in_array($field->name, $selects))
                    continue;

                $col = "{$alias}__{$field->name}";

                $record[$field->name] = $groupRows[0][$col] ?? null;
            }

            // Recurse into children
            foreach ($node->children as $child) {
                $childData = self::hydrateNode($child, $groupRows, $plan);

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
