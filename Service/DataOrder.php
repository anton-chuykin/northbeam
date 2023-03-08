<?php

declare(strict_types=1);

namespace KozakGroup\Northbeam\Service;

use Exception;
use Magento\Directory\Model\Country;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\CreditmemoRepositoryInterface as CreditMemoRepository;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Psr\Log\LoggerInterface;

class DataOrder
{
    private CreditMemoRepository $creditMemoRepository;

    private SearchCriteriaBuilder $searchCriteriaBuilder;

    private LoggerInterface $logger;

    private Country $country;

    public function __construct(
        CreditMemoRepository $creditMemoRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger,
        Country $country
    ) {
        $this->creditMemoRepository = $creditMemoRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->country = $country;
    }

    /**
     * @param OrderInterface $order
     *
     * @return array
     */
    public function generateRequestData(OrderInterface $order): array
    {
        $orderId = $order->getData('increment_id');
        $allShippingData = $order->getShippingAddress();
        $shippingData = $this->generateShippingData($allShippingData);
        $productsData = $this->generateProductData($order->getAllVisibleItems());
        $refunds = $this->getCreditMemoDataById((int)$order->getEntityId());
        $createdAt = new \Datetime($order->getCreatedAt());

        return [
            'order_id' => $orderId,
            'customer_id' => $order->getData('customer_email'),
            'time_of_purchase' => $createdAt->format(\DateTime::ATOM),
            'customer_email' => $order->getData('customer_email'),
            'customer_phone_number' => $allShippingData->getData('telephone'),
            'customer_name' => $order->getData('customer_firstname'),
            'customer_shipping_address' => $shippingData,
            'customer_ip_address' => $order->getData('remote_ip') ?? '',
            'discount_codes' => explode(',', (string)$order->getData('coupon_code')),
            'discount_amount' => (float) $order->getData('discount_amount') ?? 0.0,
            'order_tags' => [],
            'currency' => $order->getData('order_currency_code'),
            'purchase_total' => (float) $order->getData('grand_total'),
            'tax' => (float) $order->getData('tax_amount'),
            'is_recurring_order' => false,
            'products' => $productsData,
            'refunds' => $refunds ?? []
        ];
    }

    /**
     * @param OrderAddressInterface $allShippingData
     *
     * @return array
     */
    private function generateShippingData(OrderAddressInterface $allShippingData): array
    {
        $country = $this->country->loadByCode($allShippingData->getData('country_id'));
        return [
            'address1' => $allShippingData->getData('street'),
            'address2' => '',
            'city' => $allShippingData->getData('city'),
            'state' => $allShippingData->getData('region') ?? '',
            'zip' => $allShippingData->getData('postcode'),
            'country_code' => $country->getData('iso3_code'),
        ];
    }

    /**
     * @param OrderItemInterface[] $products
     *
     * @return array
     */
    private function generateProductData(array $orderItems): array
    {
        $items = [];

        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() == 'configurable') {
                $child = $orderItem->getChildrenItems()[0];
                $sku = $child->getData('sku');
                $name = $child->getData('name');
            } else {
                $sku = $orderItem->getData('sku');
                $name = $orderItem->getData('name');
            }
            $item['id'] = $sku;
            $item['name'] = $name;
            $item['quantity'] = (int) $orderItem->getData('qty_ordered');
            $item['price'] = (float) $orderItem->getData('price_incl_tax');
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param int $orderId
     *
     * @return array
     */
    private function getCreditMemoDataById(int $orderId): array
    {
        $creditMemoData = [];
        if ($orderId) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('order_id', $orderId)->create();
            try {
                $creditMemoList = $this->creditMemoRepository->getList($searchCriteria)->getItems();
                foreach ($creditMemoList as $creditmemo) {
                    $creditmemoCreatedAt = new \Datetime($creditmemo->getCreatedAt());
                    foreach ($creditmemo->getItems() as $creditmemoItem) {
                        if ($creditmemoItem->getRowTotal()) {
                            if ($creditmemoItem->getOrderItem()->getProductType() == 'configurable') {
                                $sku = $creditmemoItem->getOrderItem()->getChildrenItems()[0]->getSku();
                            } else {
                                $sku = $creditmemoItem->getSku();
                            }
                            $creditMemoData[] = [
                                'product_id' => $sku,
                                'quantity' => (int) $creditmemoItem->getQty(),
                                'refund_amount' => (float) $creditmemoItem->getPriceInclTax(),
                                'refund_cost' => (float) $creditmemoItem->getPriceInclTax(),
                                'refund_made_at' => $creditmemoCreatedAt->format(\Datetime::ATOM)
                            ];
                        }
                    }
                }
            } catch (Exception $exception) {
                $this->logger->critical($exception->getMessage());
                $creditMemoData = [];
            }
        }

        return $creditMemoData;
    }
}
