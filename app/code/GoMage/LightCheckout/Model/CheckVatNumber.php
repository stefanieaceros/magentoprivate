<?php

namespace GoMage\LightCheckout\Model;

use GoMage\LightCheckout\Api\CheckVatNumberInterface;
use GoMage\LightCheckout\Model\CheckVatNumber\EsCountriesProvider;
use GoMage\LightCheckout\Model\CheckVatNumber\IsvatChecker;
use GoMage\LightCheckout\Model\CheckVatNumber\ViesChecker;
use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use GoMage\LightCheckout\Model\Config\Source\VerificationSystem;
use Magento\Checkout\Model\Session;
use Psr\Log\LoggerInterface;

class CheckVatNumber implements CheckVatNumberInterface
{
    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var EsCountriesProvider
     */
    private $esCountriesProvider;

    /**
     * @var IsvatChecker
     */
    private $isvatChecker;

    /**
     * @var ViesChecker
     */
    private $viesChecker;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     * @param Session $session
     * @param EsCountriesProvider $esCountriesProvider
     * @param IsvatChecker $isvatChecker
     * @param ViesChecker $viesChecker
     * @param LoggerInterface $logger
     */
    public function __construct(
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider,
        Session $session,
        EsCountriesProvider $esCountriesProvider,
        IsvatChecker $isvatChecker,
        ViesChecker $viesChecker,
        LoggerInterface $logger
    ) {
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
        $this->session = $session;
        $this->esCountriesProvider = $esCountriesProvider;
        $this->isvatChecker = $isvatChecker;
        $this->viesChecker = $viesChecker;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute($vatNumber, $country, $buyWithoutVat)
    {
        $isValidVat = false;
        if ($this->checkoutConfigurationsProvider->getIsEnabledVatTax()) {
            if ($country == "GR") {
                $country = "EL";
            }

            if (in_array($country, $this->esCountriesProvider->get())) {
                $vatNumber = preg_replace('/^' . $country . '/', '', strtoupper($vatNumber));
                $vatVerificationSystem = $this->checkoutConfigurationsProvider->getVatTaxVerificationSystem();

                try {
                    if ($vatVerificationSystem == VerificationSystem::ISVAT) {
                        $isValidVat = $this->isvatChecker->execute($country, $vatNumber);
                    } else {
                        $isValidVat = $this->viesChecker->execute($country, $vatNumber);
                    }
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            }

            if ($buyWithoutVat) {
                $this->byWithoutVat($country, $isValidVat);
            } else {
                $this->session->setData('light_checkout_exclude_tax_rule_ids', null);
            }
        }

        return $isValidVat;
    }

    /**
     * @param string $country
     * @param bool $isValidVat
     *
     * @return void
     */
    private function byWithoutVat($country, $isValidVat)
    {
        $mode = 0;
        if ($country == $this->checkoutConfigurationsProvider->getVatTaxBaseEuCountry()) {
            $mode = $this->checkoutConfigurationsProvider->getVatTaxB2Cb2BBaseEu();
        } elseif (in_array($country, $this->esCountriesProvider->get())) {
            $mode = $this->checkoutConfigurationsProvider->getVatTaxB2Cb2BNotBaseEu();
        }

        if ($mode) {
            $ruleIds = $this->checkoutConfigurationsProvider->getVatTaxRule();
            $ruleIds = implode(',', array_filter(explode(',', $ruleIds)));

            if ($ruleIds) {
                switch ($mode) {
                    case (1):
                        if ($isValidVat) {
                            $this->session->setData('light_checkout_exclude_tax_rule_ids', $ruleIds);
                        } else {
                            $this->session->setData('light_checkout_exclude_tax_rule_ids', null);
                        }
                        break;
                    case (2):
                        $this->session->setData('light_checkout_exclude_tax_rule_ids', $ruleIds);
                        break;
                }
            } else {
                $this->session->setData('light_checkout_exclude_tax_rule_ids', null);
            }
        } else {
            $this->session->setData('light_checkout_exclude_tax_rule_ids', null);
        }
    }
}
