<?php

use App\Models\User\LoginAttemptModel;
use App\Models\User\LoginAttemptModelFactory;
use App\Repositories\User\LoginAttemptRepository;
use App\Services\Security\LoginThrottlingService;
use Mockery as m;
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
                $this->createLoginAttemptModelFactory(),
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
                $this->createLoginAttemptModelFactory(),
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
                $this->createLoginAttemptModelFactory(),
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
                $this->createLoginAttemptModelFactory(),
                3,
                $throttlingTimeInMinutes
            )->isLoginBlocked($ipAddress, $identifier)
        );
    }

    /**
     * @return void
     */
    public function testLogLoginAttempt(): void
    {
        $ipAddress = $this->getFaker()->ipv4;
        $identifier = $this->getFaker()->uuid;
        $success = $this->getFaker()->boolean;
        $loginAttempt = $this->createLoginAttemptModel();
        $loginAttemptModelFactory = $this->createLoginAttemptModelFactory();
        $this->mockLoginAttemptModelFactoryCreate(
            $loginAttemptModelFactory,
            $loginAttempt,
            [
                'ipAddress'   => $ipAddress,
                'identifier'  => $identifier,
                'attemptedAt' => new \DateTimeImmutable(),
                'success'     => $success,
            ]
        );
        $loginAttemptRepository = $this->createLoginAttemptRepository();
        $loginThrottlingService = $this->getLoginThrottlingService($loginAttemptRepository, $loginAttemptModelFactory);

        $this->assertEquals(
            $loginThrottlingService,
            $loginThrottlingService->logLoginAttempt($ipAddress, $identifier, $success)
        );
        $this->assertRepositorySave($loginAttemptRepository, $loginAttempt);
    }

    //endregion

    /**
     * @param LoginAttemptRepository|null   $loginAttemptRepository
     * @param LoginAttemptModelFactory|null $loginAttemptModelFactory
     * @param int|null                      $maxLoginAttempts
     * @param int|null                      $throttlingTimeInMinutes
     *
     * @return LoginThrottlingService
     */
    private function getLoginThrottlingService(
        LoginAttemptRepository $loginAttemptRepository = null,
        LoginAttemptModelFactory $loginAttemptModelFactory = null,
        int $maxLoginAttempts = null,
        int $throttlingTimeInMinutes = null
    ): LoginThrottlingService {
        return new LoginThrottlingService(
            $loginAttemptRepository ?: $this->createLoginAttemptRepository(),
            $loginAttemptModelFactory ?: $this->createLoginAttemptModelFactory(),
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

    /**
     * @param LoginAttemptModelFactory|MockInterface $loginAttemptModelFactory
     * @param LoginAttemptModel|\Exception           $loginAttemptModel
     * @param array                                  $data
     *
     * @return LoginThrottlingServiceTest
     */
    private function mockLoginAttemptModelFactoryCreate(
        MockInterface $loginAttemptModelFactory,
        $loginAttemptModel,
        array $data
    ): LoginThrottlingServiceTest {
        $loginAttemptModelFactory
            ->shouldReceive('create')
            ->with(m::on(function ($argument) use ($data) {
                if (\count($argument) != 4) {
                    return false;
                }

                return (
                    $argument['ipAddress'] == $data['ipAddress']
                    && $argument['identifier'] == $data['identifier']
                    && $argument['attemptedAt'] >= $data['attemptedAt']->sub(new \DateInterval(\sprintf('PT%dS', 5)))
                    && $argument['attemptedAt'] <= $data['attemptedAt']->add(new \DateInterval(\sprintf('PT%dS', 5)))
                    && $argument['success'] == $data['success']
                );
            }))
            ->andThrow($loginAttemptModel);

        return $this;
    }
}
