<?php

namespace GoMage\LightCheckout\Setup;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Config
     */
    private $config;


    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * UpgradeData constructor.
     * @param Config $config
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        Config $config,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.0', '<')) {
            $addressFields = [
                [
                    'code' => 'firstname',
                    'isWide' => false,
                ],
                [
                    'code' => 'lastname',
                    'isWide' => false,
                ],
                [
                    'code' => 'postcode',
                    'isWide' => false,
                ],
                [
                    'code' => 'country_id',
                    'isWide' => false,
                ],
                [
                    'code' => 'region_id',
                    'isWide' => false,
                ],
                [
                    'code' => 'city',
                    'isWide' => false,
                ],
                [
                    'code' => 'street',
                    'isWide' => true,
                ],
                [
                    'code' => 'telephone',
                    'isWide' => true,
                ],
            ];
            $this->config->saveConfig(
                CheckoutConfigurationsProvider::XML_PATH_LIGHT_CHECKOUT_ADDRESS_FIELDS_FORM,
                json_encode($addressFields),
                'default',
                0
            );
        }

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->updateTo101($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function updateTo101(ModuleDataSetupInterface $setup)
    {
        $setup->startSetup();

        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        $quoteSetup
            ->addAttribute(
                'quote',
                'comment_order',
                ['type' => Table::TYPE_TEXT, 'required' => false]
            );


        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $salesSetup->addAttribute(
            'order',
            'comment_order',
            ['type' => Table::TYPE_TEXT, 'required' => false]
        );
    }
}
