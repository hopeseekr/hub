<?php
/**
 * Copyright © BrainActs Commerce OÜ. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace BrainActs\Hub\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\HTTP\ZendClientFactory;

class Extension
{
    const PRODUCTION_URL = 'https://updates.brainacts.eu:8085/info/';

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Framework\App\CacheInterface $cache
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        ZendClientFactory $httpClientFactory,
        CacheInterface $cache
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->httpClientFactory = $httpClientFactory;
        $this->cache = $cache;
    }

    /**
     * @param string $moduleName
     * @return \Magento\Framework\DataObject
     */
    public function getExtensionInfo($moduleName)
    {
        $data = $this->getServiceRequest($moduleName);
        $object = $this->dataObjectFactory->create();
        $object->addData($data);

        return $object;
    }

    /**
     * @param string $extensionCode
     * @return array|mixed
     */
    private function getServiceRequest($extensionCode)
    {
        $responseBody = $this->getChachedResponse($extensionCode);

        if ($responseBody === false) {
            $url = $this->getGatewayUrl($extensionCode);
            try {
                $client = $this->httpClientFactory->create();
                $client->setUri($url);
                $client->setConfig(['maxredirects' => 0, 'timeout' => 1]);
                $response = $client->request();
                $responseBody = $response->getBody();
                $this->setCachedResponse($extensionCode, $responseBody);
            } catch (\Zend_Http_Client_Exception $e) {
                $data = $this->dataObjectFactory->create();
                $data->setVersion(__('N/A'));
                return $data->toArray();
            } catch (\Exception $e) {
                $data = $this->dataObjectFactory->create();
                $data->setVersion(__('N/A'));
                return $data->toArray();
            }
        }

        return json_decode($responseBody, true);
    }

    /**
     * @param string $extensionCode
     * @param string $response
     * @return $this
     */
    private function setCachedResponse($extensionCode, $response)
    {
        $this->cache->save($response, $extensionCode, ['BrainActs', 'Extension', $extensionCode], 3600);
        return $this;
    }

    /**
     * @param string $extensionCode
     * @return string
     */
    private function getChachedResponse($extensionCode)
    {
        return $this->cache->load($extensionCode);
    }

    /**
     * @param string $extensionCode
     * @return string
     */
    private function getGatewayUrl($extensionCode)
    {
        $url = self::PRODUCTION_URL;
        return $url . $extensionCode;
    }
}
