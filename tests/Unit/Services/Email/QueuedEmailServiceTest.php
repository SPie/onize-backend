<?php

use App\Services\Email\QueuedEmailService;
use Illuminate\Contracts\Queue\Queue;
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
        $messageQueueService = $this->createQueueService();
        $queuedEmailService = $this->createQueuedEmailService($messageQueueService);

        $this->assertEquals($queuedEmailService, $queuedEmailService->passwordResetEmail($recipient, $resetToken));
        $this->assertQueuePush(
            $messageQueueService,
            'passwordReset',
            [
                'recipient'  => $recipient,
                'data' => ['resetToken' => $resetToken],
            ],
            'email'
        );
    }

    //endregion

    //region Mocks

    /**
     * @param Queue $queue
     *
     * @return QueuedEmailService
     */
    private function createQueuedEmailService(Queue $queue = null): QueuedEmailService
    {
        return new QueuedEmailService($queue ?: $this->createQueueService());
    }

    //endregion
}
