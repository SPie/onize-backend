<?php

use App\Services\Email\QueuedEmailService;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Contracts\View\Factory;
use Test\EmailHelper;
use Test\MessageQueueHelper;

/**
 * Class QueuedEmailServiceTest
 */
final class QueuedEmailServiceTest extends \PHPUnit\Framework\TestCase
{
    use EmailHelper;
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
        $content = $this->getFaker()->text;
        $view = $this->createView();
        $this->mockViewRender($view, $content);
        $viewPath = 'passwordReset';
        $viewFactory = $this->createViewFactory();
        $this->mockViewFactoryMake($viewFactory, $view, $viewPath, ['resetToken' => $resetToken]);
        $queuedEmailService = $this->createQueuedEmailService($messageQueueService, $viewFactory);

        $this->assertEquals($queuedEmailService, $queuedEmailService->passwordResetEmail($recipient, $resetToken));
        $this->assertQueuePush(
            $messageQueueService,
            'passwordReset',
            [
                'recipient' => $recipient,
                'content'   => $content,
            ],
            'email'
        );
    }

    //endregion

    //region Mocks

    /**
     * @param Queue|null   $queue
     * @param Factory|null $viewFactory
     *
     * @return QueuedEmailService
     */
    private function createQueuedEmailService(Queue $queue = null, Factory $viewFactory = null): QueuedEmailService
    {
        return new QueuedEmailService(
            $queue ?: $this->createQueueService(),
            $viewFactory ?: $this->createViewFactory()
        );
    }

    //endregion
}