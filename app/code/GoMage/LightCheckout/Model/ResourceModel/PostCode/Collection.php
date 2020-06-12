<?php

namespace GoMage\LightCheckout\Model\ResourceModel\PostCode;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \GoMage\LightCheckout\Model\PostCode::class,
            \GoMage\LightCheckout\Model\ResourceModel\PostCode::class
        );
    }
}
