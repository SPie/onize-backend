<?php

namespace Test;

use Mockery as m;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;

/**
 * Trait LoggerHelper
 *
 * @package Test
 */
trait LoggerHelper
{
    //region Mocks

    /**
     * @return LoggerInterface|MockInterface
     */
    private function createLogger(): LoggerInterface
    {
        return m::spy(LoggerInterface::class);
    }

    //endregion

    //region Assertions

    /**
     * @param LoggerInterface|MockInterface $logger
     * @param string                        $messages
     *
     * @return $this
     */
    private function assertLoggerWarning(MockInterface $logger, string $messages)
    {
        $logger
            ->shouldHaveReceived('warning')
            ->with($messages)
            ->once();

        return $this;
    }

    //endregion
}
