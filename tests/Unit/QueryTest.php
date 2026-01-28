<?php 

declare(strict_types=1);

namespace Tests\Unit;

use Jengo\Schema\Debug\Explain;
use Jengo\Schema\Query\DTO\PaginationOptions;
use Jengo\Schema\Query\DTO\QueryOptions;
use Jengo\Schema\Query\Query;
use Tests\BaseTest;
use Tests\Support\Schemas\UserFileSchema;

final class QueryTest extends BaseTest
{
     public function testQuery(): void
    {
        $options = new QueryOptions(
            derive: ['user'],
            pagination: new PaginationOptions(
                limit: 1,
            ),
            logger: true,
        );

        $result = Query::run(
            schema: UserFileSchema::class,
            options: $options
        );

        $this->assertTrue(true);

        $explain = Explain::run(
            schema: UserFileSchema::class,
            options: $options
        );

        var_dump($result);
    }
}
