<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config\TrustSeals\Renderer;

use GoMage\LightCheckout\Model\Config\Source\TrustSealsWhereToShow;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class WhereToShow extends Select
{
    /**
     * @var TrustSealsWhereToShow
     */
    private $trustSealsWhereToShow;

    /**
     * @param Context $context
     * @param TrustSealsWhereToShow $trustSealsWhereToShow
     * @param array $data
     */
    public function __construct(
        Context $context,
        TrustSealsWhereToShow $trustSealsWhereToShow,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->trustSealsWhereToShow = $trustSealsWhereToShow;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->trustSealsWhereToShow->toOptionArray());
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
