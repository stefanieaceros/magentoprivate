<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config\DeliveryDate;

use GoMage\LightCheckout\Block\Adminhtml\Config\DeliveryDate\Renderer\Available;
use GoMage\LightCheckout\Block\Adminhtml\Config\DeliveryDate\Renderer\Time;
use GoMage\LightCheckout\Block\Adminhtml\Config\DeliveryDate\Renderer\WeekDay;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class DeliveryDays extends AbstractFieldArray
{
    /**
     * @var Time
     */
    private $timeFromOptionsRenderer;

    /**
     * @var Time
     */
    private $timeToOptionsRenderer;

    /**
     * @var WeekDay
     */
    private $weekDayOptionsRenderer;

    /**
     * @var Available
     */
    private $availableOptionsRenderer;

    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'delivery_sort',
            [
                'label' => __('Sort'),
            ]
        );
        $this->addColumn(
            'delivery_day',
            [
                'label' => __('Delivery Day'),
                'renderer' => $this->getWeekDayOptionsRenderer(),
            ]
        );
        $this->addColumn(
            'delivery_time_from',
            [
                'label' => __('Time From'),
                'renderer' => $this->getTimeFromOptionsRenderer(),
            ]
        );
        $this->addColumn(
            'delivery_time_to',
            [
                'label' => __('Time To'),
                'renderer' => $this->getTimeToOptionsRenderer(),
            ]
        );
        $this->addColumn(
            'delivery_available',
            [
                'label' => __('Available'),
                'renderer' => $this->getAvailableOptionsRenderer(),
            ]
        );

        $this->_addAfter = false;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $timeFromOptionsRenderer = $this->getTimeFromOptionsRenderer();
        $timeToOptionsRenderer = $this->getTimeToOptionsRenderer();
        $dayOptionsRenderer = $this->getWeekDayOptionsRenderer();
        $availableOptionsRenderer = $this->getAvailableOptionsRenderer();

        $selectedStr = 'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            [
                'option_' . $timeFromOptionsRenderer->calcOptionHash($row->getDeliveryTimeFrom()) => $selectedStr,
                'option_' . $timeToOptionsRenderer->calcOptionHash($row->getDeliveryTimeTo()) => $selectedStr,
                'option_' . $dayOptionsRenderer->calcOptionHash($row->getDeliveryDay()) => $selectedStr,
                'option_' . $availableOptionsRenderer->calcOptionHash($row->getDeliveryAvailable()) => $selectedStr,
            ]
        );

        return parent::_prepareArrayRow($row);
    }

    /**
     * @return WeekDay
     */
    private function getWeekDayOptionsRenderer()
    {
        if (!$this->weekDayOptionsRenderer) {
            $this->weekDayOptionsRenderer = $this->createOptionsRendererByBlockClass(WeekDay::class);
        }

        return $this->weekDayOptionsRenderer;
    }

    /**
     * @return Time
     */
    private function getTimeFromOptionsRenderer()
    {
        if (!$this->timeFromOptionsRenderer) {
            $this->timeFromOptionsRenderer = $this->createOptionsRendererByBlockClass(Time::class);
        }

        return $this->timeFromOptionsRenderer;
    }

    /**
     * @return Time
     */
    private function getTimeToOptionsRenderer()
    {
        if (!$this->timeToOptionsRenderer) {
            $this->timeToOptionsRenderer = $this->createOptionsRendererByBlockClass(Time::class);
        }

        return $this->timeToOptionsRenderer;
    }

    /**
     * @return Available
     */
    private function getAvailableOptionsRenderer()
    {
        if (!$this->availableOptionsRenderer) {
            $this->availableOptionsRenderer = $this->createOptionsRendererByBlockClass(Available::class);
        }

        return $this->availableOptionsRenderer;
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

        foreach ($arrayRows as $arrayRow) {
            if ($arrayRow->getDeliverySort()) {
                $sortedArray[$arrayRow->getDeliverySort()] = $arrayRow;
            } else {
                $sortedArray[] = $arrayRow;
            }
        }
        ksort($sortedArray);

        return $sortedArray;
    }
}
