<?php

namespace Test;

use App\Services\Email\EmailService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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

    /**
     * @return Factory|MockInterface
     */
    private function createViewFactory(): Factory
    {
        return m::spy(Factory::class);
    }

    /**
     * @param MockInterface $viewFactory
     * @param View          $view
     * @param string        $viewName
     * @param array         $data
     *
     * @return $this
     */
    private function mockViewFactoryMake(MockInterface $viewFactory, View $view, string $viewName, array $data)
    {
        $viewFactory
            ->shouldReceive('make')
            ->with($viewName, $data)
            ->andReturn($view);

        return $this;
    }

    /**
     * @return View|MockInterface
     */
    private function createView(): View
    {
        return m::spy(View::class);
    }

    /**
     * @param MockInterface $view
     * @param string        $content
     *
     * @return $this
     */
    private function mockViewRender(MockInterface $view, string $content)
    {
        $view
            ->shouldReceive('render')
            ->andReturn($content);

        return $this;
    }

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
    ) {
        $emailService
            ->shouldHaveReceived('queueEmail')
            ->with($identifier, $recipient, $context)
            ->once();

        return $this;
    }

    //endregion

    //region Assertions

    /**
     * @param EmailService|MockInterface $emailService
     * @param string                     $recipient
     * @param string                     $resetToken
     *
     * @return $this
     */
    protected function assertEmailServicePasswordResetEmail(
        MockInterface $emailService,
        string $recipient,
        string $resetToken
    ) {
        $emailService
            ->shouldHaveReceived('passwordResetEmail')
            ->with($recipient, $resetToken)
            ->once();

        return $this;
    }

    /**
     * @param EmailService|MockInterface $emailService
     * @param string                     $recipient
     * @param string                     $inviteUrl
     *
     * @return $this
     */
    private function assertEmailServiceProjectInvite(
        MockInterface $emailService,
        string $recipient,
        string $inviteUrl
    ) {
        $emailService
            ->shouldHaveReceived('projectInvite')
            ->with($recipient, $inviteUrl)
            ->once();

        return $this;
    }

    //endregion
}
