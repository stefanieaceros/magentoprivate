<?php

namespace GoMage\LightCheckout\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

class CustomerLoginByEmailAndPassword
{
    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var PhpCookieManager
     */
    private $phpCookieManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerUrl
     */
    private $customerUrl;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param AccountManagementInterface $accountManagement
     * @param CustomerSession $customerSession
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param PhpCookieManager $phpCookieManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerUrl $customerUrl
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        AccountManagementInterface $accountManagement,
        CustomerSession $customerSession,
        CookieMetadataFactory $cookieMetadataFactory,
        PhpCookieManager $phpCookieManager,
        CustomerRepositoryInterface $customerRepository,
        CustomerUrl $customerUrl,
        ManagerInterface $messageManager
    ) {
        $this->accountManagement = $accountManagement;
        $this->customerSession = $customerSession;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->phpCookieManager = $phpCookieManager;
        $this->customerRepository = $customerRepository;
        $this->customerUrl = $customerUrl;
        $this->messageManager = $messageManager;
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @throws AuthenticationException
     * @throws EmailNotConfirmedException
     * @throws LocalizedException
     * @throws UserLockedException
     * @throws \Exception
     */
    public function execute($email, $password)
    {
        $customer = null;

        try {
            $customer = $this->accountManagement->authenticate($email, $password);
            $this->customerSession->setCustomerDataAsLoggedIn($customer);
            $this->customerSession->regenerateId();

            if ($this->phpCookieManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->phpCookieManager->deleteCookie('mage-cache-sessid', $metadata);
            }

        } catch (EmailNotConfirmedException $e) {
            $value = $this->customerUrl->getEmailConfirmationUrl($email);
            $message = __(
                'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                $value
            );
            $this->messageManager->addErrorMessage($message);
            $this->customerSession->setUsername($email);
            throw $e;
        } catch (UserLockedException $e) {
            $result = false;
            $message = __('Invalid login or password.');
            $this->messageManager->addErrorMessage($message);
            $this->customerSession->setUsername($email);
            throw $e;
        } catch (AuthenticationException $e) {
            $message = __('Invalid login or password.');
            $this->messageManager->addErrorMessage($message);
            $this->customerSession->setUsername($email);
            throw $e;
        } catch (LocalizedException $e) {
            $message = $e->getMessage();
            $this->messageManager->addError($message);
            $this->customerSession->setUsername($email);
            throw $e;
        } catch (\Exception $e) {
            // PA DSS violation: throwing or logging an exception here can disclose customer password
            $this->messageManager->addErrorMessage(
                __('An unspecified error occurred. Please contact us for assistance.')
            );
            throw $e;
        }

        return $customer;
    }
}
