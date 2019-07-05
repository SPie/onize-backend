<?php

namespace App\Services\Email;

use App\Services\MessageQueue\MessageQueueService;

/**
 * Class QueuedEmailService
 *
 * @package App\Services\Email
 */
final class QueuedEmailService implements EmailService
{

    const QUEUE_IDENTIFIER_PASSWORD_RESET = 'passwordReset';

    const CONTEXT_PARAMETER_RECIPIENT   = 'recipient';
    const CONTEXT_PARAMETER_RESET_TOKEN = 'resetToken';

    /**
     * @var MessageQueueService
     */
    private $messageQueueService;

    /**
     * QueuedEmailService constructor.
     *
     * @param MessageQueueService $messageQueueService
     */
    public function __construct(MessageQueueService $messageQueueService)
    {
        $this->messageQueueService = $messageQueueService;
    }

    /**
     * @return MessageQueueService
     */
    private function getMessageQueueService(): MessageQueueService
    {
        return $this->messageQueueService;
    }

    /**
     * @param string $recipient
     * @param string $resetToken
     *
     * @return EmailService
     */
    public function passwordResetEmail(string $recipient, string $resetToken): EmailService
    {
        $this->getMessageQueueService()->queueMessage(
            self::QUEUE_IDENTIFIER_PASSWORD_RESET,
            [
                self::CONTEXT_PARAMETER_RECIPIENT   => $recipient,
                self::CONTEXT_PARAMETER_RESET_TOKEN => $resetToken,
            ]
        );

        return $this;
    }
}