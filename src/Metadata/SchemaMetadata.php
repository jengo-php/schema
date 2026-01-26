<?php

declare(strict_types=1);

namespace Jengo\Schema\Metadata;

final class SchemaMetadata
{
    public function __construct(
        public string $schemaClass,
        public string $modelClass,
        public ?string $entityClass,
        public FieldMetadata $primaryKey,
        /**
         * @var list<FieldMetadata>
         */
        public array $fields,
        /**
         * @var list<RelationMetadata>
         */
        public array $relations,
        /**
         * @var list<ComputedMetadata>
         */
        public array $computed,
    ) {
    }
}
