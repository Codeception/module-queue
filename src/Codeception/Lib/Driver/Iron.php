<?php

declare(strict_types=1);

namespace Codeception\Lib\Driver;

use Codeception\Lib\Interfaces\Queue;
use Http_Exception;
use IronMQ;
use PHPUnit\Framework\Assert;

class Iron implements Queue
{
    protected ?IronMQ $queue = null;

    /**
     * Connect to the queueing server. (AWS, Iron.io and Beanstalkd)
     */
    public function openConnection(array $config): void
    {
        $this->queue = new IronMQ([
            "token"      => $config['token'],
            "project_id" => $config['project'],
            "host"       => $config['host']
        ]);
    }

    /**
     * Post/Put a message on to the queue server
     *
     * @param string $message Message Body to be send
     */
    public function addMessageToQueue(string $message, string $queueName): void
    {
        $this->queue->postMessage($queueName, $message);
    }

    /**
     * Return a list of queues/tubes on the queueing server
     *
     * @return string[] Array of Queues
     */
    public function getQueues(): array
    {
        // Format the output to suit
        $queues = [];
        foreach ($this->queue->getQueues() as $queue) {
            $queues[] = $queue->name;
        }
        return $queues;
    }

    /**
     * Count the current number of messages on the queue.
     */
    public function getMessagesCurrentCountOnQueue(string $queueName): int
    {
        try {
            return (int)$this->queue->getQueue($queueName)->size;
        } catch (Http_Exception $ex) {
            Assert::fail("queue [$queueName] not found");
        }
    }

    /**
     * Count the total number of messages on the queue.
     */
    public function getMessagesTotalCountOnQueue(string $queueName): int
    {
        try {
            return (int)$this->queue->getQueue($queueName)->total_messages;
        } catch (Http_Exception $e) {
            Assert::fail("queue [$queueName] not found");
        }
    }

    public function clearQueue(string $queueName): void
    {
        try {
            $this->queue->clearQueue($queueName);
        } catch (Http_Exception $ex) {
            Assert::fail("queue [$queueName] not found");
        }
    }

    /**
     * @return string[]
     */
    public function getRequiredConfig(): array
    {
        return ['host', 'token', 'project'];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultConfig(): array
    {
        return [];
    }
}
