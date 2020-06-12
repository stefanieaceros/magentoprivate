<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config;

use GoMage\LightCheckout\Model\SocialCustomer\BaseAuthUrlProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field as FormField;
use Magento\Framework\Data\Form\Element\AbstractElement;

class RedirectUrl extends FormField
{
    /**
     * @var BaseAuthUrlProvider
     */
    private $baseAuthUrlProvider;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param BaseAuthUrlProvider $baseAuthUrlProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        BaseAuthUrlProvider $baseAuthUrlProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->baseAuthUrlProvider = $baseAuthUrlProvider;
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $elementId = explode('_', $element->getHtmlId());
        $redirectUrl = $this->getAuthUrl(ucfirst($elementId[9]));
        $html = '<input style="opacity:1;" readonly id="' . $element->getHtmlId()
            . '" class="input-text admin__control-text" value="' . $redirectUrl
            . '" onclick="this.select()" type="text">';

        return $html;
    }

    /**
     * @param $type
     *
     * @return string
     */
    private function getAuthUrl($type)
    {
        $authUrl = $this->baseAuthUrlProvider->get($type);

        if ($type == 'Facebook') {
            $param = 'hauth_done=' . $type;
        } else {
            $param = '';
        }

        return $authUrl . ($param ? (strpos($authUrl, '?') ? '&' : '?') . $param : '');
    }
}
