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

    public function openConnection(array $config)
    {
        $this->queue = new Pheanstalk($config['host'], $config['port'], $config['timeout']);
    }

    /**
     * Post/Put a message on to the queue server
     *
     * @param string $message Message Body to be send
     * @param string $queue Queue name
     */
    public function addMessageToQueue(string $message, string $queue)
    {
        $this->queue->putInTube($queue, $message);
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
            return $this->queue->statsTube($queue)['total-jobs'];
        } catch (ConnectionException $connectionException) {
            Assert::fail(sprintf('queue [%s] not found', $queue));
        }
    }

    public function clearQueue(string $queue = 'default')
    {
        while ($job = $this->queue->reserveFromTube($queue, 0)) {
            $this->queue->delete($job);
        }
    }

    /**
     * Return a list of queues/tubes on the queueing server
     *
     * @return array Array of Queues
     */
    public function getQueues()
    {
        return $this->queue->listTubes();
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
            return $this->queue->statsTube($queue)['current-jobs-ready'];
        } catch (ConnectionException $e) {
            Assert::fail(sprintf('queue [%s] not found', $queue));
        }
    }

    public function getRequiredConfig()
    {
        return ['host'];
    }

    public function getDefaultConfig()
    {
        return ['port' => 11300, 'timeout' => 90];
    }
}
