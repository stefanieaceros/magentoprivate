<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config\HelpMessages\Renderer;

use GoMage\LightCheckout\Model\Config\Source\CheckoutFields;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class Field extends Select
{
    /**
     * @var CheckoutFields
     */
    private $checkoutFields;

    /**
     * @param Context $context
     * @param CheckoutFields $checkoutFields
     * @param array $data
     */
    public function __construct(
        Context $context,
        CheckoutFields $checkoutFields,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutFields = $checkoutFields;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->checkoutFields->toOptionArray());
        }

        return parent::_toHtml();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
