<?php

use App\Models\User\LoginAttemptModel;
use App\Repositories\User\LoginAttemptRepository;
use App\Services\Security\LoginThrottlingService;
use Mockery\MockInterface;
use Test\RepositoryHelper;
use Test\UserHelper;

/**
 * Class LoginThrottlingServiceTest
 */
final class LoginThrottlingServiceTest extends TestCase
{
    use RepositoryHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testIsLoginBlockedWithoutTooManyLoginAttempts(): void
    {
        $ipAddress = $this->getFaker()->ipv4;
        $identifier = $this->getFaker()->uuid;
        $throttlingTimeInMinutes = $this->getFaker()->numberBetween();
        $loginAttemptRepository = $this->createLoginAttemptRepository();
        $this->mockLoginAttemptRepositoryGetLoginAttemptsForUserSince(
            $loginAttemptRepository,
            $this->createCollection([
                $this->createFailedLoginAttemptModel(),
                $this->createFailedLoginAttemptModel(),
            ]),
            $ipAddress,
            $identifier,
            (new \DateTimeImmutable())->sub(new \DateInterval(\sprintf('PT%dM', $throttlingTimeInMinutes)))
        );

        $this->assertFalse(
            $this->getLoginThrottlingService(
                $loginAttemptRepository,
                3,
                $throttlingTimeInMinutes
            )->isLoginBlocked($ipAddress, $identifier)
        );
    }

    /**
     * @return void
     */
    public function testIsLoginBlockedWithoutLoginAttempts(): void
    {
        $ipAddress = $this->getFaker()->ipv4;
        $identifier = $this->getFaker()->uuid;
        $throttlingTimeInMinutes = $this->getFaker()->numberBetween();
        $loginAttemptRepository = $this->createLoginAttemptRepository();
        $this->mockLoginAttemptRepositoryGetLoginAttemptsForUserSince(
            $loginAttemptRepository,
            $this->createCollection(),
            $ipAddress,
            $identifier,
            (new \DateTimeImmutable())->sub(new \DateInterval(\sprintf('PT%dM', $throttlingTimeInMinutes)))
        );

        $this->assertFalse(
            $this->getLoginThrottlingService(
                $loginAttemptRepository,
                3,
                $throttlingTimeInMinutes
            )->isLoginBlocked($ipAddress, $identifier)
        );
    }

    /**
     * @return void
     */
    public function testIsLoginBlockedWithTooManyLoginAttempts(): void
    {
        $ipAddress = $this->getFaker()->ipv4;
        $identifier = $this->getFaker()->uuid;
        $throttlingTimeInMinutes = $this->getFaker()->numberBetween();
        $loginAttemptRepository = $this->createLoginAttemptRepository();
        $this->mockLoginAttemptRepositoryGetLoginAttemptsForUserSince(
            $loginAttemptRepository,
            $this->createCollection([
                $this->createFailedLoginAttemptModel(),
                $this->createFailedLoginAttemptModel(),
                $this->createFailedLoginAttemptModel(),
            ]),
            $ipAddress,
            $identifier,
            (new \DateTimeImmutable())->sub(new \DateInterval(\sprintf('PT%dM', $throttlingTimeInMinutes)))
        );

        $this->assertTrue(
            $this->getLoginThrottlingService(
                $loginAttemptRepository,
                3,
                $throttlingTimeInMinutes
            )->isLoginBlocked($ipAddress, $identifier)
        );
    }

    /**
     * @return void
     */
    public function testIsLoginBlockedWithSuccessfulLoginAttempt(): void
    {
        $ipAddress = $this->getFaker()->ipv4;
        $identifier = $this->getFaker()->uuid;
        $throttlingTimeInMinutes = $this->getFaker()->numberBetween();
        $loginAttemptRepository = $this->createLoginAttemptRepository();
        $this->mockLoginAttemptRepositoryGetLoginAttemptsForUserSince(
            $loginAttemptRepository,
            $this->createCollection([
                $this->createFailedLoginAttemptModel(),
                $this->createFailedLoginAttemptModel(),
                $this->createSuccessfulLoginAttemptModel(),
                $this->createFailedLoginAttemptModel(),
            ]),
            $ipAddress,
            $identifier,
            (new \DateTimeImmutable())->sub(new \DateInterval(\sprintf('PT%dM', $throttlingTimeInMinutes)))
        );

        $this->assertFalse(
            $this->getLoginThrottlingService(
                $loginAttemptRepository,
                3,
                $throttlingTimeInMinutes
            )->isLoginBlocked($ipAddress, $identifier)
        );
    }

    //endregion

    /**
     * @param LoginAttemptRepository|null $loginAttemptRepository
     * @param int|null                    $maxLoginAttempts
     * @param int|null                    $throttlingTimeInMinutes
     *
     * @return LoginThrottlingService
     */
    private function getLoginThrottlingService(
        LoginAttemptRepository $loginAttemptRepository = null,
        int $maxLoginAttempts = null,
        int $throttlingTimeInMinutes = null
    ): LoginThrottlingService {
        return new LoginThrottlingService(
            $loginAttemptRepository ?: $this->createLoginAttemptRepository(),
            $maxLoginAttempts ?: $this->getFaker()->numberBetween(),
            $throttlingTimeInMinutes ?: $this->getFaker()->numberBetween()
        );
    }

    /**
     * @return LoginAttemptModel|MockInterface
     */
    private function createFailedLoginAttemptModel(): LoginAttemptModel
    {
        $loginAttemptModel = $this->createLoginAttemptModel();
        $this->mockLoginAttemptModelWasSuccess($loginAttemptModel, false);

        return $loginAttemptModel;
    }

    /**
     * @return LoginAttemptModel|MockInterface
     */
    private function createSuccessfulLoginAttemptModel(): LoginAttemptModel
    {
        $loginAttemptModel = $this->createLoginAttemptModel();
        $this->mockLoginAttemptModelWasSuccess($loginAttemptModel, true);

        return $loginAttemptModel;
    }
}
