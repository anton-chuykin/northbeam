<?php

declare(strict_types=1);

namespace KozakGroup\Northbeam\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use KozakGroup\Northbeam\Publisher\OrderCancelPublisher;

class CancelOrderAPI implements ObserverInterface
{
    private OrderCancelPublisher $orderCancelPublisher;

    public function __construct(
        OrderCancelPublisher $orderCancelPublisher
    ) {
        $this->orderCancelPublisher = $orderCancelPublisher;
    }

    /**
     * @param EventObserver $observer
     *
     * @return void
     */
    public function execute(EventObserver $observer): void
    {        /** @var OrderInterface $order */
        $order = $observer->getData('order');
        $this->orderCancelPublisher->publish($order->getIncrementId());
    }
}
