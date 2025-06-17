<?php

namespace App\Domain\Service;

use App\Domain\Entity\Order;
use App\Domain\Repository\OrderRepositoryInterface;

class GetOrderService
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return Order[]
     */
    public function getOrdersByUserId(string $userId): array
    {
        return $this->orderRepository->findByUserId($userId);
    }
}
