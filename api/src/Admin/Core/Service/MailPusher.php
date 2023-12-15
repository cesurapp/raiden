<?php

namespace App\Admin\Core\Service;

use App\Admin\Core\Task\SendMailTask;
use Cesurapp\SwooleBundle\Task\TaskHandler;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Send Mail to Task Queue.
 */
#[Autoconfigure(public: true)]
readonly class MailPusher
{
    public function __construct(
        private CurrentUser $currentUser,
        private TaskHandler $taskHandler,
        private TranslatorInterface $translator,
        private RequestStack $requestStack
    ) {
    }

    public function send(Email|TemplatedEmail $email, bool $translateSubject = true): void
    {
        // Translate
        if ($translateSubject) {
            $email->subject($this->translator->trans($email->getSubject()));
        }

        if ($email instanceof TemplatedEmail) {
            $context = $email->getContext();

            if (!isset($context['user'])) {
                $context['user'] = $this->currentUser->get();
            }

            if (!isset($context['locale'])) {
                $context['locale'] = $this->requestStack->getMainRequest()?->getLocale() ?? 'en';
            }

            $email->context($context);
        }

        $this->taskHandler->dispatch(SendMailTask::class, $email);
    }
}
