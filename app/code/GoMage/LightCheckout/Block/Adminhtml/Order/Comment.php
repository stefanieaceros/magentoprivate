<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;

class Comment extends Template
{
    protected $_template = 'order/comment/order.phtml';

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    private function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if ($this->getCommentOrder()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Get delivery date.
     *
     * @return string
     */
    public function getCommentOrder()
    {
        return $this->getOrder()->getCommentOrder();
    }
}
