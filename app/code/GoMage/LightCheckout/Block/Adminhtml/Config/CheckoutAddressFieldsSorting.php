<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config;

use GoMage\LightCheckout\Model\Config\CheckoutAddressFieldsSorting\FieldsDataTransferObject;
use GoMage\LightCheckout\Model\Config\CheckoutAddressFieldsSorting\FieldsProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class CheckoutAddressFieldsSorting extends Field
{
    /**
     * @var string
     */
    protected $_template = 'GoMage_LightCheckout::config/checkout_address_fields_sorting.phtml';

    /**
     * @var FieldsProvider
     */
    public $fieldsProvider;

    /**
     * @var FieldsDataTransferObject
     */
    private $fieldsDataTransferObject;

    /**
     * @param Context $context
     * @param FieldsProvider $fieldsProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        FieldsProvider $fieldsProvider,
        array $data = []
    ) {
        $this->fieldsProvider = $fieldsProvider;

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

         $this->fieldsDataTransferObject = $this->fieldsProvider->get();
    }

    /**
     * Remove scope label.
     *
     * @param  AbstractElement $element
     *
     * @return string
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
     * @return array
     */
    public function getVisibleFields()
    {
        return $this->fieldsDataTransferObject->getVisibleFields();
    }

    /**
     * @return array
     */
    public function geNotVisibleFields()
    {
        return $this->fieldsDataTransferObject->getNotVisibleFields();
    }
}
