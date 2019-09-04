<?php

use App\Listeners\User\LogSuccessfulLoginAttempt;
use App\Services\Security\LoginThrottlingServiceInterface;
use Test\SecurityHelper;

/**
 * Class LogSuccessfulLoginAttemptTest
 */
final class LogSuccessfulLoginAttemptTest extends TestCase
{
    use SecurityHelper;

    //region Tests

    /**
     * @return void
     */
    public function testHandleSuccessfulLoginAttempt(): void
    {
        // TODO
    }

    //endregion

    private function getLogSuccessfulLoginAttemptListener(
        LoginThrottlingServiceInterface $loginThrottlingService = null
    ): LogSuccessfulLoginAttempt {
        return new LogSuccessfulLoginAttempt($loginThrottlingService ?: $this->createLoginThrottlingService());
    }
}
