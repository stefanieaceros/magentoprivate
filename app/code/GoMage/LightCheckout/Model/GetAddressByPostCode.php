<?php

namespace GoMage\LightCheckout\Model;

use GoMage\LightCheckout\Api\GetAddressByPostCodeInterface;
use GoMage\LightCheckout\Model\Api\Factory;
use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use GoMage\LightCheckout\Model\Config\Source\AutofillByZipCode\Mode;
use GoMage\LightCheckout\Model\GetAddressByPostCode\ResponseDataInterfaceFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class GetAddressByPostCode implements GetAddressByPostCodeInterface
{
    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @var PostCodeFactory
     */
    private $postcodeFactory;

    /**
     * @var Mode
     */
    private $mode;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var ResponseDataInterfaceFactory
     */
    private $responseDataFactory;

    /**
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     * @param JsonHelper $jsonHelper
     * @param PostCodeFactory $postcodeFactory
     * @param Mode $mode
     * @param Factory $factory
     * @param ResponseDataInterfaceFactory $responseDataFactory
     */
    public function __construct(
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider,
        JsonHelper $jsonHelper,
        PostCodeFactory $postcodeFactory,
        Mode $mode,
        Factory $factory,
        ResponseDataInterfaceFactory $responseDataFactory
    ) {
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
        $this->jsonHelper = $jsonHelper;
        $this->postcodeFactory = $postcodeFactory;
        $this->mode = $mode;
        $this->factory = $factory;
        $this->responseDataFactory = $responseDataFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute($postcode)
    {
        $addressFromCache = $this->getAddressByPostcodeCached($postcode);

        if ($addressFromCache !== null) {
            $address = $addressFromCache;
        } else {
            $result = $this->callApi($postcode);
            if ($result->getCompleted()) {
                $this->saveAddressByPostcodeCached($postcode, $result->getData());
            }
            $address = $result->getData();
        }

        return $this->getResponseData($address);
    }

    /**
     * @param string $postcode
     *
     * @return array
     */
    private function getAddressByPostcodeCached($postcode)
    {
        if (!$this->checkoutConfigurationsProvider->getAutoFillByZipCodeIsEnabledZipCaching()) {
            return null;
        }

        $postcodeModel = $this->postcodeFactory->create();
        $postcodeModel->load($postcode, 'zip_code');
        if (!$postcodeModel->getEncodedData()) {
            $result = null;
        } else {
            $result = $this->jsonHelper->jsonDecode($postcodeModel->getEncodedData());
        }

        return $result;
    }

    /**
     * @param $postcode
     *
     * @return mixed
     */
    private function callApi($postcode)
    {
        $result = null;
        $apiModes = $this->checkoutConfigurationsProvider->getAutoFillByZipCodeApiMode();

        if (!$apiModes) {
            $apiModes = [];
            foreach ($this->mode->toOptionArray() as $item) {
                array_push($apiModes, $item['value']);
            }
        } else {
            $apiModes = explode(",", $apiModes);
        }

        foreach ($apiModes as $className) {
            try {
                $apiClass = $this->factory->get($className);
                $result = $apiClass->getAddress($postcode);
            } catch (\Exception $e) {
                continue;
            }

            if ($result->getCompleted()) {
                break;
            }
        }

        return $result;
    }

    /**
     * @param string $postcode
     * @param array $resultData
     *
     * @return void
     */
    private function saveAddressByPostcodeCached($postcode, $resultData)
    {
        if (!$this->checkoutConfigurationsProvider->getAutoFillByZipCodeIsEnabledZipCaching()) {
            return null;
        }

        $resultEncoded = $this->jsonHelper->jsonEncode($resultData);

        $postcodeModel = $this->postcodeFactory->create();
        $postcodeModel->setZipCode($postcode);
        $postcodeModel->setEncodedData($resultEncoded);
        $postcodeModel->save();
    }

    /**
     * @param array $address
     *
     * @return GetAddressByPostCode\ResponseDataInterface
     */
    private function getResponseData($address)
    {
        $responseData = $this->responseDataFactory->create();

        $responseData->setCity($address['city']);
        $responseData->setCountry($address['country']);
        $responseData->setCountryId($address['country_id']);
        $responseData->setRegion($address['region']);
        $responseData->setRegionId($address['region_id']);

        $isDisabledAddressFields = $this->checkoutConfigurationsProvider->getAutoFillByZipCodeIsDisabledAddressFields();

        if ($isDisabledAddressFields) {
            if ($address['city'] && $address['country_id'] && $address['region_id']) {
                $responseData->setEnableFields(false);
            } else {
                $responseData->setEnableFields(true);
            }
        }

        return $responseData;
    }
}
