<?php

namespace GoMage\LightCheckout\Api;

use Magento\Quote\Api\Data\TotalsItemInterface;

interface QuoteItemManagementInterface
{
    /**
     * @param int $cartId
     * @param TotalsItemInterface $item
     *
     * @return \GoMage\LightCheckout\Model\QuoteItemManagement\ResponseDataInterface
     */
    public function updateItemQty($cartId, TotalsItemInterface $item);

    /**
     * @param int $cartId
     * @param int $itemId
     *
     * @return \GoMage\LightCheckout\Model\QuoteItemManagement\ResponseDataInterface
     */
    public function removeItemById($cartId, $itemId);

    /**
     * @param int $cartId
     *
     * @return \GoMage\LightCheckout\Model\QuoteItemManagement\ResponseDataInterface
     */
    public function updateSections($cartId);
}
