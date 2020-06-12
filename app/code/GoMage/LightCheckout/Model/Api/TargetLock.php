<?php

namespace GoMage\LightCheckout\Model\Api;

class TargetLock extends Base
{
    /**
     * URL to get address info
     *
     * @const string
     */
    const POST_CODE_API_URL = "https://api.targetlock.io/v1/post-code/";

    /**
     * @param $postCode
     *
     * @return mixed
     * @throws \Zend_Http_Client_Exception
     */
    public function getAddress($postCode)
    {
        $result = [
            'city' => '',
            'country' => '',
            'country_id' => '',
            'region_id' => '',
            'region' => '',
            'completed' => false,
        ];

        $postCode = urlencode(trim($postCode));

        $allowedCountry = $this->allowedCountries->getAllowedCountries();

        /** @var \Magento\Framework\HTTP\ZendClient $client */
        $client = $this->httpClientFactory->create();
        $client->setUri(self::POST_CODE_API_URL . $postCode);
        $client->setMethod(\Zend_Http_Client::GET);
        $client->setConfig([
            'verifyhost' => false,
            'verifypeer' => false
        ]);

        $responseBodyText = '';
        try {
            $response = $client->request();
            $responseBodyText = $response->getBody();
            $responseBody = $this->jsonHelper->jsonDecode($responseBodyText);
        } catch (\Exception $e) {
            $this->logger->critical('TargetLock response error: ' . $responseBodyText);
            throw new $e;
        }

        if ($responseBody) {
            $country = $responseBody['country_iso2'];
            $city = $responseBody['locality'];
            $region = $responseBody['admin_level_1_short'];

            if (in_array($country, $allowedCountry)) {
                $result['completed'] = true;
                $result['city'] = $city;
                $result['country_id'] = $result['country'] = $this->getCountryCode($country);
                $result['region_id'] = $result['region'] = $this->getRegionId($country, $region);
            }
        } else {
            $this->logger->critical('TargetLock response error: ' . $responseBodyText);
        }

        $dataObject = $this->dataObjectFactory->create();
        $dataObject->addData($result);

        return $dataObject;
    }
}
