<?php


declare(strict_types=1);

namespace Tests\Unit;

use Jengo\Schema\Metadata\FieldMetadata;
use Jengo\Schema\Reflection\SchemaReflector;
use Jengo\Schema\Support\ArrayUtils;
use Tests\BaseTest;
use Tests\Support\Entity\User;
use Tests\Support\Models\UserModel;
use Tests\Support\Schemas\UserSchema;

final class ReflectorTest extends BaseTest
{
    public function testReflector(): void
    {
        $userSchemaMeta = SchemaReflector::reflect(UserSchema::class);
        $fieldsArray = ArrayUtils::toArray($userSchemaMeta->fields);
        $fieldNames = array_column($fieldsArray, 'name');

        // classes
        $this->assertSame(UserSchema::class, $userSchemaMeta->schemaClass);
        $this->assertSame(UserModel::class, $userSchemaMeta->modelClass);
        $this->assertSame(User::class, $userSchemaMeta->entityClass);

        // primary key
        $this->assertSame('id', $userSchemaMeta->primaryKey->name);
        $this->assertFalse($userSchemaMeta->primaryKey->searchable);
        $this->assertFalse($userSchemaMeta->primaryKey->derived);

        // fields
        $this->assertEquals(4, count($userSchemaMeta->fields));
        $this->assertContains('first_name', $fieldNames);
        $this->assertContains('last_name', $fieldNames);
        $this->assertContains('email', $fieldNames);

        // individual fields

        // first_name
        $firstNameField = $this->getField('first_name', $fieldsArray);

        $this->assertTrue($firstNameField->searchable);
        $this->assertFalse($firstNameField->derived);

        // last_name
        $lastNameField = $this->getField('last_name', $fieldsArray);

        $this->assertTrue($lastNameField->searchable);
        $this->assertFalse($lastNameField->derived);

        // email
        $emailField = $this->getField('email', $fieldsArray);

        $this->assertTrue($emailField->searchable);
        $this->assertFalse($emailField->derived);

        // computed fields
        $this->assertEquals(1, count($userSchemaMeta->computed));

        $fullNameComputedField = $userSchemaMeta->computed[0];

        $this->assertEquals('getFullName', $fullNameComputedField->method);
        $this->assertEquals('full_name', $fullNameComputedField->name);
    }

    private function getField(string $name, array $fields): FieldMetadata
    {
        return new FieldMetadata(...array_values(array_filter(
            $fields,
            fn($field) => $field['name'] === $name
        ))[0]);
    }
}
