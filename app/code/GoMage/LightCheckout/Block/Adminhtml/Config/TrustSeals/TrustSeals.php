<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config\TrustSeals;

use GoMage\LightCheckout\Block\Adminhtml\Config\TrustSeals\Renderer\TrustSeal;
use GoMage\LightCheckout\Block\Adminhtml\Config\TrustSeals\Renderer\WhereToShow;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class TrustSeals extends AbstractFieldArray
{
    /**
     * @var TrustSeal
     */
    private $trustStealOptionsBlockRenderer;

    /**
     * @var WhereToShow
     */
    private $whereToShowOptionsBlockRenderer;

    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'trust_seal',
            [
                'label' => __('Trust Seal'),
                'renderer' => $this->getTrustStealOptionsBlockRenderer(),
            ]
        );
        $this->addColumn(
            'where_to_show',
            [
                'label' => __('Where to Show'),
                'renderer' => $this->getWhereToShowOptionsBlockRenderer(),
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
        $whereToShowOptionsRenderer = $this->getWhereToShowOptionsBlockRenderer();

        $selectedStr = 'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            [
                'option_' . $whereToShowOptionsRenderer->calcOptionHash($row->getWhereToShow()) => $selectedStr,
            ]
        );

        parent::_prepareArrayRow($row);
    }

    /**
     * @return TrustSeal
     */
    private function getTrustStealOptionsBlockRenderer()
    {
        if (!$this->trustStealOptionsBlockRenderer) {
            $this->trustStealOptionsBlockRenderer = $this->createOptionsRendererByBlockClass(TrustSeal::class);
        }

        return $this->trustStealOptionsBlockRenderer;
    }

    /**
     * @return WhereToShow
     */
    private function getWhereToShowOptionsBlockRenderer()
    {
        if (!$this->whereToShowOptionsBlockRenderer) {
            $this->whereToShowOptionsBlockRenderer = $this->createOptionsRendererByBlockClass(WhereToShow::class);
        }

        return $this->whereToShowOptionsBlockRenderer;
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
