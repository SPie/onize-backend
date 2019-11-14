<?php

use App\Models\Project\MetaDataElementDoctrineModel;
use App\Models\Project\MetaDataElementModel;
use App\Models\Project\ProjectDoctrineModel;
use Faker\Generator as Faker;
use LaravelDoctrine\ORM\Testing\Factory;

/**
 * @var Factory $factory
 */

$factory->define(MetaDataElementDoctrineModel::class, function (Faker $faker, array $attributes = []) {
    return [
        MetaDataElementModel::PROPERTY_NAME => $attributes[MetaDataElementModel::PROPERTY_NAME] ?? $faker->uuid,
        MetaDataElementModel::PROPERTY_LABEL => $attributes[MetaDataElementModel::PROPERTY_LABEL] ?? $faker->word,
        MetaDataElementModel::PROPERTY_PROJECT => $attributes[MetaDataElementModel::PROPERTY_PROJECT] ?? entity(ProjectDoctrineModel::class, 1)->create(),
        MetaDataElementModel::PROPERTY_REQUIRED => $attributes[MetaDataElementModel::PROPERTY_REQUIRED] ?? $faker->boolean,
        MetaDataElementModel::PROPERTY_IN_LIST => $attributes[MetaDataElementModel::PROPERTY_IN_LIST] ?? $faker->boolean,
        MetaDataElementModel::PROPERTY_POSITION => $attributes[MetaDataElementModel::PROPERTY_POSITION] ?? $faker->numberBetween(),
    ];
});
