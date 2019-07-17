<?php

use App\Services\Email\EmailService;
use Illuminate\Support\Facades\URL;
use Laravel\Lumen\Testing\DatabaseMigrations as EloquentDatabaseMigrations;
use LaravelDoctrine\Migrations\Testing\DatabaseMigrations;
use Test\TestEmailService;

/**
 * Class IntegrationTestCase
 */
abstract class IntegrationTestCase extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(EmailService::class, TestEmailService::class);
    }

    /**
     * @return void
     */
    public function setUpTraits()
    {
        parent::setUpTraits();

        $uses = array_flip(class_uses_recursive(get_class($this)));

        if (isset($uses[DatabaseMigrations::class]) && !isset($uses[EloquentDatabaseMigrations::class])) {
            $this->runDatabaseMigrations();
        }
    }

    /**
     * @param string $routeName
     *
     * @return string
     */
    protected function getUrl(string $routeName): string
    {
        return URL::route($routeName);
    }

    /**
     * @return TestEmailService
     */
    protected function getEmailService(): TestEmailService
    {
        return $this->app->get(EmailService::class);
    }
}
