<?php

namespace App\Domain\Service;

use App\Application\DTO\OrderDTO;
use App\Domain\Entity\Order;
use App\Domain\Entity\OrderLine;
use App\Domain\Repository\ArticleRepositoryInterface;
use App\Domain\Repository\ClearBucketProducerInterface;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\UpdateQuantityProducerInterface;
use Psr\Log\LoggerInterface;

class CreateOrderService
{
    private OrderRepositoryInterface $orderRepository;
    private ArticleRepositoryInterface $articleRepository;
    private ClearBucketProducerInterface $clearBucketProducer;
    private UpdateQuantityProducerInterface $updateQuantityProducer;
    private LoggerInterface $logger;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ArticleRepositoryInterface $articleRepository,
        ClearBucketProducerInterface $clearBucketProducer,
        UpdateQuantityProducerInterface $updateQuantityProducer,
        LoggerInterface $logger,
    ) {
        $this->orderRepository = $orderRepository;
        $this->articleRepository = $articleRepository;
        $this->clearBucketProducer = $clearBucketProducer;
        $this->updateQuantityProducer = $updateQuantityProducer;
        $this->logger = $logger;
    }

    public function execute(OrderDTO $orderDTO, array $articleDTOs): void
    {
        try {
            $this->logger->info('Début de l’exécution du cas d’utilisation pour l’utilisateur', ['userId' => $orderDTO->userId]);

            $order = $this->createOrder($orderDTO);

            $this->logger->info('Commande créée avec succès', ['orderId' => $order->getId()]);

            $this->createOrderLines($articleDTOs, $order);

            $this->clearBucketProducer->publish([
                'userId' => $orderDTO->userId,
                'cmd' => 'clear',
            ]);

            $articlesToPublish = [];
            foreach ($articleDTOs as $articleDTO) {
                $articlesToPublish[] = [
                    'articleId' => $articleDTO->articleId,
                    'qteCmd' => $articleDTO->quantity,
                ];
            }

            $this->updateQuantityProducer->publish($articlesToPublish);

            $this->logger->info('Fin du cas d’utilisation pour la commande', ['orderId' => $order->getId()]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l’exécution du cas d’utilisation', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function createOrder(OrderDTO $orderDTO): Order
    {
        $order = new Order();
        $order->setUserId($orderDTO->userId);
        $this->orderRepository->save($order);

        return $order;
    }

    private function createOrderLines(array $articles, Order $order): void
    {
        foreach ($articles as $articleDTO) {
            $article = new OrderLine();
            $article->setArticleId($articleDTO->articleId);
            $article->setName($articleDTO->name);
            $article->setQuantity($articleDTO->quantity);
            $article->setPrice($articleDTO->price);
            $article->setOrder($order);
            $this->articleRepository->save($article);
        }
    }
}
