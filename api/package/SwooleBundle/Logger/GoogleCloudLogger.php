<?php

namespace Package\SwooleBundle\Logger;

use Google\Cloud\Logging\Logger as GoogleLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Log\Logger as BaseLogger;

/**
 * Google Cloud Logger.
 */
class GoogleCloudLogger extends BaseLogger
{
    private const LEVELS = [
        LogLevel::DEBUG => 0,
        LogLevel::INFO => 1,
        LogLevel::NOTICE => 2,
        LogLevel::WARNING => 3,
        LogLevel::ERROR => 4,
        LogLevel::CRITICAL => 5,
        LogLevel::ALERT => 6,
        LogLevel::EMERGENCY => 7,
    ];

    private const PRIORITIES = [
        LogLevel::DEBUG => 100,
        LogLevel::INFO => 200,
        LogLevel::NOTICE => 250,
        LogLevel::WARNING => 300,
        LogLevel::ERROR => 400,
        LogLevel::CRITICAL => 500,
        LogLevel::ALERT => 550,
        LogLevel::EMERGENCY => 600,
    ];

    private int $minLevelIndex;

    public function __construct(
        string $minLevel = null,
        $output = null,
        callable $formatter = null,
        private readonly ?RequestStack $requestStack = null,
        private readonly ?GoogleLogger $psrClient = null
    ) {
        parent::__construct($minLevel, $output, $formatter, $requestStack);

        if (!$minLevel) {
            $minLevel = null === $output || 'php://stdout' === $output || 'php://stderr' === $output ? LogLevel::ERROR : LogLevel::WARNING;
        }
        $this->minLevelIndex = self::LEVELS[$minLevel];
    }

    public function log($level, $message, array $context = []): void
    {
        if (!isset(self::LEVELS[$level])) {
            throw new InvalidArgumentException(sprintf('The log level "%s" does not exist.', $level));
        }

        if (self::LEVELS[$level] < $this->minLevelIndex) {
            return;
        }

        $options = [
            'severity' => self::PRIORITIES[$level],
        ];

        if ($req = $this->requestStack->getCurrentRequest()) {
            $options['httpRequest'] = [
                'requestMethod' => $req->getMethod(),
                'requestUrl' => $req->getUri(),
                'userAgent' => $req->headers->get('User-Agent'),
                'remoteIp' => implode(',', $req->getClientIps()),
                'serverIp' => $_SERVER['SERVER_ADDR'] ?? '',
                'referer' => $req->headers->get('referer'),
            ];
        }

        go(function () use ($message, $context, $options) {
            $this->psrClient->write(
                $this->psrClient->entry(['message' => $message] + $context, $options)
            );
        });
    }
}
