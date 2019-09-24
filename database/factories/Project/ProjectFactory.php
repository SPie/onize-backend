<?php

use App\Models\Project\ProjectDoctrineModel;
use App\Models\User\UserDoctrineModel;
use Faker\Generator as Faker;
use LaravelDoctrine\ORM\Testing\Factory;

/**
 * @var Factory $factory
 */

$factory->define(ProjectDoctrineModel::class, function (Faker $faker, array $attributes = []) {
    return [
        ProjectDoctrineModel::PROPERTY_UUID        => $attributes[ProjectDoctrineModel::PROPERTY_UUID] ?? $faker->uuid,
        ProjectDoctrineModel::PROPERTY_LABEL       => $attributes[ProjectDoctrineModel::PROPERTY_LABEL] ?? $faker->word,
        ProjectDoctrineModel::PROPERTY_DESCRIPTION => $attributes[ProjectDoctrineModel::PROPERTY_DESCRIPTION] ?? $faker->text,
        ProjectDoctrineModel::PROPERTY_USER        => $attributes[ProjectDoctrineModel::PROPERTY_USER] ?? entity(UserDoctrineModel::class, 1)->create(),
    ];
});
