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
     * @param string $resetToken
     *
     * @return EmailService
     */
    public function passwordResetEmail(string $recipient, string $resetToken): EmailService;
}