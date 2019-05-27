<?php

use App\Models\User\UserDoctrineModel;
use App\Models\User\UserModelInterface;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;
use LaravelDoctrine\ORM\Testing\Factory;

/**
 * @var Factory $factory
 */


$factory->define(UserDoctrineModel::class, function (Faker $faker, array $attributes = []) {
    return [
        UserModelInterface::PROPERTY_EMAIL    => $attributes[UserModelInterface::PROPERTY_EMAIL] ?? $faker->safeEmail,
        UserModelInterface::PROPERTY_PASSWORD => $attributes[UserModelInterface::PROPERTY_PASSWORD] ?? Hash::make($faker->password()),
        UserModelInterface::PROPERTY_REFRESH_TOKENS => $attributes[UserModelInterface::PROPERTY_REFRESH_TOKENS] ?? []
    ];
});
