<?php

declare(strict_types=1);

namespace Tests;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\Fabricator;
use Config\Database;
use Jengo\Schema\Graph\RelationshipGraph;
use Jengo\Schema\Query\DTO\PaginationOptions;
use Jengo\Schema\Query\DTO\QueryOptions;
use Jengo\Schema\Query\Query;
use Jengo\Schema\Reflection\SchemaReflector;
use Tests\Support\Entity\User;
use Tests\Support\Models\UserFileModel;
use Tests\Support\Models\UserModel;
use Tests\Support\Schemas\UserFileSchema;
use Tests\Support\Schemas\UserSchema;

final class SchemaTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $namespace = null;

    public function setUp(): void
    {
        $this->loadDependencies();
        $this->migrateDatabase();
        $this->generateData();
    }

    public function tearDown(): void
    {
        $this->regressDatabase();
        $this->loadDependencies();
        $this->migrateDatabase();
    }

    public function testReflector(): void
    {
        $userSchemaMeta = SchemaReflector::reflect(UserSchema::class);

        $this->assertSame(UserSchema::class, $userSchemaMeta->schemaClass);
        $this->assertSame(UserModel::class, $userSchemaMeta->modelClass);
        $this->assertSame(User::class, $userSchemaMeta->entityClass);
        $this->assertSame('id', $userSchemaMeta->primaryKey->name);
        $this->assertSame(false, $userSchemaMeta->primaryKey->searchable);
        $this->assertSame(false, $userSchemaMeta->primaryKey->derived);
    }

    public function testGraph(): void
    {
        $userFileSchema = SchemaReflector::reflect(UserFileSchema::class);
        $graph = RelationshipGraph::build(
            rootSchema: $userFileSchema,
            derivePaths: ['user'],
        );

        $this->assertTrue(true);
    }

    public function testQuery(): void
    {
        $result = Query::run(
            schema: UserFileSchema::class,
            options: new QueryOptions(
                derive: ['user'],
                pagination: new PaginationOptions(
                    limit: 1,
                    page: 4
                )
            )
        );

        $this->assertTrue(true);

        var_dump($result);
    }

    private function generateData(): void
    {
        $conn = Database::connect('tests');
        $userModel = new UserModel($conn);
        $userFileModel = new UserFileModel($conn);

        $users = new Fabricator($userModel)->make(10);
        $userFiles = new Fabricator($userFileModel)->make(10);

        $conn->table('users')->insertBatch($users);
        $conn->table('user_files')->insertBatch($userFiles);
    }
}
