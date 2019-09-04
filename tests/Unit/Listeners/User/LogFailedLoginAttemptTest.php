<?php

use App\Exceptions\InvalidParameterException;
use App\Listeners\User\LogFailedLoginAttempt;
use App\Services\Security\LoginThrottlingServiceInterface;
use SPie\LaravelJWT\Events\FailedLoginAttempt;
use Test\ModelHelper;
use Test\RepositoryHelper;
use Test\SecurityHelper;
use Test\UserHelper;

/**
 * Class LogFailedLoginAttemptTest
 */
final class LogFailedLoginAttemptTest extends TestCase
{
    use ModelHelper;
    use RepositoryHelper;
    use SecurityHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testHandleFailedLoginAttempt(): void
    {
        $email = $this->getFaker()->safeEmail;
        $ipAddress = $this->getFaker()->ipv4;
        $loginThrottlingService = $this->createLoginThrottlingService();

        $this->getLogFailedLoginAttemptListener($loginThrottlingService)->handle(
            $this->createFailedLoginAttemptEvent(
                [
                    'email'     => $email,
                    'password'  => $this->getFaker()->password,
                    'ipAddress' => $ipAddress,
                ]
            )
        );

        $this->assertLoginThrottlingServiceLogLoginAttempt($loginThrottlingService, $ipAddress, $email, false);
    }

    /**
     * @return void
     */
    public function testHandleFailedLoginAttemptWithoutIpAddress(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getLogFailedLoginAttemptListener()->handle(
            $this->createFailedLoginAttemptEvent(
                [
                    'email'    => $this->getFaker()->safeEmail,
                    'password' => $this->getFaker()->password,
                ]
            )
        );
    }

    /**
     * @return void
     */
    public function testHandleFailedLoginAttemptWithoutIdentifier(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getLogFailedLoginAttemptListener()->handle(
            $this->createFailedLoginAttemptEvent(
                [
                    'ipAddress' => $this->getFaker()->ipv4,
                    'password'  => $this->getFaker()->password,
                ]
            )
        );
    }

    /**
     * @return void
     */
    public function testHandleFailedLoginAttemptWithInvalidParametersForCreate(): void
    {
        $email = $this->getFaker()->safeEmail;
        $ipAddress = $this->getFaker()->ipv4;
        $loginThrottlingService = $this->createLoginThrottlingService();
        $this->mockLoginThrottlingServiceLogLoginAttempt(
            $loginThrottlingService,
            new InvalidParameterException(),
            $ipAddress,
            $email,
            false
        );

        $this->expectException(InvalidParameterException::class);

        $this->getLogFailedLoginAttemptListener($loginThrottlingService)->handle(
            $this->createFailedLoginAttemptEvent(
                [
                    'email'     => $email,
                    'password'  => $this->getFaker()->password,
                    'ipAddress' => $ipAddress,
                ]
            )
        );
    }

    //endregion

    /**
     * @param LoginThrottlingServiceInterface $loginThrottlingService
     *
     * @return LogFailedLoginAttempt
     */
    private function getLogFailedLoginAttemptListener(
        LoginThrottlingServiceInterface $loginThrottlingService = null
    ): LogFailedLoginAttempt {
        return new LogFailedLoginAttempt(
            $loginThrottlingService ?: $this->createLoginThrottlingService()
        );
    }

    /**
     * @param array $credentials
     *
     * @return FailedLoginAttempt
     */
    private function createFailedLoginAttemptEvent(array $credentials = []): FailedLoginAttempt
    {
        return new FailedLoginAttempt($credentials);
    }
}
