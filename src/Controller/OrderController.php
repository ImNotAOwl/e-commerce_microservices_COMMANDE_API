<?php

namespace App\Controller;

use App\Application\DTO\ArticleDTO;
use App\Application\DTO\OrderDTO;
use App\Domain\Service\CreateOrderService;
use App\Domain\Service\GetOrderService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class OrderController
{
    private SerializerInterface $serializer;
    private CreateOrderService $createOrderService;

    private GetOrderService $getOrderService;
    private LoggerInterface $logger;

    public function __construct(SerializerInterface $serializer, GetOrderService $getOrderService, CreateOrderService $createOrderService, LoggerInterface $logger)
    {
        $this->serializer = $serializer;
        $this->getOrderService = $getOrderService;
        $this->createOrderService = $createOrderService;
        $this->logger = $logger;
    }

    #[Route('/api/order', name: 'create_order', methods: ['POST'])]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'userId', description: 'The ID of the user creating the order', type: 'string'),
                new OA\Property(
                    property: 'articles',
                    description: 'List of articles included in the order',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'articleId', description: 'The unique ID of the article', type: 'string'),
                            new OA\Property(property: 'name', description: 'The name of the article', type: 'string'),
                            new OA\Property(property: 'quantity', description: 'The quantity of the article', type: 'integer'),
                            new OA\Property(property: 'price', description: 'The price of the article', type: 'number', format: 'float'),
                        ]
                    )
                ),
            ],
            example: '{"userId": "tyu", "articles": [{"articleId": "d5151", "name": "Article 56", "quantity": 2, "price": 10.00}]}'
        )
    )
    ]
    #[OA\Response(
        response: 200,
        description: 'Order created successfully.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: OrderDTO::class))
        )
    )]
    public function createOrder(Request $request): JsonResponse
    {
        $data = $request->getContent();
        $this->logger->debug('Received a request to create an order', ['data' => $data]);

        try {
            $orderDTO = $this->serializer->deserialize($data, OrderDTO::class, 'json');
            $this->logger->debug('Deserialization of OrderDTO succeeded', ['orderDTO' => $orderDTO]);
            $articleDTOs = [];
            foreach ($orderDTO->articles as $articleData) {
                $articleDTO = $this->serializer->deserialize(json_encode($articleData), ArticleDTO::class, 'json');
                $articleDTOs[] = $articleDTO;
            }

            $this->createOrderService->execute($orderDTO, $articleDTOs);

            $this->logger->info('Order creation service executed successfully');

            return new JsonResponse(['status' => 'Order created successfully'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logger->error('Error occurred while creating order', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/orders/{userId}', name: 'get_orders_by_user', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Orders retrieved successfully.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: OrderDTO::class))
        )
    )]
    public function getOrdersByUserId(string $userId): JsonResponse
    {
        $ordersDTO = $this->getOrderService->getOrdersByUserId($userId);

        if (!$ordersDTO) {
            return new JsonResponse(
                ['error' => 'Order not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse($ordersDTO, Response::HTTP_OK);
    }
}
