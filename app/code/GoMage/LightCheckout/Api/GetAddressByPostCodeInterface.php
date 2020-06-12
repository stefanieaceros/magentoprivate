<?php

namespace GoMage\LightCheckout\Api;

interface GetAddressByPostCodeInterface
{
    /**
     * @param string $postcode
     *
     * @return \GoMage\LightCheckout\Model\GetAddressByPostCode\ResponseDataInterface
     */
    public function execute($postcode);
}
