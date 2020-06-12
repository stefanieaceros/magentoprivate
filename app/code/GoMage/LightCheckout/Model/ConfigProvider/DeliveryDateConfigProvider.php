<?php

namespace GoMage\LightCheckout\Model\ConfigProvider;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use GoMage\LightCheckout\Model\Config\Source\DateFormats;
use GoMage\LightCheckout\Model\Config\Source\Hour;
use Magento\Framework\App\Config\ScopeConfigInterface;

class DeliveryDateConfigProvider
{
    /**
     * @var ScopeConfigInterface
     */
    private $checkoutConfigurationsProvider;

    /**
     * @var Hour
     */
    private $hour;

    /**
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     * @param Hour $hour
     */
    public function __construct(
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider,
        Hour $hour
    ) {
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
        $this->hour = $hour;
    }

    /**
     * @return array
     */
    public function get()
    {
        return [
            'displayDeliveryDateText' => $this->checkoutConfigurationsProvider->getIsDisplayDeliveryDateText(),
            'deliveryDateText' => $this->checkoutConfigurationsProvider->getDeliveryDateText(),
            'dateFormat' => $this->getDateFormat(),
            'disabledDays' => $this->getDisabledDays(),
            'nonWorkingDays' => array_merge($this->getNonWorkingDays(), $this->getDaysOffWithIntervalForDelivery()),
            'deliveryDaysWithTime' => $this->getDeliveryDaysWithTime(),
            'deliveryHoursHelper' => $this->hour->toOptionArray(),
            'shippingMethods' => explode(',', $this->checkoutConfigurationsProvider->getUseDeliveryDateFor()),
        ];
    }

    /**
     * @return array
     */
    private function getDisabledDays()
    {
        return $disabledDates = array_diff([0, 1, 2, 3, 4, 5, 6], array_keys($this->getDeliveryDaysWithTime()));
    }

    /**
     * @return array
     */
    private function getDeliveryDaysWithTime()
    {
        $availableDays = $this->checkoutConfigurationsProvider->getDeliveryDays();
        $deliveryDays = [];

        if ($availableDays) {
            $availableDays = json_decode($availableDays, true);
            if (is_array($availableDays) && !empty($availableDays)) {
                foreach ($availableDays as $value) {
                    if ($value['delivery_available']) {
                        $timeRange = $this->getTimeRange($value['delivery_time_from'], $value['delivery_time_to']);
                        if (isset($deliveryDays[$value['delivery_day']])) {
                            $deliveryDays[$value['delivery_day']] = array_merge(
                                $deliveryDays[$value['delivery_day']],
                                $timeRange
                            );
                            $deliveryDays[$value['delivery_day']] = array_unique($deliveryDays[$value['delivery_day']]);
                        } else {
                            $deliveryDays[$value['delivery_day']] = $timeRange;
                        }
                    }
                }
            }
        }

        foreach ($deliveryDays as $key => $value) {
            sort($value, SORT_NUMERIC);
            $deliveryDays[$key] = $value;
        }

        return $deliveryDays;
    }

    /**
     * @param $from
     * @param $to
     *
     * @return array
     */
    private function getTimeRange($from, $to)
    {
        if (!$to) {
            $to = 24;
        }

        $hours = [];
        if ($from > $to) {
            for ($i = $from; $i != $to; $i++) {
                if ($i == 24) {
                    $i = 0;
                }
                $hours[] = $i;
            }
            $hours[] = $i;
        } else {
            for ($i = $from; $i <= $to; $i++) {
                if ($i == 24) {
                    $hours[] = 0;
                } else {
                    $hours[] = $i;
                }
            }
        }

        return $hours;
    }

    /**
     * @return string
     */
    private function getDateFormat()
    {
        if ((int)$this->checkoutConfigurationsProvider->getDateFormat() === DateFormats::EUROPEAN) {
            $format = 'dd.MM.y';
        } else {
            $format = 'MM.dd.y';
        }

        return $format;
    }

    /**
     * @return array
     */
    private function getNonWorkingDays()
    {
        $nonWorkingDaysFromConfig = $this->checkoutConfigurationsProvider->getNonWorkingDays();
        $nonWorkingDays = [];

        if ($nonWorkingDaysFromConfig) {
            $nonWorkingDaysFromConfig = json_decode($nonWorkingDaysFromConfig, true);
            foreach ($nonWorkingDaysFromConfig as $nonWorkingDayFromConfig) {
                $nonWorkingDays[] =
                    [
                        'day' => $nonWorkingDayFromConfig['day_nonworking'],
                        'month' => $nonWorkingDayFromConfig['month_nonworking'],
                    ];
            }
        }

        return $nonWorkingDays;
    }

    /**
     * @return array
     */
    private function getDaysOffWithIntervalForDelivery()
    {
        $interval = $this->checkoutConfigurationsProvider->getIntervalForDelivery();
        $nonWorkingDays = [];

        for ($i = 0; $i < $interval; $i++) {
            $date = time() + $i * 60 * 60 * 24;
            $nonWorkingDays[] =
                [
                    'day' => (int)date('d', $date),
                    'month' => (int)date('m', $date) - 1,
                ];
        }

        return $nonWorkingDays;
    }
}
