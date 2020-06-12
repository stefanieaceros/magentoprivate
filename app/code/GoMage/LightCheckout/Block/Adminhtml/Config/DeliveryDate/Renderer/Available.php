<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config\DeliveryDate\Renderer;

use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class Available extends Select
{
    /**
     * @var Yesno
     */
    private $yesNo;

    /**
     * @param Context $context
     * @param Yesno $yesNo
     * @param array $data
     */
    public function __construct(
        Context $context,
        Yesno $yesNo,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->yesNo = $yesNo;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->yesNo->toOptionArray());
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
