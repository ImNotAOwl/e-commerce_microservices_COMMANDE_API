<?php

namespace App\Tests;

use App\Infrastructure\Messaging\Catalogue\UpdateQuantityProducer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateQuantityTest extends TestCase
{
    /** @var AMQPStreamConnection|MockObject */
    private $connection;

    /** @var UpdateQuantityProducer */
    private $updateQuantityProducer;

    /** @var \PhpAmqpLib\Channel\AMQPChannel|MockObject */
    private $channel;

    protected function setUp(): void
    {
        // Créez un mock de la connexion AMQP
        $this->connection = $this->createMock(AMQPStreamConnection::class);

        // Créez un mock du canal
        $this->channel = $this->createMock(\PhpAmqpLib\Channel\AMQPChannel::class);

        // Configurez la connexion pour renvoyer le mock du canal
        $this->connection
            ->method('channel')
            ->willReturn($this->channel);

        // Créez l'instance de UpdateQuantityProducer avec les mocks
        $this->updateQuantityProducer = new UpdateQuantityProducer(
            $this->connection,
            'test_exchange',
            'test_routing_key'
        );
    }

    public function testPublishSendsMessage(): void
    {
        $messageData = [
            'articleId' => 'article123',
            'qteCmd' => 10,
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

        $this->updateQuantityProducer->publish($messageData);
    }

    public function testPublishHandlesChannelClose(): void
    {
        $messageData = [
            'articleId' => 'article123',
            'qteCmd' => 10,
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

        $this->updateQuantityProducer->publish($messageData);
    }
}
