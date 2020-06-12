<?php

namespace GoMage\LightCheckout\Model\QuoteItemManagement;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector;

/**
 * Shipping methods for response data.
 */
class ShippingMethodsProvider
{
    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @var AddressInterface
     */
    private $address;

    /**
     * @var ShippingMethodConverter
     */
    private $shippingMethodConverter;

    /**
     * @param TotalsCollector $totalsCollector
     * @param AddressInterface $address
     * @param ShippingMethodConverter $shippingMethodConverter
     */
    public function __construct(
        TotalsCollector $totalsCollector,
        AddressInterface $address,
        ShippingMethodConverter $shippingMethodConverter
    ) {
        $this->totalsCollector = $totalsCollector;
        $this->address = $address;
        $this->shippingMethodConverter = $shippingMethodConverter;
    }

    /**
     * @param Quote $quote
     *
     * @return array
     */
    public function get(Quote $quote)
    {
        $shippingMethods = [];

        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->addData($this->address->getData());
        $shippingAddress->setCollectShippingRates(true);

        $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);

        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $shippingMethods[] = $this->shippingMethodConverter->modelToDataObject(
                    $rate,
                    $quote->getQuoteCurrencyCode()
                );
            }
        }

        return $shippingMethods;
    }
}
