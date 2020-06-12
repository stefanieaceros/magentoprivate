<?php

namespace GoMage\LightCheckout\Model\ConfigProvider;

use Magento\Quote\Api\PaymentMethodManagementInterface;

class PaymentMethodsListProvider
{
    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     */
    public function __construct(PaymentMethodManagementInterface $paymentMethodManagement)
    {
        $this->paymentMethodManagement = $paymentMethodManagement;
    }

    /**
     * @param int $cartId
     * @return array
     */
    public function get($cartId)
    {
        $result = [];
        foreach ($this->paymentMethodManagement->getList($cartId) as $method) {
            $result[] = [
                'code' => $method->getCode(),
                'title' => $method->getTitle()
            ];
        }
        return $result;
    }
}
