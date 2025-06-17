<?php

namespace App\Tests;

use App\Application\DTO\ArticleDTO;
use App\Application\DTO\OrderDTO;
use App\Domain\Entity\Order;
use App\Domain\Entity\OrderLine;
use App\Domain\Repository\ArticleRepositoryInterface;
use App\Domain\Repository\ClearBucketProducerInterface;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\UpdateQuantityProducerInterface;
use App\Domain\Service\CreateOrderService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CreateOrderServiceTest extends TestCase
{
    private OrderRepositoryInterface $orderRepository;
    private ArticleRepositoryInterface $articleRepository;
    private ClearBucketProducerInterface $clearBucketProducer;
    private UpdateQuantityProducerInterface $updateQuantityProducer;
    private CreateOrderService $createOrderService;
    private LoggerInterface $logger;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->clearBucketProducer = $this->createMock(ClearBucketProducerInterface::class);
        $this->updateQuantityProducer = $this->createMock(UpdateQuantityProducerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->createOrderService = new CreateOrderService(
            $this->orderRepository,
            $this->articleRepository,
            $this->clearBucketProducer,
            $this->updateQuantityProducer,
            $this->logger
        );
    }

    public function testExecuteSuccessfully(): void
    {
        $orderDTO = new OrderDTO();
        $orderDTO->userId = 'user123';
        $orderDTO->articles = [
            new ArticleDTO('article1', 'Article 1', 2, 20.0),
            new ArticleDTO('article2', 'Article 2', 1, 10.0),
        ];

        $logMessages = [];
        $this->logger
            ->expects($this->any())
            ->method('info')
            ->willReturnCallback(function ($message, $context) use (&$logMessages) {
                $logMessages[] = ['message' => $message, 'context' => $context];
            });

        $this->orderRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Order::class));

        $this->articleRepository
            ->expects($this->exactly(2))
            ->method('save')
            ->with($this->isInstanceOf(OrderLine::class));

        $this->clearBucketProducer
            ->expects($this->once())
            ->method('publish')
            ->with($this->callback(function ($data) use ($orderDTO) {
                return $data['userId'] === $orderDTO->userId && 'clear' === $data['cmd'];
            }));

        $this->updateQuantityProducer
            ->expects($this->once())
            ->method('publish')
            ->with($this->callback(function ($articles) use ($orderDTO) {
                return count($articles) === count($orderDTO->articles);
            }));

        $this->createOrderService->execute($orderDTO, $orderDTO->articles);

        // Assertions pour les logs
        $this->assertCount(3, $logMessages);
        $this->assertEquals('Début de l’exécution du cas d’utilisation pour l’utilisateur', $logMessages[0]['message']);
        $this->assertEquals('Commande créée avec succès', $logMessages[1]['message']);
        $this->assertEquals('Fin du cas d’utilisation pour la commande', $logMessages[2]['message']);
    }

    public function testErrorWhenOrderSaveFails(): void
    {
        $orderDTO = new OrderDTO();
        $orderDTO->userId = 'user123';
        $orderDTO->articles = [
            new ArticleDTO('article1', 'Article 1', 2, 20.0),
        ];

        $this->orderRepository
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \Exception('Erreur lors de l’exécution du cas d’utilisation')));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Erreur lors de l’exécution du cas d’utilisation'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erreur lors de l’exécution du cas d’utilisation');

        $this->createOrderService->execute($orderDTO, $orderDTO->articles);
    }
}
