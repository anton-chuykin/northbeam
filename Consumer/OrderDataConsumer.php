<?php

declare(strict_types=1);

namespace KozakGroup\Northbeam\Consumer;

use KozakGroup\Northbeam\Service\RequestResponseCurl;
use KozakGroup\Northbeam\Service\DataOrder as DataOrderService;
use Magento\Sales\Model\OrderFactory;

class OrderDataConsumer
{
    private RequestResponseCurl $requestResponseCurl;

    private OrderFactory $orderFactory;

    private DataOrderService $dataOrderService;

    public function __construct(
        RequestResponseCurl $requestResponseCurl,
        OrderFactory $orderFactory,
        DataOrderService $dataOrderService
    ) {
        $this->requestResponseCurl = $requestResponseCurl;
        $this->orderFactory = $orderFactory;
        $this->dataOrderService = $dataOrderService;
    }

    public function process(string $orderIncrementId)
    {
        $order = $this->orderFactory->create();
        $order = $order->loadByIncrementId($orderIncrementId);
        $requestData = $this->dataOrderService->generateRequestData($order);
        $this->requestResponseCurl->postOrder($requestData);
    }
}
