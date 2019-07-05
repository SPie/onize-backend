<?php

namespace Test;

use App\Services\MessageQueue\MessageQueueService;
use Mockery as m;
use Mockery\MockInterface;

/**
 * Trait MessageQueueHelper
 *
 * @package Test
 */
trait MessageQueueHelper
{

    /**
     * @return MessageQueueService|MockInterface
     */
    private function createMessageQueueService(): MessageQueueService
    {
        return m::spy(MessageQueueService::class);
    }

    /**
     * @param MessageQueueService|MockInterface $messageQueueService
     * @param string                            $queueIdentifier
     * @param array                             $context
     *
     * @return $this
     */
    protected function assertMessageQueueServiceQueueMessage(
        MockInterface $messageQueueService,
        string $queueIdentifier,
        array $context
    )
    {
        $messageQueueService
            ->shouldHaveReceived('queueMessage')
            ->with($queueIdentifier, $context)
            ->once();

        return $this;
    }
}