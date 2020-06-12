<?php

namespace GoMage\LightCheckout\Model;

use GoMage\LightCheckout\Api\CheckoutAdditionalManagementInterface;
use GoMage\LightCheckout\Model\CheckoutAdditionalManagement\DeliveryDateSaverToQuote;
use GoMage\LightCheckout\Model\CheckoutAdditionalManagement\CommentOrderSaveToQuote;
use Magento\Checkout\Model\Session;

class CheckoutAdditionalManagement implements CheckoutAdditionalManagementInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var DeliveryDateSaverToQuote
     */
    private $deliveryDateSaverToQuote;

    /**
     * @var CheckoutCustomerSubscriber
     */
    private $checkoutCustomerSubscriber;

    /**
     * @var CommentOrderSaveToQuote
     */
    private $commentOrderSaveToQuote;

    /**
     * @param Session $checkoutSession
     * @param DeliveryDateSaverToQuote $deliveryDateSaverToQuote
     * @param CheckoutCustomerSubscriber $checkoutCustomerSubscriber
     */
    public function __construct(
        Session $checkoutSession,
        DeliveryDateSaverToQuote $deliveryDateSaverToQuote,
        CheckoutCustomerSubscriber $checkoutCustomerSubscriber,
        CommentOrderSaveToQuote $commentOrderSaveToQuote
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->deliveryDateSaverToQuote = $deliveryDateSaverToQuote;
        $this->checkoutCustomerSubscriber = $checkoutCustomerSubscriber;
        $this->commentOrderSaveToQuote = $commentOrderSaveToQuote;
    }

    /**
     * @inheritdoc
     */
    public function saveAdditionalInformation($additionInformation)
    {
        $this->checkoutSession->setAdditionalInformation($additionInformation);

        $this->deliveryDateSaverToQuote->execute($additionInformation);

        if (isset($additionInformation['subscribe'])) {
            $email = null;
            if (isset($additionInformation['customerEmail']) && $additionInformation['customerEmail']) {
                $email = $additionInformation['customerEmail'];
            }

            $this->checkoutCustomerSubscriber->execute($email);
        }
        /**add comment in quote*/
        if (isset($additionInformation['commentOrder'])) {
            $this->commentOrderSaveToQuote->execute($additionInformation['commentOrder']);
        }
        return true;
    }
}
