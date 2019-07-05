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
     * @param string $queueIdentifier
     * @param array  $context
     *
     * @return MessageQueueService
     */
    public function queueMessage(string $queueIdentifier, array $context = []): MessageQueueService;
}