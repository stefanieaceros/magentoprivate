<?php

namespace GoMage\LightCheckout\Plugin\Quote;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Model\ShippingMethodManagement;

class SaveShippingAddressBeforeEstimate
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @param ShippingMethodManagement $subject
     * @param \Closure $proceed
     * @param int $cartId The shopping cart ID.
     * @param \Magento\Quote\Api\Data\EstimateAddressInterface $address The estimate address
     *
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundEstimateByAddress(
        ShippingMethodManagement $subject,
        \Closure $proceed,
        $cartId,
        EstimateAddressInterface $address
    ) {
        $this->saveShippingAddress($cartId, $address);

        return $proceed($cartId, $address);
    }

    /**
     * @param ShippingMethodManagement $subject
     * @param \Closure $proceed
     * @param mixed $cartId
     * @param AddressInterface $address
     *
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundEstimateByExtendedAddress(
        ShippingMethodManagement $subject,
        \Closure $proceed,
        $cartId,
        AddressInterface $address
    ) {
        $this->saveShippingAddress($cartId, $address);

        return $proceed($cartId, $address);
    }

    /**
     * @param ShippingMethodManagement $subject
     * @param \Closure $proceed
     * @param int $cartId The shopping cart ID.
     * @param int $addressId The estimate address id
     *
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundEstimateByAddressId(
        ShippingMethodManagement $subject,
        \Closure $proceed,
        $cartId,
        $addressId
    ) {
        $address = $this->addressRepository->getById($addressId);
        $this->saveShippingAddress($cartId, $address);

        return $proceed($cartId, $addressId);
    }

    /**
     * @param $cartId
     * @param $address
     *
     * @return $this
     */
    private function saveShippingAddress($cartId, $address)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        if (!$quote->isVirtual()) {
            $addressData = [
                EstimateAddressInterface::KEY_COUNTRY_ID => $address->getCountryId(),
                EstimateAddressInterface::KEY_POSTCODE => $address->getPostcode(),
                EstimateAddressInterface::KEY_REGION_ID => $address->getRegionId(),
                EstimateAddressInterface::KEY_REGION => $address->getRegion()
            ];

            $shippingAddress = $quote->getShippingAddress();
            try {
                $shippingAddress->addData($addressData)->save();
            } catch (\Exception $e) {
            }
        }

        return $this;
    }
}
