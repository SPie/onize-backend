<?php

namespace App\Services\Email;

/**
 * Interface EmailService
 *
 * @package App\Services\Email
 */
interface EmailService
{

    const IDENTIFIER_PASSWORD_RESET = 'password-reset';

    /**
     * @param string $recipient
     * @param string $resetToken
     *
     * @return EmailService
     */
    public function passwordResetEmail(string $recipient, string $resetToken): EmailService;
}