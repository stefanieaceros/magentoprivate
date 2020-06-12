<?php

namespace GoMage\LightCheckout\Model\CheckoutAdditionalManagement;

use Magento\Checkout\Model\Session;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use GoMage\LightCheckout\Model\Config\Source\Time;

/**
 * Save Delivery Date and Delivery Time to quote before submit order.
 */
class DeliveryDateSaverToQuote
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var Time
     */
    private $time;

    /**
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param TimezoneInterface $timezone
     * @param Time $time
     */
    public function __construct(
        Session $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        TimezoneInterface $timezone,
        Time $time
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->timezone = $timezone;
        $this->time = $time;
    }

    /**
     * @param string[] $additionInformation
     *
     * @return void
     */
    public function execute($additionInformation)
    {
        if (isset($additionInformation['deliveryDate'])) {
            $quote = $this->checkoutSession->getQuote();

            $timezone = $this->timezone->getConfigTimezone();
            $formattedDate = $this->timezone->date($additionInformation['deliveryDate'])->format('Y-m-d');
            $date = new \DateTime($formattedDate, new \DateTimeZone($timezone));
            $date = $this->timezone->convertConfigTimeToUtc($date);

            $quote->setLcDeliveryDate($date);
            unset($additionInformation['deliveryDate']);

            if (isset($additionInformation['deliveryDateTime'])) {
                $timeArray = explode(':', strval($additionInformation['deliveryDateTime']));
                if ($timeArray && count($timeArray) == 2) {
                    $time = ($timeArray[0] * 60 * 60) + ($timeArray[1] * 60);
                    $dateTime = new \DateTime($formattedDate, new \DateTimeZone($timezone));
                    $dateTime->add(new \DateInterval('PT' . $time . 'S'));
                    $time = $this->timezone->convertConfigTimeToUtc($dateTime);

                    $quote->setLcDeliveryDateTime($time);
                }
                unset($additionInformation['deliveryDateTime']);
            }

            $this->checkoutSession->setAdditionalInformation($additionInformation);
            $this->quoteRepository->save($quote);
        }
    }
}
