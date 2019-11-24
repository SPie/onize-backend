<?php

use App\Models\Project\ProjectMetaDataElementDoctrineModel;
use App\Models\Project\ProjectMetaDataElementModel;
use App\Models\Project\ProjectDoctrineModel;
use Faker\Generator as Faker;
use LaravelDoctrine\ORM\Testing\Factory;

/**
 * @var Factory $factory
 */

$factory->define(ProjectMetaDataElementDoctrineModel::class, function (Faker $faker, array $attributes = []) {
    return [
        ProjectMetaDataElementModel::PROPERTY_NAME     => $attributes[ProjectMetaDataElementModel::PROPERTY_NAME] ?? $faker->uuid,
        ProjectMetaDataElementModel::PROPERTY_LABEL    => $attributes[ProjectMetaDataElementModel::PROPERTY_LABEL] ?? $faker->word,
        ProjectMetaDataElementModel::PROPERTY_PROJECT  => $attributes[ProjectMetaDataElementModel::PROPERTY_PROJECT] ?? entity(ProjectDoctrineModel::class, 1)->create(),
        ProjectMetaDataElementModel::PROPERTY_REQUIRED => $attributes[ProjectMetaDataElementModel::PROPERTY_REQUIRED] ?? $faker->boolean,
        ProjectMetaDataElementModel::PROPERTY_IN_LIST  => $attributes[ProjectMetaDataElementModel::PROPERTY_IN_LIST] ?? $faker->boolean,
        ProjectMetaDataElementModel::PROPERTY_POSITION => $attributes[ProjectMetaDataElementModel::PROPERTY_POSITION] ?? $faker->numberBetween(),
        ProjectMetaDataElementModel::PROPERTY_FIELD_TYPE => $attributes[ProjectMetaDataElementModel::PROPERTY_FIELD_TYPE] ?? ProjectMetaDataElementModel::FIELD_TYPE_TEXT,
    ];
});
