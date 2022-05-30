<?php

declare(strict_types=1);

namespace Codeception\Lib\Driver;

use Codeception\Lib\Interfaces\Queue;
use Pheanstalk\Exception\ConnectionException;
use Pheanstalk\Pheanstalk;
use PHPUnit\Framework\Assert;

class Beanstalk implements Queue
{
    protected ?Pheanstalk $queue = null;

    /**
     * @param array<string, mixed> $config
     */
    public function openConnection(array $config): void
    {
        $this->queue = new Pheanstalk($config['host'], $config['port'], $config['timeout']);
    }

    /**
     * Post/Put a message on to the queue server
     *
     * @param string $message Message Body to be send
     */
    public function addMessageToQueue(string $message, string $queueName): void
    {
        $this->queue->putInTube($queueName, $message);
    }

    /**
     * Count the total number of messages on the queue.
     */
    public function getMessagesTotalCountOnQueue(string $queueName): int
    {
        try {
            return (int)$this->queue->statsTube($queueName)['total-jobs'];
        } catch (ConnectionException $connectionException) {
            Assert::fail(sprintf('queue [%s] not found', $queueName));
        }
    }

    public function clearQueue(string $queueName = 'default'): void
    {
        while ($job = $this->queue->reserveFromTube($queueName, 0)) {
            $this->queue->delete($job);
        }
    }

    /**
     * Return a list of queues/tubes on the queueing server
     *
     * @return string[] Array of Queues
     */
    public function getQueues(): array
    {
        return $this->queue->listTubes();
    }

    /**
     * Count the current number of messages on the queue.
     */
    public function getMessagesCurrentCountOnQueue(string $queueName): int
    {
        try {
            return (int)$this->queue->statsTube($queueName)['current-jobs-ready'];
        } catch (ConnectionException $e) {
            Assert::fail(sprintf('queue [%s] not found', $queueName));
        }
    }

    /**
     * @return string[]
     */
    public function getRequiredConfig(): array
    {
        return ['host'];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultConfig(): array
    {
        return ['port' => 11300, 'timeout' => 90];
    }
}
