<?php

use App\Listeners\User\LogSuccessfulLoginAttempt;
use App\Services\Security\LoginThrottlingServiceInterface;
use Psr\Log\LoggerInterface;
use SPie\LaravelJWT\Contracts\JWT;
use SPie\LaravelJWT\Contracts\JWTAuthenticatable;
use SPie\LaravelJWT\Events\Login;
use Test\AuthHelper;
use Test\LoggerHelper;
use Test\SecurityHelper;
use Test\UserHelper;

/**
 * Class LogSuccessfulLoginAttemptTest
 */
final class LogSuccessfulLoginAttemptTest extends TestCase
{
    use AuthHelper;
    use LoggerHelper;
    use SecurityHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testHandleSuccessfulLoginAttempt(): void
    {
        $loginThrottlingService = $this->createLoginThrottlingService();
        $identifier = $this->getFaker()->safeEmail;
        $user = $this->createUserModel();
        $this->mockUserModelGetAuthIdentifier($user, $identifier);
        $ipAddress = $this->getFaker()->ipv4;

        $this->getLogSuccessfulLoginAttemptListener($loginThrottlingService)->handle(
            $this->createLoginEvent($user, $this->createJWT(), $ipAddress)
        );

        $this->assertLoginThrottlingServiceLogLoginAttempt($loginThrottlingService, $ipAddress, $identifier, true);
    }

    /**
     * @return void
     */
    public function testHandleSuccessfulLoginAttemptWithoutIpAddress(): void
    {
        $loginThrottlingService = $this->createLoginThrottlingService();
        $logger = $this->createLogger();
        $user = $this->createUserModel();
        $this->mockUserModelGetAuthIdentifier($user, $this->getFaker()->safeEmail);

        $this->getLogSuccessfulLoginAttemptListener($loginThrottlingService, $logger)->handle(
            $this->createLoginEvent($user, $this->createJWT())
        );

        $loginThrottlingService->shouldNotHaveReceived('logLoginAttempt');
        $this->assertLoggerWarning($logger, 'Could not log login attempt: Missing ipAddress');
    }

    /**
     * @return void
     */
    public function testHandleSuccessfulLoginAttemptWithoutIdentifier(): void
    {
        $loginThrottlingService = $this->createLoginThrottlingService();
        $logger = $this->createLogger();

        $this->getLogSuccessfulLoginAttemptListener($loginThrottlingService, $logger)->handle(
            $this->createLoginEvent($this->createUserModel(), $this->createJWT(), $this->getFaker()->ipv4)
        );

        $loginThrottlingService->shouldNotHaveReceived('logLoginAttempt');
        $this->assertLoggerWarning($logger, 'Could not log login attempt: Missing identifier');
    }

    //endregion

    private function getLogSuccessfulLoginAttemptListener(
        LoginThrottlingServiceInterface $loginThrottlingService = null,
        LoggerInterface $logger = null
    ): LogSuccessfulLoginAttempt {
        return new LogSuccessfulLoginAttempt(
            $loginThrottlingService ?: $this->createLoginThrottlingService(),
            $logger ?: $this->createLogger()
        );
    }

    /**
     * @param JWTAuthenticatable|null $user
     * @param JWT|null                $accessToken
     * @param string|null             $ipAddress
     *
     * @return Login
     */
    private function createLoginEvent(
        JWTAuthenticatable $user = null,
        JWT $accessToken = null,
        string $ipAddress = null
    ): Login {
        return new Login(
            $user ?: $this->createUserModel(),
            $accessToken ?: $this->createJWT(),
            $ipAddress
        );
    }
}
