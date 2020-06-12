<?php

namespace GoMage\LightCheckout\Model\CheckVatNumber;

use Magento\Framework\Webapi\Soap\ClientFactory;

class ViesChecker
{
    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @param ClientFactory $clientFactory
     */
    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    /**
     * @param string $country
     * @param string $vatNumber
     *
     * @return bool
     */
    public function execute($country, $vatNumber)
    {
        $check = $this->clientFactory->create('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl');
        $content = $check->checkVat(['countryCode' => $country, 'vatNumber' => $vatNumber]);
        $isValidVat = $content->valid;

        return $isValidVat;
    }
}
