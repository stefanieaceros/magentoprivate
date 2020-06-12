<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config\DeliveryDate\Renderer;

use GoMage\LightCheckout\Model\Config\Source\Time as TimeSource;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class Time extends Select
{
    /**
     * @var TimeSource
     */
    private $timeSource;

    /**
     * @param Context $context
     * @param TimeSource $timeSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        TimeSource $timeSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->timeSource = $timeSource;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->timeSource->toOptionArray());
        }

        $this->setClass('time-select');

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
