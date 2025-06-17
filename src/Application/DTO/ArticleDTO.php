<?php

namespace App\Application\DTO;

class ArticleDTO
{
    public string $articleId;
    public string $name;
    public int $quantity;
    public float $price;

    public function __construct(string $articleId, string $name, int $quantity, float $price)
    {
        $this->articleId = $articleId;
        $this->name = $name;
        $this->quantity = $quantity;
        $this->price = $price;
    }
}
