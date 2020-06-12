<?php

namespace GoMage\LightCheckout\Model\Api;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Psr\Log\LoggerInterface;

class Google extends Base
{
    /**
     * URL to get address info.
     *
     * @const string
     */
    const POST_CODE_API_URL = "https://maps.googleapis.com/maps/api/geocode/json";

    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @param LoggerInterface $logger
     * @param JsonHelper $jsonHelper
     * @param DataObjectFactory $dataObjectFactory
     * @param ZendClientFactory $httpClientFactory
     * @param AllowedCountries $allowedCountries
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
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
        \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection,
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider
    ) {
        parent::__construct(
            $logger,
            $jsonHelper,
            $dataObjectFactory,
            $httpClientFactory,
            $allowedCountries,
            $countryCollection,
            $regionCollection
        );

        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
    }

    /**
     * @param $postCode
     *
     * @return mixed
     * @throws \Zend_Http_Client_Exception
     */
    public function getAddress($postCode)
    {
        $postCode = urlencode(trim($postCode));

        $parameters = [
            'sensor' => true,
            'address' => $postCode
        ];

        $apiKey = trim($this->checkoutConfigurationsProvider->getAutoFillByZipCodeGoogleApiKey());

        if ($apiKey) {
            $parameters['key'] = $apiKey;
        }

        /** @var \Magento\Framework\HTTP\ZendClient $client */
        $client = $this->httpClientFactory->create();
        $client->setUri(self::POST_CODE_API_URL);
        $client->setParameterGet($parameters);
        $client->setMethod(\Zend_Http_Client::GET);

        $responseBodyText = '';

        try {
            $response = $client->request();
            $responseBodyText = $response->getBody();
            $responseBody = $this->jsonHelper->jsonDecode($responseBodyText);
        } catch (\Exception $e) {
            $this->logger->critical('Google Maps response error: ' . $responseBodyText);
            throw new $e;
        }

        if ($responseBody['status'] == "OK") {
            $result = $this->getResultByResponseBody($responseBody);
        } else {
            $result = $this->getEmptyResult();
            $this->logger->critical('Google Maps response error: ' . $responseBodyText);
        }

        $dataObject = $this->dataObjectFactory->create();
        $dataObject->addData($result);

        return $dataObject;
    }

    /**
     * @param $responseBody
     *
     * @return array
     */
    private function getResultByResponseBody($responseBody)
    {
        $result = $this->getEmptyResult();
        $city = $country = $region = '';
        $allowedCountry = $this->allowedCountries->getAllowedCountries();

        if (!empty($responseBody['results'][0]['address_components'])) {
            foreach ($responseBody['results'][0]['address_components'] as $address_component) {
                foreach ($address_component['types'] as $type) {
                    if ($type == "locality") {
                        $city = $address_component['long_name'];
                    } elseif ($type == "administrative_area_level_1") {
                        $region = $address_component['short_name'];
                    } elseif ($type == "country") {
                        $country = $address_component['short_name'];
                    }
                }
            }
        }

        if (in_array($country, $allowedCountry)) {
            $result['completed'] = true;
            $result['city'] = $city;
            $result['country_id'] = $result['country'] = $this->getCountryCode($country);
            $result['region_id'] = $result['region'] = $this->getRegionId($result['country'], $region);
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getEmptyResult()
    {
        return $result = [
            'city' => '',
            'country' => '',
            'country_id' => '',
            'region_id' => '',
            'region' => '',
            'completed' => false
        ];
    }
}
