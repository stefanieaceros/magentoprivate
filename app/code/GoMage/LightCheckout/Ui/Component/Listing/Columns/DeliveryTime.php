<?php

namespace GoMage\LightCheckout\Ui\Component\Listing\Columns;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class DeliveryTime extends Column
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $timezone
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->timezone = $timezone;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $deliveryDateTime = $item['lc_delivery_date_time'];

                if ($deliveryDateTime) {
                    $timeFormatted = $this->timezone->formatDateTime(
                        $deliveryDateTime,
                        \IntlDateFormatter::NONE,
                        \IntlDateFormatter::SHORT
                    );

                    $item[$this->getName()] = $timeFormatted;
                }
            }
        }

        return $dataSource;
    }
}
