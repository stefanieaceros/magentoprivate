<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config\HelpMessages;

use GoMage\LightCheckout\Block\Adminhtml\Config\HelpMessages\Renderer\Field;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class HelpMessages extends AbstractFieldArray
{
    /**
     * @var Field
     */
    private $helpMessageFieldRenderer;

    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'field',
            [
                'label' => __('Choose Field or Block'),
                'renderer' => $this->getFieldsOptionsBlockRenderer(),
            ]
        );
        $this->addColumn(
            'help_message',
            [
                'label' => __('Help Message'),
            ]
        );

        $this->_addAfter = false;
    }

    /**
     * @inheritdoc
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $fieldsOptionsRenderer = $this->getFieldsOptionsBlockRenderer();

        $row->setData(
            'option_extra_attrs',
            [
                'option_' . $fieldsOptionsRenderer->calcOptionHash($row->getField()) => 'selected="selected"',
            ]
        );

        parent::_prepareArrayRow($row);
    }

    /**
     * @return Field
     */
    private function getFieldsOptionsBlockRenderer()
    {
        if (!$this->helpMessageFieldRenderer) {
            $this->helpMessageFieldRenderer = $this->createOptionsRendererByBlockClass(Field::class);
        }

        return $this->helpMessageFieldRenderer;
    }

    /**
     * @param $blockClass
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    private function createOptionsRendererByBlockClass($blockClass)
    {
        return $this->getLayout()->createBlock(
            $blockClass,
            '',
            ['data' => ['is_render_to_js_template' => true]]
        );
    }
}
