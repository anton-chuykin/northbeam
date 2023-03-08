<?php

declare(strict_types=1);

namespace KozakGroup\Northbeam\Consumer;

use Magento\Sales\Model\OrderFactory;
use KozakGroup\Northbeam\Service\RequestResponseCurl;

class OrderCancelConsumer
{
    private RequestResponseCurl $requestResponseCurl;

    private OrderFactory $orderFactory;

    public function __construct(
        RequestResponseCurl $requestResponseCurl,
        OrderFactory $orderFactory
    ) {
        $this->requestResponseCurl = $requestResponseCurl;
        $this->orderFactory = $orderFactory;
    }

    public function process(string $orderIncrementId)
    {
        $order = $this->orderFactory->create();
        $order = $order->loadByIncrementId($orderIncrementId);
        $requestData = [
            'order_id' => $order->getData('increment_id'),
            'customer_id' => $order->getCustomerEmail()
        ];
        $this->requestResponseCurl->cancelOrder($requestData);
    }
}
