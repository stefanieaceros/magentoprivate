<?php

namespace GoMage\LightCheckout\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SocialCustomer extends AbstractDb
{
    const TABLE_NAME = 'gomage_social_customer';

    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'social_customer_id');
    }
}
