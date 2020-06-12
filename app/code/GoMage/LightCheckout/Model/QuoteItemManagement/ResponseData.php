<?php

namespace GoMage\LightCheckout\Model\QuoteItemManagement;

/**
 * Response data for checkout after update the quote.
 */
class ResponseData extends \Magento\Framework\DataObject implements ResponseDataInterface
{
    /**
     * @inheritdoc
     */
    public function getShippingMethods()
    {
        return $this->getData(self::SHIPPING_METHODS);
    }

    /**
     * @inheritdoc
     */
    public function setShippingMethods($shippingMethods)
    {
        return $this->setData(self::SHIPPING_METHODS, $shippingMethods);
    }

    /**
     * @inheritdoc
     */
    public function getPaymentMethods()
    {
        return $this->getData(self::PAYMENT_METHODS);
    }

    /**
     * @inheritdoc
     */
    public function setPaymentMethods($paymentMethods)
    {
        return $this->setData(self::PAYMENT_METHODS, $paymentMethods);
    }

    /**
     * @inheritdoc
     */
    public function getTotals()
    {
        return $this->getData(self::TOTALS);
    }

    /**
     * @inheritdoc
     */
    public function setTotals($totals)
    {
        return $this->setData(self::TOTALS, $totals);
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUrl()
    {
        return $this->getData(self::REDIRECT_URL);
    }

    /**
     * @inheritdoc
     */
    public function setRedirectUrl($url)
    {
        return $this->setData(self::REDIRECT_URL, $url);
    }

    /**
     * @param $error
     * @return ResponseData|mixed
     */
    public function setError($error)
    {
        $error = (true === $error) ? true : false;
        return $this->setData(self::ERROR, $error);
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->getData(self::ERROR);
    }
}
