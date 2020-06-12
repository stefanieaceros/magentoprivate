<?php

namespace GoMage\LightCheckout\Model;

use GoMage\LightCheckout\Api\QuoteItemManagementInterface;
use GoMage\LightCheckout\Model\QuoteItemManagement\ResponseDataInterface;
use GoMage\LightCheckout\Model\QuoteItemManagement\ResponseDataInterfaceFactory;
use GoMage\LightCheckout\Model\QuoteItemManagement\ShippingMethodsProvider;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\Message\ManagerInterface;

class QuoteItemManagement implements QuoteItemManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ResponseDataInterfaceFactory
     */
    private $responseDataFactory;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var CartTotalRepositoryInterface
     */
    private $cartTotalsRepository;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var ShippingMethodsProvider
     */
    private $shippingMethodsProvider;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * QuoteItemManagement constructor.
     * @param CartRepositoryInterface $quoteRepository
     * @param ResponseDataInterfaceFactory $responseDataFactory
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param UrlInterface $url
     * @param ShippingMethodsProvider $shippingMethodsProvider
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ResponseDataInterfaceFactory $responseDataFactory,
        PaymentMethodManagementInterface $paymentMethodManagement,
        CartTotalRepositoryInterface $cartTotalRepository,
        UrlInterface $url,
        ShippingMethodsProvider $shippingMethodsProvider,
        ManagerInterface $messageManager
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->responseDataFactory = $responseDataFactory;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartTotalsRepository = $cartTotalRepository;
        $this->url = $url;
        $this->shippingMethodsProvider = $shippingMethodsProvider;
        $this->messageManager = $messageManager;
    }

    /**
     * @param int $cartId
     * @param TotalsItemInterface $item
     *
     * @return ResponseDataInterface
     */
    public function updateItemQty($cartId, TotalsItemInterface $item)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $itemId = $item->getItemId();
        $itemQty = $item->getQty();

        if ($itemQty <= 0) {
            return $this->removeItemById($cartId, $itemId);
        }

        $quoteItem = $quote->getItemById($itemId);
        if (!$quoteItem) {
            throw new NoSuchEntityException(
                __('Cart item %1 doesn\'t exist.', $itemId)
            );
        }

        try {
            $quoteItem->setQty($itemQty);

            $errorFlag = false;
            if ($errors = $quote->getErrors()) { // if requested quantity is not available
                $errorString = '';
                foreach ($errors as $error) {
                    $errorString .= $error->getText() . ' ';
                }
                $errorFlag = true;
                $this->messageManager->addErrorMessage($errorString);
            } else {
                $this->quoteRepository->save($quote);
            }
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not update item from quote'));
        }

        return $this->getResponseData($quote, $errorFlag);
    }

    /**
     * @inheritdoc
     */
    public function removeItemById($cartId, $itemId)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $quoteItem = $quote->getItemById($itemId);
        if (!$quoteItem) {
            throw new NoSuchEntityException(
                __('Cart item %1 doesn\'t exist.', $itemId)
            );
        }

        try {
            $quote->removeItem($itemId);
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not remove item from quote'));
        }

        return $this->getResponseData($quote);
    }

    /**
     * @param Quote $quote
     *
     * @return ResponseDataInterface
     */
    private function getResponseData(Quote $quote, $errorFlag = false)
    {
        /** @var ResponseDataInterface $responseData */
        $responseData = $this->responseDataFactory->create();

        if (!$quote->hasItems() || !$quote->validateMinimumAmount()) {
            $responseData->setRedirectUrl($this->url->getUrl());
        } elseif ($errorFlag) {
            $responseData->setError(true);
        } else {
            if ($quote->getShippingAddress()->getCountryId()) {
                $responseData->setShippingMethods($this->shippingMethodsProvider->get($quote));
            }
            $responseData->setPaymentMethods($this->paymentMethodManagement->getList($quote->getId()));
            $responseData->setTotals($this->cartTotalsRepository->get($quote->getId()));
        }

        return $responseData;
    }

    /**
     * @inheritdoc
     */
    public function updateSections($cartId)
    {
        try {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->quoteRepository->getActive($cartId);

            return $this->getResponseData($quote);
        } catch (\Exception $e) {
            //nothing to update
        }

        /** @var ResponseDataInterface $responseData */
        $responseData = $this->responseDataFactory->create();

        return $responseData;
    }
}
