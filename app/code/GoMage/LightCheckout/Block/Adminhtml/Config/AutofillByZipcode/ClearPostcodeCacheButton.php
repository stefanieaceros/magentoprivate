<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config\AutofillByZipcode;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ClearPostcodeCacheButton extends Field
{
    /**
     * @var string
     */
    protected $_template = 'GoMage_LightCheckout::config/clear_postcode_cache_button.phtml';

    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('lightcheckout/config/clearPostcodeCache');
    }

    /**
     * Generate collect button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'clear_zip_cache_button',
                'label' => __('Clear Zip Codes Cache'),
            ]
        );

        return $button->toHtml();
    }
}
