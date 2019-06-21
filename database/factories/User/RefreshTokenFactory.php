<?php

use App\Models\User\RefreshTokenDoctrineModel;
use App\Models\User\RefreshTokenModel;
use App\Models\User\UserDoctrineModel;
use Faker\Generator as Faker;
use LaravelDoctrine\ORM\Testing\Factory;

/**
 * @var Factory $factory
 */

$factory->define(RefreshTokenDoctrineModel::class, function (Faker $faker, array $attributes = []) {
    return [
        RefreshTokenModel::PROPERTY_IDENTIFIER  => $attributes[RefreshTokenModel::PROPERTY_IDENTIFIER] ?? $faker->uuid,
        RefreshTokenModel::PROPERTY_VALID_UNTIL => $attributes[RefreshTokenModel::PROPERTY_VALID_UNTIL] ?? $faker->dateTime,
        RefreshTokenModel::PROPERTY_USER => $attributes[RefreshTokenModel::PROPERTY_USER] ?? entity(UserDoctrineModel::class, 1)->create()
    ];
});
