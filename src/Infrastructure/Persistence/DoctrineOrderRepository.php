<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Order;
use App\Domain\Repository\OrderRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineOrderRepository implements OrderRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function save(Order $order): void
    {
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }

    /**
     * @return Order[]
     */
    public function findByUserId(string $userId): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('o.id AS order_id', 'o.userId', 'ol.articleId', 'ol.name', 'ol.quantity', 'ol.price')
            ->from(Order::class, 'o')
            ->leftJoin('o.articles', 'ol')
            ->where('o.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }
}
