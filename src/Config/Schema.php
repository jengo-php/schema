<?php 

declare(strict_types=1);

namespace Jengo\Schema\Config;

use CodeIgniter\Config\BaseConfig;

class Schema extends BaseConfig
{
    /**
     * @var callable[]
     */
    public array $paramCallbacks = [
        /* fn($key, $value, $selectType - 'and or ^or^', $callTime - 'before or after') => [$key => $value] */
    ];
}
