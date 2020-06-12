<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config\DeliveryDate;

use GoMage\LightCheckout\Block\Adminhtml\Config\DeliveryDate\Renderer\Month;
use GoMage\LightCheckout\Block\Adminhtml\Config\DeliveryDate\Renderer\NumberDay;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class NonWorkingDays extends AbstractFieldArray
{
    /**
     * @var NumberDay
     */
    private $numberDayOptionsRenderer;

    /**
     * @var Month
     */
    private $monthOptionsRenderer;

    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'sort_nonworking',
            [
                'label' => __('Sort'),
            ]
        );
        $this->addColumn(
            'day_nonworking',
            [
                'label' => __('Day'),
                'renderer' => $this->getNumberDayOptionsRenderer(),
            ]
        );
        $this->addColumn(
            'month_nonworking',
            [
                'label' => __('Month'),
                'renderer' => $this->getMonthOptionsRenderer(),
            ]
        );

        $this->_addAfter = false;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $numberDayOptionsRenderer = $this->getNumberDayOptionsRenderer();
        $monthOptionsRenderer = $this->getMonthOptionsRenderer();

        $selectedStr = 'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            [
                'option_' . $numberDayOptionsRenderer->calcOptionHash($row->getDayNonworking()) => $selectedStr,
                'option_' . $monthOptionsRenderer->calcOptionHash($row->getMonthNonworking()) => $selectedStr,
            ]
        );

        return parent::_prepareArrayRow($row);
    }

    /**
     * @return NumberDay
     */
    private function getNumberDayOptionsRenderer()
    {
        if (!$this->numberDayOptionsRenderer) {
            $this->numberDayOptionsRenderer = $this->createOptionsRendererByBlockClass(NumberDay::class);
        }

        return $this->numberDayOptionsRenderer;
    }

    /**
     * @return Month
     */
    private function getMonthOptionsRenderer()
    {
        if (!$this->monthOptionsRenderer) {
            $this->monthOptionsRenderer = $this->createOptionsRendererByBlockClass(Month::class);
        }

        return $this->monthOptionsRenderer;
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

    /**
     * @inheritdoc
     */
    public function getArrayRows()
    {
        $arrayRows = parent::getArrayRows();
        $sortedArray = [];

        foreach ($arrayRows as $key => $arrayRow) {
            $sortedArray[$arrayRow->getSortNonworking()] = $arrayRow;
        }
        ksort($sortedArray);

        return $sortedArray;
    }
}
