<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config\DeliveryDate\Renderer;

use GoMage\LightCheckout\Model\Config\Source\WeekDays as WeekDaysSource;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class WeekDay extends Select
{
    /**
     * @var WeekDaysSource
     */
    private $weekDaysSource;

    /**
     * @param Context $context
     * @param WeekDaysSource $weekDaysSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        WeekDaysSource $weekDaysSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->weekDaysSource = $weekDaysSource;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->weekDaysSource->toOptionArray());
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
