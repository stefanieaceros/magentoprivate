<?php

namespace GoMage\LightCheckout\Model\ResourceModel;

class PostCode extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_NAME = 'gomage_postcode';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }
}
