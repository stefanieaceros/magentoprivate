<?php

namespace GoMage\LightCheckout\Model;

class PostCode extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\GoMage\LightCheckout\Model\ResourceModel\PostCode::class);
    }
}
