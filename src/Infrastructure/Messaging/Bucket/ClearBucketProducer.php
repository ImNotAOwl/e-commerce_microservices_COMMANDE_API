<?php

namespace App\Infrastructure\Messaging\Bucket;

use App\Domain\Repository\ClearBucketProducerInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ClearBucketProducer implements ClearBucketProducerInterface
{
    private AMQPStreamConnection $connection;
    private string $exchange;
    private string $routingKey;

    public function __construct(AMQPStreamConnection $connection, string $exchange, string $routingKey)
    {
        $this->connection = $connection;
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
    }

    public function publish(array $messageData): void
    {
        $channel = $this->connection->channel();

        $channel->exchange_declare($this->exchange, 'direct', false, false, false);

        $message = new AMQPMessage(json_encode($messageData));

        $channel->basic_publish($message, $this->exchange, $this->routingKey);

        $channel->close();
    }
}
