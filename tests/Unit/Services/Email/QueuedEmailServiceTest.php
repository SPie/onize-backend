<?php

use App\Services\Email\QueuedEmailService;
use App\Services\MessageQueue\MessageQueueService;
use Test\MessageQueueHelper;

/**
 * Class QueuedEmailServiceTest
 */
final class QueuedEmailServiceTest extends \PHPUnit\Framework\TestCase
{

    use MessageQueueHelper;
    use TestCaseHelper;

    //region Tests

    /**
     * @return void
     */
    public function testPasswordResetEmail(): void
    {
        $recipient = $this->getFaker()->safeEmail;
        $resetToken = $this->getFaker()->uuid;
        $messageQueueService = $this->createMessageQueueService();
        $queuedEmailService = $this->createQueuedEmailService($messageQueueService);

        $this->assertEquals($queuedEmailService, $queuedEmailService->passwordResetEmail($recipient, $resetToken));
        $this->assertMessageQueueServiceQueueMessage(
            $messageQueueService,
            'passwordReset',
            [
                'recipient'  => $recipient,
                'resetToken' => $resetToken,
            ]
        );
    }

    //endregion

    //region Mocks

    /**
     * @param MessageQueueService|null $messageQueueService
     *
     * @return QueuedEmailService
     */
    private function createQueuedEmailService(MessageQueueService $messageQueueService = null): QueuedEmailService
    {
        return new QueuedEmailService($messageQueueService ?: $this->createMessageQueueService());
    }

    //endregion
}