<?php

declare(strict_types=1);

final class BeanstalkdTest extends QueueTest
{
    public function configProvider(): array
    {
        return [
            [[
                'type' => 'beanstalkd',
                'host' => 'localhost'
            ]]
        ];
    }
}
