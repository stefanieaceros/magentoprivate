<?php

namespace GoMage\LightCheckout\Plugin;

use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\ResourceModel\Quote\Address\CollectionFactory;

class DefaultProvider
{
    /**
     * @var Session
     */
    private $session;
    /**
     * @var CollectionFactory
     */
    private $addressFactory;

    /**
     * DefaultProvider constructor.
     * @param Session $session
     * @param AddressFactory $addressFactory
     */
    public function __construct(Session $session, CollectionFactory $addressFactory)
    {
        $this->addressFactory = $addressFactory;
        $this->session = $session;
    }

    /**
     * @param DefaultConfigProvider $subject
     * @param $result
     * @return mixed
     */
    public function afterGetConfig(DefaultConfigProvider $subject, $result)
    {
        try {
            if (isset($result['customerData']['addresses'])) {
                /** @var \Magento\Quote\Model\ResourceModel\Quote\Address\Collection $addressCollection */
                $addressCollection = $this->addressFactory->create();
                $quoteId = $this->session->getQuote()->getId();
                $addressCollection->addFieldToFilter('quote_id', ['eq' => $quoteId]);
                foreach ($addressCollection as $item) {
                   foreach ($result['customerData']['addresses'] as $key => $address) {
                       if($result['customerData']['addresses'][$key]['id'] == $item->getCustomerAddressId()) {
                           $result['quoteAddressInfo'][$item->getCustomerAddressId()]['addressesType'][$item->getAddressType()] = $item->getAddressType();
                       }
                   }
                }
            }
        } catch (\Exception $e) {
        }
        return $result;
    }
}