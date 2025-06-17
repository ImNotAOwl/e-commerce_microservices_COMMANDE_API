<?php

namespace App\Tests\Controller;

use App\Application\DTO\ArticleDTO;
use App\Application\DTO\OrderDTO;
use App\Controller\OrderController;
use App\Domain\Service\CreateOrderService;
use App\Domain\Service\GetOrderService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class OrderControllerTest extends TestCase
{
    private SerializerInterface $serializer;
    private GetOrderService $getOrderService;
    private CreateOrderService $createOrderService;
    private LoggerInterface $logger;
    private OrderController $orderController;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->getOrderService = $this->createMock(GetOrderService::class);
        $this->createOrderService = $this->createMock(CreateOrderService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->orderController = new OrderController(
            $this->serializer,
            $this->getOrderService,
            $this->createOrderService,
            $this->logger
        );
    }

    //    public function testCreateOrderSuccess()
    //    {
    //        $requestData = json_encode([
    //            'userId' => 'user123',
    //            'articles' => [
    //                ['articleId' => 'a1', 'name' => 'Article 1', 'quantity' => 2, 'price' => 10]
    //            ]
    //        ]);
    //
    //        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $requestData);
    //
    //
    //        $orderDTO = new OrderDTO();
    //        $orderDTO->userId = 'user123';
    //        $orderDTO->articles = [
    //            new ArticleDTO('a1', 'Article 1', 2, 10),
    //
    //        ];
    //
    //        $this->serializer->expects($this->once())
    //            ->method('deserialize')
    //            ->willReturn($orderDTO);
    //
    //        $this->createOrderService->expects($this->once())
    //            ->method('execute')
    //            ->with($orderDTO, $this->isType('array'));
    //
    //        $response = $this->orderController->createOrder($request);
    //
    //        $this->assertInstanceOf(JsonResponse::class, $response);
    //        $this->assertEquals(201, $response->getStatusCode());
    //        $this->assertStringContainsString('Order created successfully', $response->getContent());
    //    }

    public function testCreateOrderFailure()
    {
        $request = new Request([], [], [], [], [], [], '{}');

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->will($this->throwException(new \Exception('Invalid data')));

        $response = $this->orderController->createOrder($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Invalid data', $response->getContent());
    }

    public function testGetOrdersByUserIdSuccess()
    {
        $order = new OrderDTO();
        $order->userId = '123';
        $order->articles = [new ArticleDTO('a1', 'Item 1', 2, 10.0)];

        $ordersDTO = [$order];

        $this->getOrderService->method('getOrdersByUserId')
            ->willReturn($ordersDTO);

        $response = $this->orderController->getOrdersByUserId($order->userId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    //    public function testGetOrdersByUserIdNotFound()
    //    {
    //        $userId = 'user123';
    //
    //        $this->getOrderService->expects($this->once())
    //            ->method('getOrdersByUserId')
    //            ->with($userId)
    //            ->willReturn(null);
    //
    //        $response = $this->orderController->getOrdersByUserId($userId);
    //
    //        $this->assertInstanceOf(JsonResponse::class, $response);
    //        $this->assertEquals(404, $response->getStatusCode());
    //        $this->assertStringContainsString('Order not found', $response->getContent());
    //    }
}
