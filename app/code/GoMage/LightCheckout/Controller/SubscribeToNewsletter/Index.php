<?php

namespace GoMage\LightCheckout\Controller\SubscribeToNewsletter;

use GoMage\LightCheckout\Model\CheckoutCustomerSubscriber;
use Magento\Framework\Controller\Result\JsonFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var CheckoutCustomerSubscriber 
     */
    private $checkoutCustomerSubscriber;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CheckoutCustomerSubscriber $checkoutCustomerSubscriber
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        CheckoutCustomerSubscriber $checkoutCustomerSubscriber,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);

        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->checkoutCustomerSubscriber = $checkoutCustomerSubscriber;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();

        $order = $this->checkoutSession->getLastRealOrder();
        if (!$order->getId()) {
            return $resultJson->setData(
                [
                    'errors' => true,
                    'message' => __('Your session has expired')
                ]
            );
        }
        try {
            $email = $order->getCustomerEmail();
            $this->checkoutCustomerSubscriber->execute($email);

            return $resultJson->setData(
                [
                    'errors' => false,
                ]
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());

            throw $e;
        }
    }
}
