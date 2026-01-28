<?php

declare(strict_types=1);

namespace Jengo\Schema\Query;

use Jengo\Schema\Graph\RelationshipGraph;
use Jengo\Schema\Hydration\Hydrator;
use Jengo\Schema\Query\DTO\QueryOptions;
use Jengo\Schema\Query\DTO\QueryResult;
use Jengo\Schema\Query\Enums\QueryMode;
use Jengo\Schema\Reflection\SchemaReflector;


final class Query
{
    public const QueryMode INLINE_MODE = QueryMode::INLINE;
    public const QueryMode OPEN_MODE = QueryMode::OPEN;

    public static function run(string $schema, QueryOptions $options = new QueryOptions(), QueryMode $mode = Query::INLINE_MODE): QueryResult
    {
        $options = OptionsResolver::resolve($mode, $options);

        // reflect schema
        // TODO: we can later improve this by caching reflected clases and removing this overhead
        $schemaMeta = SchemaReflector::reflect($schema);

        // build relationship graph from schema
        $graph = RelationshipGraph::build(rootSchema: $schemaMeta, derivePaths: $options?->derive ?? []);

        // generate plan
        $plan = QueryPlan::fromGraph($graph, $options);

        // build and exceute query
        $builderResult = QueryBuilder::build($graph->root, $options, $plan)->execute();

        // hydrate data to get QueryResult
        return Hydrator::hydrate($graph->root, $builderResult, $options, $plan);
    }
}


