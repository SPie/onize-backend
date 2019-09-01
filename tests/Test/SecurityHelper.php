<?php

namespace Test;

use App\Services\Security\LoginThrottlingServiceInterface;
use Mockery as m;
use Mockery\MockInterface;

/**
 * Trait SecurityHelper
 *
 * @package Test
 */
trait SecurityHelper
{
    /**
     * @return LoginThrottlingServiceInterface|MockInterface
     */
    private function createLoginThrottlingService(): LoginThrottlingServiceInterface
    {
        return m::spy(LoginThrottlingServiceInterface::class);
    }

    /**
     * @param LoginThrottlingServiceInterface|MockInterface $loginThrottlingService
     * @param bool                                          $isBlocked
     * @param string                                        $ipAddress
     * @param string                                        $identifier
     *
     * @return $this
     */
    private function mockLoginThrottlingServiceIsLoginBlocked(
        MockInterface $loginThrottlingService,
        bool $isBlocked,
        string $ipAddress,
        string $identifier
    ) {
        $loginThrottlingService
            ->shouldReceive('isLoginBlocked')
            ->with($ipAddress, $identifier)
            ->andReturn($isBlocked);

        return $this;
    }
}
