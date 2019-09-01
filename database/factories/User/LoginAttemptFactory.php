<?php

use App\Models\User\LoginAttemptDoctrineModel;
use App\Models\User\LoginAttemptModel;
use Faker\Generator as Faker;
use LaravelDoctrine\ORM\Testing\Factory;

/**
 * @var Factory $factory
 */

$factory->define(LoginAttemptDoctrineModel::class, function (Faker $faker, array $attributes = []) {
    return [
        LoginAttemptModel::PROPERTY_IP_ADDRESS   => $attributes[LoginAttemptModel::PROPERTY_IP_ADDRESS] ?: $faker->ipv4,
        LoginAttemptModel::PROPERTY_IDENTIFIER   => $attributes[LoginAttemptModel::PROPERTY_IDENTIFIER] ?: $faker->safeEmail,
        LoginAttemptModel::PROPERTY_ATTEMPTED_AT => $attributes[LoginAttemptModel::PROPERTY_ATTEMPTED_AT] ?: $faker->dateTime,
        LoginAttemptModel::PROPERTY_SUCCESS      => $attributes[LoginAttemptModel::PROPERTY_SUCCESS] ?: $faker->boolean,
    ];
});
