<?php

namespace GoMage\LightCheckout\Api;

interface CheckoutAdditionalManagementInterface
{
    /**
     * @param string[] $additionInformation
     *
     * @return bool
     */
    public function saveAdditionalInformation($additionInformation);
}
