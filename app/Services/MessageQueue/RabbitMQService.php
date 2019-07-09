<?php

namespace App\Services\MessageQueue;

use App\Exceptions\Service\MessageQueue\QueuePushException;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

/**
 * Class RabbitMQService
 *
 * @package App\Services\MessageQueue
 */
final class RabbitMQService implements MessageQueueService
{

    /**
     * @var RabbitMQQueue
     */
    private $rabbitMQQueue;

    /**
     * RabbitMQService constructor.
     *
     * @param RabbitMQQueue $rabbitMQQueue
     */
    public function __construct(RabbitMQQueue $rabbitMQQueue)
    {
        $this->rabbitMQQueue = $rabbitMQQueue;
    }

    /**
     * @return RabbitMQQueue
     */
    private function getRabbitMQQueue(): RabbitMQQueue
    {
        return $this->rabbitMQQueue;
    }

    /**
     * @param string $jobIdentifier
     * @param string $queue
     * @param array  $context
     *
     * @return MessageQueueService
     */
    public function queueMessage(string $jobIdentifier, string $queue, array $context = []): MessageQueueService
    {
        try {
            $this->getRabbitMQQueue()->push($jobIdentifier, $context, $queue);
        } catch (\RuntimeException $e) {
            throw new QueuePushException();
        }

        return $this;
    }
}