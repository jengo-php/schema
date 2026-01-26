<?php

declare(strict_types=1);

namespace Jengo\Schema\Query;

use CodeIgniter\Model;
use Jengo\Schema\Graph\RelationshipGraph;
use Jengo\Schema\Hydration\Hydrator;
use Jengo\Schema\Query\DTO\QueryOptions;
use Jengo\Schema\Query\DTO\QueryResult;
use Jengo\Schema\Query\Enums\QueryMode;
use Jengo\Schema\Query\OptionBuilder\RequestOptionsBuilder;
use Jengo\Schema\Reflection\SchemaReflector;


final class Query
{
    public const QueryMode INLINE_MODE = QueryMode::INLINE;
    public const QueryMode OPEN_MODE = QueryMode::OPEN;

    public static function run(string $schema, ?QueryOptions $options = null, QueryMode $mode = Query::INLINE_MODE): QueryResult
    {
        if ($mode === Query::OPEN_MODE) {
            // build query from request
            $options = RequestOptionsBuilder::build($options);
        }

        // reflect schema
        // TODO: we can later improve this by caching reflected clases and removing this overhead
        $schemaMeta = SchemaReflector::reflect($schema);

        // build relationship graph from schema
        $graph = RelationshipGraph::build(rootSchema: $schemaMeta, derivePaths: $options?->derive ?? []);

        // build and exceute query
        $builderResult = QueryBuilder::build($graph->root, $options)->execute();

        // hydrate data to get QueryResult
        return Hydrator::hydrate($graph->root, $builderResult, $options);
    }
}


