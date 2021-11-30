<?php

declare(strict_types=1);

namespace Codeception\Lib\Driver;

use Aws\Credentials\Credentials;
use Aws\Sqs\SqsClient;
use Codeception\Exception\TestRuntimeException;
use Codeception\Lib\Interfaces\Queue;

class  AmazonSQS implements Queue
{
    protected ?SqsClient $queue = null;

    /**
     * Connect to the queueing server. (AWS, Iron.io and Beanstalkd)
     */
    public function openConnection(array $config)
    {
        $params = [
            'region' => $config['region'],
        ];

        if (! empty($config['key']) && ! empty($config['secret'])) {
            $params['credentials'] = new Credentials($config['key'], $config['secret']);
        }

        if (! empty($config['profile'])) {
            $params['profile'] = $config['profile'];
        }

        if (! empty($config['version'])) {
            $params['version'] = $config['version'];
        }

        if (! empty($config['endpoint'])) {
            $params['endpoint'] = $config['endpoint'];
        }

        $this->queue = new SqsClient($params);
        if (!$this->queue) {
            throw new TestRuntimeException('connection failed or timed-out.');
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
        $this->queue->sendMessage([
            'QueueUrl' => $this->getQueueURL($queue),
            'MessageBody' => $message,
        ]);
    }

    /**
     * Return a list of queues/tubes on the queueing server
     *
     * @return array Array of Queues
     */
    public function getQueues()
    {
        $queueNames = [];
        $queues = $this->queue->listQueues(['QueueNamePrefix' => ''])->get('QueueUrls');
        foreach ($queues as $queue) {
            $tokens = explode('/', $queue);
            $queueNames[] = $tokens[count($tokens) - 1];
        }

        return $queueNames;
    }

    /**
     * Count the current number of messages on the queue.
     *
     * @param string $queue Queue name
     * @return int Count
     */
    public function getMessagesCurrentCountOnQueue(string $queue)
    {
        return $this->queue->getQueueAttributes([
            'QueueUrl' => $this->getQueueURL($queue),
            'AttributeNames' => ['ApproximateNumberOfMessages'],
        ])->get('Attributes')['ApproximateNumberOfMessages'];
    }

    /**
     * Count the total number of messages on the queue.
     *
     * @param string $queue Queue name
     * @return int Count
     */
    public function getMessagesTotalCountOnQueue(string $queue)
    {
        return $this->queue->getQueueAttributes([
            'QueueUrl' => $this->getQueueURL($queue),
            'AttributeNames' => ['ApproximateNumberOfMessages'],
        ])->get('Attributes')['ApproximateNumberOfMessages'];
    }

    public function clearQueue(string $queue)
    {
        $queueURL = $this->getQueueURL($queue);
        while (true) {
            $res = $this->queue->receiveMessage(['QueueUrl' => $queueURL]);

            if (!$res->getPath('Messages')) {
                return;
            }

            foreach ($res->getPath('Messages') as $msg) {
                $this->queue->deleteMessage([
                    'QueueUrl' => $queueURL,
                    'ReceiptHandle' => $msg['ReceiptHandle']
                ]);
            }
        }
    }

    /**
     * Get the queue/tube URL from the queue name (AWS function only)
     *
     * @param string $queue Queue name
     * @return string Queue URL
     */
    private function getQueueURL(string $queue)
    {
        $queues = $this->queue->listQueues(['QueueNamePrefix' => ''])->get('QueueUrls');
        foreach ($queues as $queueURL) {
            $tokens = explode('/', $queueURL);
            if (strtolower($queue) === strtolower($tokens[count($tokens) - 1])) {
                return $queueURL;
            }
        }

        throw new TestRuntimeException('queue [' . $queue . '] not found');
    }

    public function getRequiredConfig()
    {
        return ['region'];
    }

    public function getDefaultConfig()
    {
        return [];
    }
}
