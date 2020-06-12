<?php

namespace GoMage\LightCheckout\Model\ResourceModel\SocialCustomer;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \GoMage\LightCheckout\Model\SocialCustomer::class,
            \GoMage\LightCheckout\Model\ResourceModel\SocialCustomer::class
        );
    }
}
