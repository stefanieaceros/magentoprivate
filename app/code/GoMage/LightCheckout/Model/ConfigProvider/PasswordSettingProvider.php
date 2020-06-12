<?php

namespace GoMage\LightCheckout\Model\ConfigProvider;

use Magento\Customer\Model\AccountManagement;
use Magento\Framework\App\Config\ScopeConfigInterface;

class PasswordSettingProvider
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function get()
    {
        $minimumPasswordLength = $this->scopeConfig->getValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH);
        $classesNumber = $this->scopeConfig->getValue(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);

        return [
            'minimumPasswordLength' => $minimumPasswordLength,
            'requiredCharacterClassesNumber' => $classesNumber,
        ];
    }
}
