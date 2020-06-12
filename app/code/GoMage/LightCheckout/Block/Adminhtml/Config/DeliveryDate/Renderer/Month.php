<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config\DeliveryDate\Renderer;

use GoMage\LightCheckout\Model\Config\Source\Month as MonthSource;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class Month extends Select
{
    /**
     * @var MonthSource
     */
    private $monthSource;

    /**
     * @param Context $context
     * @param MonthSource $monthSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        MonthSource $monthSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->monthSource = $monthSource;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->monthSource->toOptionArray());
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
