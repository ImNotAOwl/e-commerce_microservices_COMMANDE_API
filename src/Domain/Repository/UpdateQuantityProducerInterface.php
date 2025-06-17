<?php

namespace App\Domain\Repository;

interface UpdateQuantityProducerInterface
{
    public function publish(array $messageData): void;
}
