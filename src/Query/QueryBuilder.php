<?php

declare(strict_types=1);

namespace Jengo\Schema\Query;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use Config\Database;
use Jengo\Schema\Graph\Node;
use Jengo\Schema\Metadata\FieldMetadata;
use Jengo\Schema\Metadata\SchemaMetadata;
use Jengo\Schema\Query\DTO\BuilderResult;
use Jengo\Schema\Query\DTO\PaginationOptions;
use Jengo\Schema\Query\DTO\ParamOptions;
use Jengo\Schema\Query\DTO\QueryOptions;
use Jengo\Schema\Query\DTO\SortOptions;
use Jengo\Schema\Support\AliasGenerator;
use RuntimeException;

final class QueryBuilder
{
    private ?BaseBuilder $builder = null;
    private ?QueryOptions $options = null;
    public static function build(Node $rootNode, QueryOptions $options): self
    {
        $plan = new QueryPlan($rootNode);
        $baseTable = self::resolveTableFromSchema($rootNode->schema);
        $rootAlias = AliasGenerator::for($rootNode);
        $db = Database::connect();

        // init builder
        $builder = $db->table("$baseTable AS $rootAlias");

        // build query
        self::applyRootSelect($builder, $plan, $rootAlias);

        self::applyWhere($builder, $options->params, $rootAlias);

        self::applySort($builder, $options->sort, $rootAlias);

        self::applySearch($builder, $rootNode, $options->search);

        self::applyJoins($builder, $rootNode, $plan);

        // any other feature related to query building can be done here

        $self = new self();

        $self->builder = $builder;
        $self->options = $options;

        return $self;
    }

    public function execute(): BuilderResult
    {
        $builder = $this->builder;
        $options = $this->options;

        if (!$builder || !$options) {
            throw new RuntimeException('You need to build the query before exceuting');
        }

        $total = $builder->countAllResults(false);
        $result = self::applyPagination($builder, $options->pagination);

        return new BuilderResult(
            total: $total,
            rows: $result->getResultArray(),
        );
    }

    private static function applyRootSelect(BaseBuilder $builder, QueryPlan $plan, string $rootAlias): void
    {
        $baseSelects = ["$rootAlias.*"];

        if ($plan->selects[$rootAlias] ?? []) {
            $baseSelects = $plan->selects[$rootAlias];
        }

        $builder->select($baseSelects);
    }

    private static function applyWhere(BaseBuilder $builder, ParamOptions $paramOptions, string $rootAlias): void
    {
        foreach ($paramOptions->params as $key => $value) {
            if (is_array($value)) {
                if (!$paramOptions->isOr) {
                    $builder->whereIn("$rootAlias.$key", $value);
                } else {
                    $builder->orWhereIn("$rootAlias.$key", $value);
                }
                continue;
            }

            if (!$paramOptions->isOr) {
                log_message('debug', 'got here');
                $builder->where("$rootAlias.$key", $value);
            } else {
                $builder->orWhere("$rootAlias.$key", $value);
            }
        }
    }

    private static function applySearch(BaseBuilder $builder, Node $node, ?string $search, ): void
    {
        if (!$search)
            return;

        $current = $node;

        foreach ($node->children as $child) {
            $fields = array_filter(array_map(function (FieldMetadata $f) {
                if (!$f->searchable) {
                    return null;
                }

                return $f->name;
            }, $current->schema->fields));

            $alias = AliasGenerator::for($current);

            $builder->groupStart();
            foreach ($fields as $field) {
                $builder->orLike(sprintf('%s.%s', $alias, $field), $search, 'both', null, true);
            }

            $builder->groupEnd();

            $current = $child;
        }
    }

    private static function applySort(BaseBuilder $builder, SortOptions $sort, string $rootAlias): void
    {
        $builder->orderBy("$rootAlias.{$sort->column}", $sort->direction->value);
    }

    private static function applyJoins($builder, Node $node, QueryPlan $plan, ?string $parentAlias = null, ): void
    {
        foreach ($node->children as $child) {
            $childAlias = AliasGenerator::for($child);
            $parentAlias ??= AliasGenerator::for($node);

            $fk = $child->edge->relation->fromField;
            $pk = $child->schema->primaryKey->name;

            $childTable = self::resolveTableFromSchema($child->schema);

            $builder->join(
                "{$childTable} {$childAlias}",
                "{$parentAlias}.{$fk} = {$childAlias}.{$pk}",
                'left',
            );

            $builder->select($plan->selects[$childAlias]); // temp, will map to alias

            self::applyJoins(builder: $builder, node: $child, plan: $plan, parentAlias: $parentAlias);
        }
    }

    private static function applyPagination(BaseBuilder $builder, PaginationOptions $pagination): ResultInterface
    {
        $limit = $pagination->limit;
        $page = $pagination->page;
        $offset = ($page - 1) * $limit;

        return $builder->get($limit, $offset);
    }

    private static function resolveTableFromSchema(SchemaMetadata $schema): string
    {
        $modelClass = $schema->modelClass;

        // chec if class exists
        if (!class_exists($modelClass)) {
            throw new RuntimeException("Model class {$modelClass} does not exist");
        }

        /** @var Model $modelInstance */
        $modelInstance = new $modelClass();

        if (!$modelInstance instanceof Model) {
            throw new RuntimeException("Model class {$modelClass} is not an instance of CodeIgniter Model");
        }

        return $modelInstance->builder()->getTable();
    }
}
