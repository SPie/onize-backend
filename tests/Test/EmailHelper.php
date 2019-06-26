<?php

namespace Test;

use App\Services\Email\EmailService;
use Mockery as m;
use Mockery\MockInterface;

/**
 * Trait EmailHelper
 *
 * @package Test
 */
trait EmailHelper
{

    //region Mocks

    /**
     * @return EmailService|MockInterface
     */
    protected function createEmailService(): EmailService
    {
        return m::spy(EmailService::class);
    }

    //endregion

    //region Assertions

    /**
     * @param EmailService|MockInterface $emailService
     * @param string                     $identifier
     * @param string                     $recipient
     * @param array                      $context
     *
     * @return $this
     */
    protected function assertEmailServiceQueueEmail(
        MockInterface $emailService,
        string $identifier,
        string $recipient,
        array $context
    )
    {
        $emailService
            ->shouldHaveReceived('queueEmail')
            ->with($identifier, $recipient, $context)
            ->once();

        return $this;
    }

    //endregion
}