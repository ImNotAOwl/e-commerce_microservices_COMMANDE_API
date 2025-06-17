<?php

namespace App\Tests;

use App\Infrastructure\Messaging\Bucket\ClearBucketProducer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClearBucketTest extends TestCase
{
    /** @var AMQPStreamConnection|MockObject */
    private $connection;

    /** @var ClearBucketProducer */
    private $clearBucketProducer;

    /** @var \PhpAmqpLib\Channel\AMQPChannel|MockObject */
    private $channel;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(AMQPStreamConnection::class);

        $this->channel = $this->createMock(\PhpAmqpLib\Channel\AMQPChannel::class);

        $this->connection
            ->method('channel')
            ->willReturn($this->channel);

        $this->clearBucketProducer = new ClearBucketProducer(
            $this->connection,
            'test_exchange',
            'test_routing_key'
        );
    }

    public function testPublishSendsMessage(): void
    {
        $messageData = [
            'userId' => 'user123',
            'cmd' => 'clear',
        ];

        $message = new AMQPMessage(json_encode($messageData));

        $this->channel
            ->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->equalTo($message),
                $this->equalTo('test_exchange'),
                $this->equalTo('test_routing_key')
            );

        $this->channel
            ->expects($this->once())
            ->method('close');

        $this->clearBucketProducer->publish($messageData);
    }
}
