<?php

namespace App\Services\Email;

use Illuminate\Contracts\Queue\Queue;

/**
 * Class QueuedEmailService
 *
 * @package App\Services\Email
 */
final class QueuedEmailService implements EmailService
{
    const JOB_IDENTIFIER_PASSWORD_RESET = 'passwordReset';

    const QUEUE_NAME_EMAIL = 'email';

    const CONTEXT_PARAMETER_RECIPIENT   = 'recipient';
    const CONTEXT_PARAMETER_RESET_TOKEN = 'resetToken';

    /**
     * @var Queue
     */
    private $queue;

    /**
     * QueuedEmailService constructor.
     *
     * @param Queue $queue
     */
    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return Queue
     */
    private function getQueue(): Queue
    {
        return $this->queue;
    }

    /**
     * @param string $recipient
     * @param string $resetToken
     *
     * @return EmailService
     */
    public function passwordResetEmail(string $recipient, string $resetToken): EmailService
    {
        $this->getQueue()->push(
            self::JOB_IDENTIFIER_PASSWORD_RESET,
            [
                self::CONTEXT_PARAMETER_RECIPIENT   => $recipient,
                self::CONTEXT_PARAMETER_RESET_TOKEN => $resetToken,
            ],
            self::QUEUE_NAME_EMAIL
        );

        return $this;
    }
}
