<?php 

declare(strict_types=1);

namespace Tests\Unit;

use Jengo\Schema\Graph\RelationshipGraph;
use Jengo\Schema\Reflection\SchemaReflector;
use Tests\BaseTest;
use Tests\Support\Schemas\UserFileSchema;

final class GraphTest extends BaseTest
{
    public function testGraph(): void
    {
        $userFileSchema = SchemaReflector::reflect(UserFileSchema::class);
        $graph = RelationshipGraph::build(
            rootSchema: $userFileSchema,
            derivePaths: ['user'],
        );

        $this->assertTrue(true);
    }
}
