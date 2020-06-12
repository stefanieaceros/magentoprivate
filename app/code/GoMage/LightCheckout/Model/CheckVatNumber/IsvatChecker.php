<?php

namespace GoMage\LightCheckout\Model\CheckVatNumber;

use Magento\Framework\HTTP\Client\Curl;

class IsvatChecker
{
    /**
     * @var Curl
     */
    private $curl;

    /**
     * @param Curl $curl
     */
    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
    }

    /**
     * @param string $country
     * @param string $vatNumber
     *
     * @return bool
     */
    public function execute($country, $vatNumber)
    {
        $this->curl->post('http://isvat.appspot.com/' . $country . '/' . $vatNumber . '/', []);
        $content = $this->curl->getBody();
        $isValidVat = !(strpos($content, "true") === false);

        return $isValidVat;
    }
}
