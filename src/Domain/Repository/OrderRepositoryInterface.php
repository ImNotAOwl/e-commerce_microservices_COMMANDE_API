<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Order;

interface OrderRepositoryInterface
{
    public function save(Order $order): void;

    /**
     * @return Order[]
     */
    public function findByUserId(string $userId): array;
}
