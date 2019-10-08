<?php

namespace App\Services\Email;

/**
 * Interface EmailService
 *
 * @package App\Services\Email
 */
interface EmailService
{
    /**
     * @param string $recipient
     * @param string $finishUrl
     *
     * @return EmailService
     */
    public function passwordResetEmail(string $recipient, string $finishUrl): EmailService;

    /**
     * @param string $recipient
     * @param string $inviteUrl
     *
     * @return EmailService
     */
    public function projectInvite(string $recipient, string $inviteUrl): EmailService;
}
