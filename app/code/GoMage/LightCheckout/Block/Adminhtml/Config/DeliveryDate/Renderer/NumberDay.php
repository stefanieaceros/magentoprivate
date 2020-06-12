<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config\DeliveryDate\Renderer;

use GoMage\LightCheckout\Model\Config\Source\NumberDays as DaySource;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class NumberDay extends Select
{
    /**
     * @var DaySource
     */
    private $daySource;

    /**
     * @param Context $context
     * @param DaySource $daySource
     * @param array $data
     */
    public function __construct(
        Context $context,
        DaySource $daySource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->daySource = $daySource;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->daySource->toOptionArray());
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
