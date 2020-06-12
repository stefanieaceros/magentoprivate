<?php

namespace GoMage\LightCheckout\Api;

interface CheckVatNumberInterface
{
    /**
     * @param string $vatNumber
     * @param string $country
     * @param string $buyWithoutVat
     *
     * @return bool
     */
    public function execute($vatNumber, $country, $buyWithoutVat);
}
