<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config\TrustSeals\Renderer;

class TrustSeal extends \Magento\Backend\Block\Template
{
    protected $_template = 'GoMage_LightCheckout::config/text_area.phtml';

    /**
     * Set name for input element.
     *
     * @param string $value
     *
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set html id for input element.
     *
     * @param string $id
     *
     * @return $this
     */
    public function setInputId($id)
    {
        return $this->setId($id);
    }
}
