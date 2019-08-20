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
        $finishUrl = $this->getFaker()->url;
        $messageQueueService = $this->createQueueService();
        $templatesDir = $this->getFaker()->uuid;
        $content = $this->getFaker()->text;
        $view = $this->createView();
        $this->mockViewRender($view, $content);
        $viewFactory = $this->createViewFactory();
        $this->mockViewFactoryMake(
            $viewFactory,
            $view,
            $templatesDir . '/passwordReset',
            ['finishUrl' => $finishUrl]
        );
        $queuedEmailService = $this->createQueuedEmailService($messageQueueService, $viewFactory, $templatesDir);

        $this->assertEquals($queuedEmailService, $queuedEmailService->passwordResetEmail($recipient, $finishUrl));
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
     * @param string|null  $templatesDir
     *
     * @return QueuedEmailService
     */
    private function createQueuedEmailService(
        Queue $queue = null,
        Factory $viewFactory = null,
        string $templatesDir = null
    ): QueuedEmailService {
        return new QueuedEmailService(
            $queue ?: $this->createQueueService(),
            $viewFactory ?: $this->createViewFactory(),
            $templatesDir ?: $this->getFaker()->uuid
        );
    }

    //endregion
}