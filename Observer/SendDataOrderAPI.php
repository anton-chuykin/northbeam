<?php

declare(strict_types=1);

namespace KozakGroup\Northbeam\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use KozakGroup\Northbeam\Publisher\OrderDataPublisher;

class SendDataOrderAPI implements ObserverInterface
{
    private OrderDataPublisher $orderDataPublisher;

    public function __construct(
        OrderDataPublisher $orderDataPublisher
    ) {
        $this->orderDataPublisher = $orderDataPublisher;
    }

    /**
     * @param EventObserver $observer
     *
     * @return void
     */
    public function execute(EventObserver $observer): void
    {
        /** @var OrderInterface $order */
        $order = $observer->getData('order');
        $this->orderDataPublisher->publish($order->getIncrementId());
    }
}
