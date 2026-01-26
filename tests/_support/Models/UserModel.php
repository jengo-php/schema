<?php 

declare(strict_types=1);

namespace Tests\Support\Models;

use CodeIgniter\Model;
use Faker\Generator;

final class UserModel extends Model
{
    protected $table = 'users';

    public function fake(Generator $generator): array
    {
        return [
            'first_name' => $generator->firstName(),
            'last_name' => $generator->lastName(),
            'email' => $generator->email(),
            'phone' => $generator->phoneNumber(),
            'address' => $generator->address(),
        ];
    }
}