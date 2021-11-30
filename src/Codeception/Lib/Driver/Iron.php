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
    public function openConnection(array $config)
    {
        $this->queue = new IronMQ([
            "token"      => $config['token'],
            "project_id" => $config['project'],
            "host"       => $config['host']
        ]);
        if (!$this->queue) {
            Assert::fail('connection failed or timed-out.');
        }
    }

    /**
     * Post/Put a message on to the queue server
     *
     * @param string $message Message Body to be send
     * @param string $queue Queue name
     */
    public function addMessageToQueue(string $message, string $queue)
    {
        $this->queue->postMessage($queue, $message);
    }

    /**
     * Return a list of queues/tubes on the queueing server
     *
     * @return array Array of Queues
     */
    public function getQueues()
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
     *
     * @param string $queue Queue name
     * @return int Count
     */
    public function getMessagesCurrentCountOnQueue(string $queue)
    {
        try {
            return $this->queue->getQueue($queue)->size;
        } catch (Http_Exception $ex) {
            Assert::fail("queue [$queue] not found");
        }
    }

    /**
     * Count the total number of messages on the queue.
     *
     * @param string $queue Queue name
     * @return int Count
     */
    public function getMessagesTotalCountOnQueue(string $queue)
    {
        try {
            return $this->queue->getQueue($queue)->total_messages;
        } catch (Http_Exception $e) {
            Assert::fail("queue [$queue] not found");
        }
    }

    public function clearQueue(string $queue)
    {
        try {
            $this->queue->clearQueue($queue);
        } catch (Http_Exception $ex) {
            Assert::fail("queue [$queue] not found");
        }
    }

    public function getRequiredConfig()
    {
        return ['host', 'token', 'project'];
    }

    public function getDefaultConfig()
    {
        return [];
    }
}
