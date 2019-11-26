<?php

use Codeception\Lib\ModuleContainer;
use Codeception\Module\Queue;
use \Codeception\PHPUnit\TestCase;
use Codeception\TestInterface;
use Codeception\Util\Stub;

abstract class QueueTest extends TestCase
{

    abstract public function configProvider();

    /**
     * @dataProvider configProvider
     */
    public function testFlow($config)
    {
        /** @var ModuleContainer $container */
        $container = Stub::make(ModuleContainer::class);
        $module = new Queue($container);
        $module->_setConfig($config);
        $module->_before(Stub::makeEmpty(TestInterface::class));
        try {
            $module->clearQueue('default');
        } catch (\Throwable $t) {
            $this->markTestSkipped("Connection failed for: " . print_r($config, true));
        }
        $module->addMessageToQueue('hello world - ' . date('d-m-y'), 'default');
        $module->clearQueue('default');

        $module->seeQueueExists('default');
        $module->dontSeeQueueExists('fake_queue');

        $module->seeEmptyQueue('default');
        $module->addMessageToQueue('hello world - ' . date('d-m-y'), 'default');
        $module->dontSeeEmptyQueue('default');

        $module->seeQueueHasTotalCount('default', 2);

        $module->seeQueueHasCurrentCount('default', 1);
        $module->dontSeeQueueHasCurrentCount('default', 9999);

        $module->grabQueues();
    }
}