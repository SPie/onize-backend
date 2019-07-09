<?php

namespace Test;

use App\Services\MessageQueue\MessageQueueService;
use Mockery as m;
use Mockery\MockInterface;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

/**
 * Trait MessageQueueHelper
 *
 * @package Test
 */
trait MessageQueueHelper
{

    //region Mocks

    /**
     * @return MessageQueueService|MockInterface
     */
    protected function createMessageQueueService(): MessageQueueService
    {
        return m::spy(MessageQueueService::class);
    }

    /**
     * @return RabbitMQQueue
     */
    protected function createRabbitMQQueue(): RabbitMQQueue
    {
        return m::spy(RabbitMQQueue::class);
    }

    /**
     * @param RabbitMQQueue|MockInterface $rabbitMQQueue
     * @param string|\Exception|null      $correlationId
     * @param string                      $job
     * @param array                       $data
     * @param string                      $queue
     *
     * @return $this
     */
    protected function mockRabbitMQQueuePush(
        MockInterface $rabbitMQQueue,
        $correlationId,
        string $job,
        array $data,
        string $queue
    )
    {
        $expectation = $rabbitMQQueue
            ->shouldReceive('push')
            ->with($job, $data, $queue);

        if ($correlationId instanceof \Exception) {
            $expectation->andThrow($correlationId);

            return $this;
        }

        $expectation->andReturn($correlationId);

        return $this;
    }

    //endregion

    //region Assertions

    /**
     * @param MessageQueueService|MockInterface $messageQueueService
     * @param string                            $jobIdentifier
     * @param string                            $queue
     * @param array                             $context
     *
     * @return $this
     */
    protected function assertMessageQueueServiceQueueMessage(
        MockInterface $messageQueueService,
        string $jobIdentifier,
        string $queue,
        array $context
    )
    {
        $messageQueueService
            ->shouldHaveReceived('queueMessage')
            ->with($jobIdentifier, $queue, $context)
            ->once();

        return $this;
    }

    /**
     * @param RabbitMQQueue|MockInterface $rabbitMQQueue
     * @param string                      $job
     * @param array                       $data
     * @param string                      $queue
     *
     * @return $this
     */
    protected function assertRabbitMQQueuePush(MockInterface $rabbitMQQueue, string $job, array $data, string $queue)
    {
        $rabbitMQQueue
            ->shouldHaveReceived('push')
            ->with($job, $data, $queue)
            ->once();

        return $this;
    }

    //endregion
}