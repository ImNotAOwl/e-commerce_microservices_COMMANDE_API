<?php

namespace App\Domain\Repository;

use App\Domain\Entity\OrderLine;

interface ArticleRepositoryInterface
{
    public function save(OrderLine $article): void;
}
