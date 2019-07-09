<?php

namespace App\Services\MessageQueue;

/**
 * Interface MessageQueueService
 *
 * @package App\Services\MessageQueue
 */
interface MessageQueueService
{

    /**
     * @param string $jobIdentifier
     * @param string $queue
     * @param array  $context
     *
     * @return MessageQueueService
     */
    public function queueMessage(string $jobIdentifier, string $queue, array $context = []): MessageQueueService;
}