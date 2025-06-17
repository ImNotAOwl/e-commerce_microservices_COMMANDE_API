<?php

namespace App\Domain\Entity;

use App\Domain\Repository\OrderRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepositoryInterface::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $userId = null;

    /**
     * @var Collection<int, OrderLine>
     */
    #[ORM\OneToMany(targetEntity: OrderLine::class, mappedBy: 'order')]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return Collection<int, OrderLine>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(OrderLine $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setOrder($this);
        }

        return $this;
    }

    public function removeArticle(OrderLine $article): static
    {
        if ($this->articles->removeElement($article) && $article->getOrder() === $this) {
            $article->setOrder(null);
        }

        return $this;
    }
}
