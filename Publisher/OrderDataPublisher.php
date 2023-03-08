<?php

declare(strict_types=1);

namespace KozakGroup\Northbeam\Publisher;

use Magento\Framework\MessageQueue\PublisherInterface;

class OrderDataPublisher
{
    const TOPIC_NAME = 'nb.send.order.data';

    private PublisherInterface $publisher;

    /**
     * @param PublisherInterface $publisher
     */
    public function __construct(
        PublisherInterface $publisher
    ) {
        $this->publisher = $publisher;
    }

    /**
     * @param string $data
     * @return mixed|null
     */
    public function publish(string $data)
    {
        return $this->publisher->publish(self::TOPIC_NAME, $data);
    }
}
