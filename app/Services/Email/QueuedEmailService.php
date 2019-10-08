<?php

namespace App\Services\Email;

use Illuminate\Contracts\Queue\Queue;
use Illuminate\Contracts\View\Factory;

/**
 * Class QueuedEmailService
 *
 * @package App\Services\Email
 */
final class QueuedEmailService implements EmailService
{
    const EMAIL_IDENTIFIER_PASSWORD_RESET = 'passwordReset';

    const QUEUE_NAME_EMAIL = 'email';

    const CONTEXT_PARAMETER_RECIPIENT = 'recipient';
    const CONTEXT_PARAMETER_CONTENT   = 'content';

    const VIEW_DATA_FINISH_URL = 'finishUrl';

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var Factory
     */
    private $viewFactory;

    private $templatesDir;

    /**
     * QueuedEmailService constructor.
     *
     * @param Queue   $queue
     * @param Factory $viewFactory
     * @param string  $templatesDir
     */
    public function __construct(Queue $queue, Factory $viewFactory, string $templatesDir)
    {
        $this->queue = $queue;
        $this->viewFactory = $viewFactory;
        $this->templatesDir = $templatesDir;
    }

    /**
     * @return Queue
     */
    private function getQueue(): Queue
    {
        return $this->queue;
    }

    /**
     * @return Factory
     */
    private function getViewFactory(): Factory
    {
        return $this->viewFactory;
    }

    /**
     * @return string
     */
    private function getTemplatesDir(): string
    {
        return $this->templatesDir;
    }

    /**
     * @param string $recipient
     * @param string $finishUrl
     *
     * @return EmailService
     */
    public function passwordResetEmail(string $recipient, string $finishUrl): EmailService
    {
        $this->getQueue()->push(
            self::EMAIL_IDENTIFIER_PASSWORD_RESET,
            [
                self::CONTEXT_PARAMETER_RECIPIENT => $recipient,
                self::CONTEXT_PARAMETER_CONTENT   => $this->getViewFactory()->make(
                    $this->getTemplatesDir() . '/' . self::EMAIL_IDENTIFIER_PASSWORD_RESET,
                    [self::VIEW_DATA_FINISH_URL => $finishUrl]
                )->render(),
            ],
            self::QUEUE_NAME_EMAIL
        );

        return $this;
    }

    /**
     * @param string $recipient
     * @param string $inviteUrl
     *
     * @return EmailService
     */
    public function projectInvite(string $recipient, string $inviteUrl): EmailService
    {
        // TODO
    }
}
