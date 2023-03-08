<?php

declare(strict_types=1);

namespace KozakGroup\Northbeam\Service;

use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use KozakGroup\Northbeam\Logger\NorthbeamHandlerLogger;
use KozakGroup\Northbeam\Service\Config as ConfigService;

class RequestResponseCurl
{
    private Encryptor $encryptor;

    private Config $configService;

    private CurlFactory $curlFactory;

    private JsonSerializer $jsonSerializer;

    private NorthbeamHandlerLogger $northbeamHandlerLogger;

    public function __construct(
        Encryptor $encryptor,
        ConfigService $configService,
        CurlFactory $curlFactory,
        JsonSerializer $jsonSerializer,
        NorthbeamHandlerLogger $northbeamHandlerLogger
    ) {
        $this->encryptor = $encryptor;
        $this->configService = $configService;
        $this->curlFactory = $curlFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->northbeamHandlerLogger = $northbeamHandlerLogger;
    }

    /**
     * @param array $requestData
     */
    public function postOrder(array $requestData)
    {
        $url = $this->configService->getConfigData('base_url');
        $url .= '/orders';
        $this->callApi($url, \Zend_Http_Client::POST, $requestData);
    }

    /**
     * @param array $requestData
     */
    public function cancelOrder(array $requestData)
    {
        $url = $this->configService->getConfigData('base_url');
        $url .= '/orders/' . $requestData['order_id'] . '/' . $requestData['customer_id'] . '/cancel';
        $this->callApi($url, \Zend_Http_Client::PUT, []);
    }

    /**
     * @return array
     */
    private function generateHeaders(): array
    {
        return [
            'Content-Type: application/json',
            'Data-Client-ID: ' . $this->configService->getConfigData('client_id'),
            'Authorization: Basic ' . $this->configService->getConfigData('api_key')
        ];
    }

    /**
     * @param string $typeCallAPI
     * @param array $requestData
     *
     * @return void
     */
    private function callApi(string $url, string $typeCallAPI, array $requestData)
    {
        $headers = $this->generateHeaders();
        $requestData = [$requestData];
        $requestParams = $this->jsonSerializer->serialize($requestData);
        $this->northbeamHandlerLogger->debug('Send request to Url: ' . $url);
        $this->northbeamHandlerLogger->debug('Request params: ' . $requestParams);

        $httpAdapter = $this->curlFactory->create();
        $httpAdapter->setOptions([
            CURLOPT_RETURNTRANSFER=>true
        ]);
        $httpAdapter->write($typeCallAPI, $url, '1.1', $headers, $requestParams);
        $result = $httpAdapter->read();
        $this->northbeamHandlerLogger->debug('Result: ' . $result);
        $httpAdapter->close();
    }
}
