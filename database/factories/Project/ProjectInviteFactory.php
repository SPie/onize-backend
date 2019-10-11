<?php

use App\Models\Project\ProjectDoctrineModel;
use App\Models\Project\ProjectInviteDoctrineModel;
use Faker\Generator as Faker;
use LaravelDoctrine\ORM\Testing\Factory;

/**
 * @var Factory $factory
 */

$factory->define(ProjectDoctrineModel::class, function (Faker $faker, array $attributes = []) {
    return [
        ProjectInviteDoctrineModel::PROPERTY_UUID    => $attributes[ProjectInviteDoctrineModel::PROPERTY_UUID] ?? $faker->uuid,
        ProjectInviteDoctrineModel::PROPERTY_TOKEN   => $attributes[ProjectInviteDoctrineModel::PROPERTY_TOKEN] ?? $faker->uuid,
        ProjectInviteDoctrineModel::PROPERTY_EMAIL   => $attributes[ProjectInviteDoctrineModel::PROPERTY_EMAIL] ?? $faker->safeEmail,
        ProjectInviteDoctrineModel::PROPERTY_PROJECT => $attributes[ProjectInviteDoctrineModel::PROPERTY_PROJECT] ?? entity(ProjectDoctrineModel::class, 1)->create(),
    ];
});
