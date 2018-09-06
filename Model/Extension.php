<?php
/**
 * Copyright © BrainActs Commerce OÜ. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace BrainActs\Hub\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

/**
 * Class Extension
 * @author BrainActs Commerce OÜ Core Team <support@brainacts.com>
 */
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
     * Extension constructor.
     * @param DataObjectFactory $dataObjectFactory
     * @param ZendClientFactory $httpClientFactory
     * @param CacheInterface $cache
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
     *
     * @param string $moduleName
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getExtensionInfo($moduleName)
    {

        $data = $this->getServiceRequest($moduleName);

        $object = $this->dataObjectFactory->create();
        $object->addData($data);

        return $object;
    }

    /**
     * Connect to BA server to get info about extension
     * @param string $extensionCode
     * @return array
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
            }
        }

        return json_decode($responseBody, true);
    }

    /**
     * Set Data to cache
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
     * Return data from cache
     * @param $extensionCode
     * @return string
     */
    private function getChachedResponse($extensionCode)
    {
        return $this->cache->load($extensionCode);
    }

    /**
     * Return api url
     * @param $extensionCode
     * @return string
     */
    private function getGatewayUrl($extensionCode)
    {
        $url = self::PRODUCTION_URL;
        return $url . $extensionCode;
    }
}
