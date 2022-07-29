<?php

namespace App\Admin\Core\Task;

use Package\SwooleBundle\Task\TaskInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Notifier\Transport\Transports;

/**
 * Send SMS to Task.
 *
 * @param array{
 *  phone: number,
 *  country: string,
 *  subject: string,
 * } $data
 */
class SendSmsTask implements TaskInterface
{
    private array $transports;

    public function __construct(
        private readonly TexterInterface $texter,
        #[Autowire(service: 'texter.transports')] Transports $transports
    ) {
        $this->transports = explode(',', trim((string) $transports, '[]'));
    }

    public function __invoke(array $data = []): bool
    {
        $sms = new SmsMessage($data['phone'], $data['subject']);
        $sms->transport($this->getTransport($data['country']));

        // Send
        $this->texter->send($sms);

        return true;
    }

    private function getTransport(string $country): string
    {
        $key = array_search(strtolower($country), array_map('strtolower', $this->transports), true);

        return false !== $key ? $this->transports[$key] : $this->transports[0];
    }
}
