<?php

namespace App\Domain\Repository;

interface ClearBucketProducerInterface
{
    public function publish(array $messageData): void;
}
