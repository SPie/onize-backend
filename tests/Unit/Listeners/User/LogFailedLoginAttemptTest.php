<?php

use App\Exceptions\InvalidParameterException;
use App\Listeners\User\LogFailedLoginAttempt;
use App\Services\Security\LoginThrottlingServiceInterface;
use Psr\Log\LoggerInterface;
use SPie\LaravelJWT\Events\FailedLoginAttempt;
use Test\LoggerHelper;
use Test\ModelHelper;
use Test\RepositoryHelper;
use Test\SecurityHelper;
use Test\UserHelper;

/**
 * Class LogFailedLoginAttemptTest
 */
final class LogFailedLoginAttemptTest extends TestCase
{
    use LoggerHelper;
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
                    'email'    => $email,
                    'password' => $this->getFaker()->password,
                ],
                $ipAddress
            )
        );

        $this->assertLoginThrottlingServiceLogLoginAttempt($loginThrottlingService, $ipAddress, $email, false);
    }

    /**
     * @return void
     */
    public function testHandleFailedLoginAttemptWithoutIpAddress(): void
    {
        $loginThrottlingService = $this->createLoginThrottlingService();
        $logger = $this->createLogger();

        $this->getLogFailedLoginAttemptListener($loginThrottlingService, $logger)->handle(
            $this->createFailedLoginAttemptEvent(
                [
                    'email'    => $this->getFaker()->safeEmail,
                    'password' => $this->getFaker()->password,
                ]
            )
        );

        $loginThrottlingService->shouldNotHaveReceived('logLoginAttempt');
        $this->assertLoggerWarning($logger, 'Could not log login attempt: Missing ipAddress');
    }

    /**
     * @return void
     */
    public function testHandleFailedLoginAttemptWithoutIdentifier(): void
    {
        $loginThrottlingService = $this->createLoginThrottlingService();
        $logger = $this->createLogger();

        $this->getLogFailedLoginAttemptListener($loginThrottlingService, $logger)->handle(
            $this->createFailedLoginAttemptEvent(
                [
                    'password' => $this->getFaker()->password,
                ],
                $this->getFaker()->ipv4
            )
        );

        $loginThrottlingService->shouldNotHaveReceived('logLoginAttempt');
        $this->assertLoggerWarning($logger, 'Could not log login attempt: Missing identifier');
    }

    /**
     * @return void
     */
    public function testHandleFailedLoginAttemptWithInvalidParametersForCreate(): void
    {
        $email = $this->getFaker()->safeEmail;
        $ipAddress = $this->getFaker()->ipv4;
        $logger = $this->createLogger();
        $invalidParameterException = new InvalidParameterException($this->getFaker()->text);
        $loginThrottlingService = $this->createLoginThrottlingService();
        $this->mockLoginThrottlingServiceLogLoginAttempt(
            $loginThrottlingService,
            $invalidParameterException,
            $ipAddress,
            $email,
            false
        );

        $this->getLogFailedLoginAttemptListener($loginThrottlingService, $logger)->handle(
            $this->createFailedLoginAttemptEvent(
                [
                    'email'    => $email,
                    'password' => $this->getFaker()->password,
                ],
                $ipAddress
            )
        );

        $this->assertLoggerWarning($logger, 'Could not log login attempt: ' . $invalidParameterException->getMessage());
    }

    //endregion

    /**
     * @param LoginThrottlingServiceInterface $loginThrottlingService
     * @param LoggerInterface|null            $logger
     *
     * @return LogFailedLoginAttempt
     */
    private function getLogFailedLoginAttemptListener(
        LoginThrottlingServiceInterface $loginThrottlingService = null,
        LoggerInterface $logger = null
    ): LogFailedLoginAttempt {
        return new LogFailedLoginAttempt(
            $loginThrottlingService ?: $this->createLoginThrottlingService(),
            $logger ?: $this->createLogger()
        );
    }

    /**
     * @param array       $credentials
     * @param string|null $ipAddress
     *
     * @return FailedLoginAttempt
     */
    private function createFailedLoginAttemptEvent(array $credentials = [], string $ipAddress = null): FailedLoginAttempt
    {
        return new FailedLoginAttempt($credentials, $ipAddress);
    }
}
