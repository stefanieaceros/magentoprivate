<?php

namespace GoMage\LightCheckout\Block;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Onepage extends Template
{
    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var CompositeConfigProvider
     */
    private $configProvider;

    /**
     * @var LayoutProcessorInterface[]
     */
    private $layoutProcessors;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @param Context $context
     * @param FormKey $formKey
     * @param CompositeConfigProvider $configProvider
     * @param Session $customerSession
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     * @param array $layoutProcessors
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        CompositeConfigProvider $configProvider,
        Session $customerSession,
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->formKey = $formKey;
        $this->_isScopePrivate = true;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout'])
            ? $data['jsLayout']
            : [];
        $this->configProvider = $configProvider;
        $this->layoutProcessors = $layoutProcessors;
        $this->customerSession = $customerSession;
        $this->url = $context->getUrlBuilder();
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
    }

    /**
     * @inheritdoc
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }
        return \Zend_Json::encode($this->jsLayout);
    }

    /**
     * Get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Retrieve checkout configuration
     *
     * @return array
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * @return bool|string
     */
    public function getSerializedCheckoutConfig()
    {
        return json_encode($this->getCheckoutConfig(), JSON_HEX_TAG);
    }

    /**
     * @return bool
     */
    public function isRegistrationPopupAvailable()
    {
        $isRegistrationPopupAvailable = !$this->customerSession->isLoggedIn() &&
            $this->checkoutConfigurationsProvider->getCheckoutMode() == 1;

        if ($isRegistrationPopupAvailable) {
            $this->setRedirectUrlCheckoutAfterRegistration();
        }

        return $isRegistrationPopupAvailable;
    }

    /**
     * Set redirect to checkout after registration on checkout.
     */
    private function setRedirectUrlCheckoutAfterRegistration()
    {
        $url = $this->url->getUrl('checkout');
        $this->customerSession->setBeforeAuthUrl($url);
    }
}
