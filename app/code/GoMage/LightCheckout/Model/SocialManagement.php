<?php

namespace GoMage\LightCheckout\Model;

use GoMage\LightCheckout\Model\SocialCustomer\ApiDataProviderByType;
use GoMage\LightCheckout\Model\SocialCustomer\BaseAuthUrlProvider;
use GoMage\LightCheckout\Model\SocialCustomer\CreateCustomerFromArray;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Store\Model\StoreManagerInterface;
use Hybridauth\Hybridauth;
class SocialManagement
{
    /**
     * @var PhpCookieManager
     */
    private $phpCookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var SocialCustomerFactory
     */
    private $socialCustomerFactory;

    /**
     * @var BaseAuthUrlProvider
     */
    private $baseAuthUrlProvider;

    /**
     * @var ApiDataProviderByType
     */
    private $apiDataProviderByType;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var CreateCustomerFromArray
     */
    private $createCustomerFromArray;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param BaseAuthUrlProvider $baseAuthUrlProvider
     * @param ApiDataProviderByType $apiDataProviderByType
     * @param PhpCookieManager $phpCookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SocialCustomerFactory $socialCustomerFactory
     * @param Session $session
     * @param CustomerFactory $customerFactory
     * @param CreateCustomerFromArray $createCustomerFromArray
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        BaseAuthUrlProvider $baseAuthUrlProvider,
        ApiDataProviderByType $apiDataProviderByType,
        PhpCookieManager $phpCookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SocialCustomerFactory $socialCustomerFactory,
        Session $session,
        CustomerFactory $customerFactory,
        CreateCustomerFromArray $createCustomerFromArray,
        StoreManagerInterface $storeManager
    ) {
        $this->baseAuthUrlProvider = $baseAuthUrlProvider;
        $this->apiDataProviderByType = $apiDataProviderByType;
        $this->phpCookieManager = $phpCookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->socialCustomerFactory = $socialCustomerFactory;
        $this->session = $session;
        $this->customerFactory = $customerFactory;
        $this->createCustomerFromArray = $createCustomerFromArray;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    public function getUserProfileByType($type)
    {
        $config = [
            "callback" => $this->baseAuthUrlProvider->get($type),
            "providers" => [
                $type => $this->apiDataProviderByType->get($type)
            ],
            "debug_mode" => false
        ];

        $auth = new Hybridauth($config);
        $adapter = $auth->authenticate($type);
        $userProfile = $adapter->getUserProfile();
        $adapter->disconnect();
        return $userProfile;
    }

    /**
     * @param $userProfile
     * @param string $type
     */
    public function login($userProfile, $type)
    {
        $socialCustomer = $this->socialCustomerFactory->create();
        $socialCustomer = $socialCustomer->getSocialCustomerByIdentifierAndType($userProfile->identifier, $type);

        // check if customer was already identified by social network.
        $customer = $this->getCustomerBySocialCustomer($socialCustomer);

        if (!$customer->getId()) {
            // check if customer with such email is already in system.
            $customer = $this->getCustomerByEmail($this->getEmailByUserProfileAndType($userProfile, $type));
            if ($customer->getId()) {
                // create only social if m2 customer already exist.
                $socialCustomer->createSocialCustomer($userProfile->identifier, $customer->getId(), $type);
            }
        }

        if (!$customer->getId()) {
            //create m2 customer and social customer.
            $customer = $this->createCustomerByUserProfileAndType($userProfile, $type);
            $socialCustomer->createSocialCustomer($userProfile->identifier, $customer->getId(), $type);
        }

        if ($customer->getId()) {
            $this->refreshCustomerData($customer);
        }
    }

    /**
     * @param SocialCustomer $socialCustomer
     *
     * @return \Magento\Customer\Model\Customer
     */
    private function getCustomerBySocialCustomer($socialCustomer)
    {
        $customer = $this->customerFactory->create();

        if ($socialCustomer && $socialCustomer->getId()) {
            $customer->load($socialCustomer->getCustomerId());
        }

        return $customer;
    }

    /**
     * @param string $email
     *
     * @return \Magento\Customer\Model\Customer
     */
    private function getCustomerByEmail($email)
    {
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($this->storeManager->getWebsite()->getId());
        $customer->loadByEmail($email);

        return $customer;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     */
    private function refreshCustomerData($customer)
    {
        $this->session->setCustomerAsLoggedIn($customer);
        $this->session->regenerateId();

        if ($this->phpCookieManager->getCookie('mage-cache-sessid')) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->phpCookieManager->deleteCookie('mage-cache-sessid', $metadata);
        }
    }

    /**
     * @param $userProfile
     * @param string $type
     *
     * @return string
     */
    private function getEmailByUserProfileAndType($userProfile, $type)
    {
        return $userProfile->email ?: $userProfile->identifier . '@' . strtolower($type) . '.com';
    }

    /**
     * @param $userProfile
     * @param string $type
     *
     * @return \Magento\Customer\Model\Customer
     */
    private function createCustomerByUserProfileAndType($userProfile, $type)
    {
        $name = explode(' ', $userProfile->displayName ?: __('New User'));
        $userArray = [
            'email' => $this->getEmailByUserProfileAndType($userProfile, $type),
            'firstname' => $userProfile->firstName ?: (array_shift($name) ?: $userProfile->identifier),
            'lastname' => $userProfile->lastName ?: (array_shift($name) ?: $userProfile->identifier),
            'identifier' => $userProfile->identifier,
            'type' => $type
        ];

        $customer = $this->createCustomerFromArray->execute($userArray);

        return $customer;
    }
}