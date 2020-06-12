<?php

namespace GoMage\LightCheckout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Tax\Model\ResourceModel\Calculation\Rule\CollectionFactory;

class TaxRules implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @param CollectionFactory $ruleCollectionFactory
     */
    public function __construct(
        CollectionFactory $ruleCollectionFactory
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options = [];
        $ruleCollection = $this->ruleCollectionFactory->create();

        /** @var \Magento\Tax\Model\Calculation\Rule $item */
        foreach ($ruleCollection as $item) {
            $options[] = [
                'value' => $item->getId(),
                'label' => $item->getCode()
            ];
        }

        return $options;
    }
}
