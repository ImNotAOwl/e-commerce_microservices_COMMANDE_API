<?php

namespace App\Application\DTO;

class OrderDTO
{
    public string $userId;
    /** @var ArticleDTO[] */
    public array $articles;
}
