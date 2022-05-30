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
     * Connect to the queueing server.
     */
    public function openConnection(array $config): void
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
    }

    /**
     * Post/Put a message on to the queue server
     *
     * @param string $message Message Body to be send
     */
    public function addMessageToQueue(string $message, string $queueName): void
    {
        $this->queue->sendMessage([
            'QueueUrl' => $this->getQueueURL($queueName),
            'MessageBody' => $message,
        ]);
    }

    /**
     * Return a list of queues/tubes on the queueing server
     *
     * @return string[] Array of Queues
     */
    public function getQueues(): array
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
     */
    public function getMessagesCurrentCountOnQueue(string $queueName): int
    {
        return (int)$this->queue->getQueueAttributes([
            'QueueUrl' => $this->getQueueURL($queueName),
            'AttributeNames' => ['ApproximateNumberOfMessages'],
        ])->get('Attributes')['ApproximateNumberOfMessages'];
    }

    /**
     * Count the total number of messages on the queue.
     */
    public function getMessagesTotalCountOnQueue(string $queueName): int
    {
        return (int)$this->queue->getQueueAttributes([
            'QueueUrl' => $this->getQueueURL($queueName),
            'AttributeNames' => ['ApproximateNumberOfMessages'],
        ])->get('Attributes')['ApproximateNumberOfMessages'];
    }

    public function clearQueue(string $queueName): void
    {
        $queueURL = $this->getQueueURL($queueName);
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
     */
    private function getQueueURL(string $queueName): string
    {
        $queues = $this->queue->listQueues(['QueueNamePrefix' => ''])->get('QueueUrls');
        foreach ($queues as $queueURL) {
            $tokens = explode('/', $queueURL);
            if (strtolower($queueName) === strtolower($tokens[count($tokens) - 1])) {
                return $queueURL;
            }
        }

        throw new TestRuntimeException('queue [' . $queueName . '] not found');
    }

    /**
     * @return string[]
     */
    public function getRequiredConfig(): array
    {
        return ['region'];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultConfig(): array
    {
        return [];
    }
}
