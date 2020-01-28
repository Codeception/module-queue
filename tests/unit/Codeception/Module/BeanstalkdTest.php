<?php

class BeanstalkdTest extends QueueTest
{
    public function configProvider()
    {
        return [
            [[
                'type' => 'beanstalkd',
                'host' => 'localhost'
            ]]
        ];
    }
}
