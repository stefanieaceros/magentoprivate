<?php

namespace GoMage\LightCheckout\Model\Api;

use Psr\Log\LoggerInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Directory\Model\AllowedCountries;

abstract class Base
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * @var AllowedCountries
     */
    protected $allowedCountries;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $countryCollection;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    protected $regionCollection;

    /**
     * @param LoggerInterface $logger
     * @param JsonHelper $jsonHelper
     * @param DataObjectFactory $dataObjectFactory
     * @param ZendClientFactory $httpClientFactory
     * @param AllowedCountries $allowedCountries
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     * @param \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection
     */
    public function __construct(
        LoggerInterface $logger,
        JsonHelper $jsonHelper,
        DataObjectFactory $dataObjectFactory,
        ZendClientFactory $httpClientFactory,
        AllowedCountries $allowedCountries,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection
    ) {
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->httpClientFactory = $httpClientFactory;
        $this->allowedCountries = $allowedCountries;
        $this->countryCollection = $countryCollection;
        $this->regionCollection = $regionCollection;
    }

    /**
     * @param $postCode
     * @return mixed
     * @throws \Zend_Http_Client_Exception
     */
    abstract public function getAddress($postCode);

    /**
     * @param  string $code
     *
     * @return string
     */
    protected function getCountryCode($code)
    {
        $country = $this->countryCollection->addCountryCodeFilter($code)->getFirstItem();

        return $country->getCountryId() ?: '';
    }

    /**
     * @param  string $country_code
     * @param  string $code
     *
     * @return string
     */
    protected function getRegionId($country_code, $code)
    {
        $region = $this->regionCollection->addCountryFilter($country_code)
            ->addRegionCodeFilter($code)
            ->getFirstItem();

        return $region->getRegionId() ?: '';
    }
}
