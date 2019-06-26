<?php

use Faker\Factory;
use Faker\Generator;
use Laravel\Lumen\Testing\DatabaseMigrations as EloquentDatabaseMigrations;
use LaravelDoctrine\Migrations\Testing\DatabaseMigrations;

/**
 * Class TestCase
 */
abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{

    const BEARER_AUTHORIZATION = 'Authorization';

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * @return Generator
     */
    protected function getFaker(): Generator
    {
        if (!isset($this->faker)) {
            $this->faker = Factory::create();
        }

        return $this->faker;
    }
}
