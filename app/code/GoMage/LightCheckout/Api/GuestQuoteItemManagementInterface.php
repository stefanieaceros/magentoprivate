<?php

namespace GoMage\LightCheckout\Api;

use Magento\Quote\Api\Data\TotalsItemInterface;

interface GuestQuoteItemManagementInterface
{
    /**
     * @param string $cartId
     * @param TotalsItemInterface $item
     *
     * @return \GoMage\LightCheckout\Model\QuoteItemManagement\ResponseDataInterface
     */
    public function updateItemQty($cartId, TotalsItemInterface $item);

    /**
     * @param string $cartId
     * @param int $itemId
     *
     * @return \GoMage\LightCheckout\Model\QuoteItemManagement\ResponseDataInterface
     */
    public function removeItemById($cartId, $itemId);

    /**
     * @param string $cartId
     *
     * @return \GoMage\LightCheckout\Model\QuoteItemManagement\ResponseDataInterface
     */
    public function updateSections($cartId);
}
