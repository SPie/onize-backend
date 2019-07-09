<?php

use App\Exceptions\Service\MessageQueue\QueuePushException;
use App\Services\MessageQueue\RabbitMQService;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Test\MessageQueueHelper;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

/**
 * Class RabbitMQServiceTest
 */
final class RabbitMQServiceTest extends BaseTestCase
{

    use MessageQueueHelper;
    use TestCaseHelper;

    //region Tests

    /**
     * @return void
     */
    public function testQueueMessage(): void
    {
        $jobIdentifier = $this->getFaker()->uuid;
        $queue = $this->getFaker()->uuid;
        $context = [$this->getFaker()->uuid => $this->getFaker()->uuid];
        $rabbitMQQueue = $this->createRabbitMQQueue();
        $rabbitMQService = $this->getRabbitMQService($rabbitMQQueue);

        $this->assertEquals($rabbitMQService, $rabbitMQService->queueMessage($jobIdentifier, $queue, $context));
        $this->assertRabbitMQQueuePush($rabbitMQQueue, $jobIdentifier, $context, $queue);
    }

    /**
     * @return void
     */
    public function testQueueMessageWithException(): void
    {
        $jobIdentifier = $this->getFaker()->uuid;
        $queue = $this->getFaker()->uuid;
        $context = [$this->getFaker()->uuid => $this->getFaker()->uuid];
        $rabbitMQQueue = $this->createRabbitMQQueue();
        $rabbitMQService = $this->getRabbitMQService($rabbitMQQueue);
        $this->mockRabbitMQQueuePush($rabbitMQQueue, new \RuntimeException(), $jobIdentifier, $context, $queue);

        $this->expectException(QueuePushException::class);

        $rabbitMQService->queueMessage($jobIdentifier, $queue, $context);
    }

    //endregion

    //region Mocks

    /**
     * @param RabbitMQQueue|null $rabbitMQQueue
     *
     * @return RabbitMQService
     */
    private function getRabbitMQService(RabbitMQQueue $rabbitMQQueue = null): RabbitMQService
    {
        return new RabbitMQService( $rabbitMQQueue ?: $this->createRabbitMQQueue());
    }

    //endregion
}