<?php

use App\Models\User\UserDoctrineModel;
use App\Models\User\UserModelInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Class UsersSeeder
 */
class UsersSeeder extends Seeder
{

    /**
     * @return void
     */
    public function run(): void
    {
        entity(UserDoctrineModel::class, 5)->create(
            [UserModelInterface::PROPERTY_PASSWORD => Hash::make('test1234')
        ]);
    }
}
