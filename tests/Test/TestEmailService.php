<?php

namespace Test;

use App\Services\Email\EmailService;

/**
 * Class TestEmailService
 *
 * @package Test
 */
final class TestEmailService implements EmailService
{

    /**
     * @var array
     */
    private $queuedEmails;

    /**
     * TestEmailService constructor.
     */
    public function __construct()
    {
        $this->queuedEmails = [];
    }

    /**
     * @return array
     */
    public function getQueuedEmails(): array
    {
        return $this->queuedEmails;
    }

    /**
     * @param string $identifier
     *
     * @return array
     */
    public function getQueuedEmailsByIdentifier(string $identifier): array
    {
        return $this->queuedEmails[$identifier] ?? [];
    }

    /**
     * @param string $identifier
     * @param string $recipient
     * @param array  $context
     *
     * @return EmailService
     */
    private function queueEmail(string $identifier, string $recipient, array $context = []): EmailService
    {
        if (!isset($this->queuedEmails[$identifier])) {
            $this->queuedEmails[$identifier] = [];
        }

        $this->queuedEmails[$identifier][] = [
            'recipient' => $recipient,
            'context'   => $context,
        ];

        return $this;
    }

    /**
     * @param string $recipient
     * @param string $finishUrl
     *
     * @return EmailService
     */
    public function passwordResetEmail(string $recipient, string $finishUrl): EmailService
    {
        return $this->queueEmail('passwordReset', $recipient, ['resetToken' => $finishUrl]);
    }

    /**
     * @param string $recipient
     * @param string $inviteUrl
     *
     * @return EmailService
     */
    public function projectInvite(string $recipient, string $inviteUrl): EmailService
    {
        return $this->queueEmail('projectInvite', $recipient, ['inviteUrl' => $inviteUrl]);
    }
}
