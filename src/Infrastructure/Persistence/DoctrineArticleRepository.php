<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\OrderLine;
use App\Domain\Repository\ArticleRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineArticleRepository implements ArticleRepositoryInterface
{
    public EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function save(OrderLine $article): void
    {
        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }
}
