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
     * @param string $identifier
     * @param string $recipient
     * @param array  $context
     *
     * @return EmailService
     */
    public function queueEmail(string $identifier, string $recipient, array $context = []): EmailService;
}