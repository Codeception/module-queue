<?php

namespace Codeception\Lib\Interfaces;

interface Queue
{
    /**
     * Connect to the queueing server.
     *
     * @param array<string, mixed> $config
     */
    public function openConnection(array $config): void;

    /**
     * Post/Put a message on to the queue server
     *
     * @param string $message Message Body to be send
     */
    public function addMessageToQueue(string $message, string $queueName): void;
    
    /**
     * Return a list of queues/tubes on the queueing server
     *
     * @return string[] Array of Queues
     */
    public function getQueues(): array;

    /**
     * Count the current number of messages on the queue.
     */
    public function getMessagesCurrentCountOnQueue(string $queueName): int;

    /**
     * Count the total number of messages on the queue.
     */
    public function getMessagesTotalCountOnQueue(string $queueName): int;

    public function clearQueue(string $queueName): void;

    /**
     * @return string[]
     */
    public function getRequiredConfig(): array;

    /**
     * @return array<string, mixed>
     */
    public function getDefaultConfig(): array;
}
