<?php

namespace App\Services\MessageQueue;

use App\Exceptions\Service\MessageQueue\QueuePushException;
use Illuminate\Contracts\Queue\Queue;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

/**
 * Class RabbitMQService
 *
 * @package App\Services\MessageQueue
 */
final class RabbitMQService implements MessageQueueService
{

    /**
     * @var RabbitMQQueue|Queue
     */
    private $rabbitMQQueue;

    /**
     * RabbitMQService constructor.
     *
     * @param RabbitMQQueue|Queue $rabbitMQQueue
     */
    public function __construct(Queue $rabbitMQQueue)
    {
        $this->rabbitMQQueue = $rabbitMQQueue;
    }

    /**
     * @return RabbitMQQueue|Queue
     */
    private function getRabbitMQQueue(): Queue
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