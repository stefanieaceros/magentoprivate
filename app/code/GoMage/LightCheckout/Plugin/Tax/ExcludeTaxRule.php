<?php

namespace GoMage\LightCheckout\Plugin\Tax;

use Magento\Checkout\Model\Session;

class ExcludeTaxRule
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param $subject
     * @param array $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCalculationProcess($subject, $result)
    {
        $ruleIdsString = $this->session->getData('light_checkout_exclude_tax_rule_ids');
        if ($ruleIdsString) {
            $ruleIds = explode(',', $ruleIdsString);

            if ($ruleIds) {
                foreach ($result as $key => $taxes) {
                    foreach ($taxes['rates'] as $rate) {
                        if (in_array($rate['rule_id'], $ruleIds)) {
                            unset($result[$key]);
                        }
                    }
                }
            }
        }

        return $result;
    }
}
