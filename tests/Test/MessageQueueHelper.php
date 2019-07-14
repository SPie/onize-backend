<?php

namespace Test;

use Illuminate\Contracts\Queue\Queue;
use Mockery as m;
use Mockery\MockInterface;

/**
 * Trait MessageQueueHelper
 *
 * @package Test
 */
trait MessageQueueHelper
{

    //region Mocks

    /**
     * @return Queue|MockInterface
     */
    protected function createQueueService(): Queue
    {
        return m::spy(Queue::class);
    }

    //endregion

    //region Assertions

    /**
     * @param Queue|MockInterface $messageQueueService
     * @param string              $jobIdentifier
     * @param array               $context
     * @param string              $queue
     *
     * @return $this
     */
    protected function assertQueuePush(
        MockInterface $messageQueueService,
        string $jobIdentifier,
        array $context,
        string $queue
    )
    {
        $messageQueueService
            ->shouldHaveReceived('push')
            ->with($jobIdentifier, $context, $queue)
            ->once();

        return $this;
    }

    //endregion
}