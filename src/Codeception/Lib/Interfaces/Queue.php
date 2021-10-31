<?php

namespace Codeception\Lib\Interfaces;

interface Queue
{
    /**
     * Connect to the queueing server.
     *
     * @return void
     */
    public function openConnection(array $config);

    /**
     * Post/Put a message on to the queue server
     *
     * @param string $message Message Body to be send
     * @param string $queue Queue name
     */
    public function addMessageToQueue(string $message, string $queue);

    /**
     * Return a list of queues/tubes on the queueing server
     *
     * @return array Array of Queues
     */
    public function getQueues();

    /**
     * Count the current number of messages on the queue.
     *
     * @param string $queue Queue name
     * @return int Count
     */
    public function getMessagesCurrentCountOnQueue(string $queue);

    /**
     * Count the total number of messages on the queue.
     *
     * @param string $queue Queue name
     * @return int Count
     */
    public function getMessagesTotalCountOnQueue(string $queue);

    /**
     * @param string $queue
     * @return void
     */
    public function clearQueue(string $queue);

    /**
     * @return array
     */
    public function getRequiredConfig();

    /**
     * @return array
     */
    public function getDefaultConfig();
}
