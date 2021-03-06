<?php
declare(strict_types=1);

namespace Codeception\Lib\Driver;


use Codeception\Lib\Interfaces\Queue;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Pheanstalk;

class Pheanstalk4 implements Queue
{
    /**
     * @var Pheanstalk
     */
    private $queue;
    /**
     * @inheritDoc
     */
    public function openConnection($config)
    {
        $this->queue = Pheanstalk::create($config['host'], $config['port'], $config['timeout']);
    }

    /**
     * @inheritDoc
     */
    public function addMessageToQueue($message, $queue)
    {
        $this->queue->useTube($queue);
        $this->queue->put($message);
    }

    /**
     * @inheritDoc
     */
    public function getQueues()
    {
        return $this->queue->listTubes();
    }

    /**
     * @inheritDoc
     */
    public function getMessagesCurrentCountOnQueue($queue)
    {
        $response = $this->queue->statsTube($queue);
        return $response->getResponseName() !== ResponseInterface::RESPONSE_NOT_FOUND
            ? $response['current-jobs-ready']
            : 0;
    }

    /**
     * @inheritDoc
     */
    public function getMessagesTotalCountOnQueue($queue)
    {
        $response = $this->queue->statsTube($queue);
        return $response->getResponseName() !== ResponseInterface::RESPONSE_NOT_FOUND
            ? $response['total-jobs']
            : 0;
    }

    public function clearQueue($queue)
    {
        $this->queue->useTube($queue);
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

    public function getRequiredConfig()
    {
        return [];
    }

    public function getDefaultConfig()
    {
        return ['port' => 11300, 'timeout' => 90, 'host' => 'localhost'];
    }
}