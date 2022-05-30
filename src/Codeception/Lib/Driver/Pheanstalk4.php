<?php

declare(strict_types=1);

namespace Codeception\Lib\Driver;

use Codeception\Lib\Interfaces\Queue;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Pheanstalk;

class Pheanstalk4 implements Queue
{
    protected ?Pheanstalk $queue = null;

    /**
     * Connect to the queueing server.
     */
    public function openConnection(array $config): void
    {
        $this->queue = Pheanstalk::create($config['host'], $config['port'], $config['timeout']);
    }

    /**
     * Post/Put a message on to the queue server
     *
     * @param string $message Message Body to be send
     */
    public function addMessageToQueue(string $message, string $queueName): void
    {
        $this->queue->useTube($queueName);
        $this->queue->put($message);
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
        $response = $this->queue->statsTube($queueName);
        return $response->getResponseName() !== ResponseInterface::RESPONSE_NOT_FOUND
            ? (int)$response['current-jobs-ready']
            : 0;
    }

    /**
     * Count the total number of messages on the queue.
     */
    public function getMessagesTotalCountOnQueue(string $queueName): int
    {
        $response = $this->queue->statsTube($queueName);
        return $response->getResponseName() !== ResponseInterface::RESPONSE_NOT_FOUND
            ? (int)$response['total-jobs']
            : 0;
    }

    public function clearQueue(string $queueName): void
    {
        $this->queue->useTube($queueName);
        while (null !== $job = $this->queue->peekBuried()) {
            $this->queue->delete($job);
        }

        while (null !== $job = $this->queue->peekDelayed()) {
            $this->queue->delete($job);
        }

        while (null !== $job = $this->queue->peekReady()) {
            $this->queue->delete($job);
        }
    }

    /**
     * @return string[]
     */
    public function getRequiredConfig(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultConfig(): array
    {
        return ['port' => 11300, 'timeout' => 90, 'host' => 'localhost'];
    }
}
