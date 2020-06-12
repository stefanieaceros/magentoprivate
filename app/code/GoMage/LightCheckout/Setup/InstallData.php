<?php

namespace GoMage\LightCheckout\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetup;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;

class InstallData implements InstallDataInterface
{
    const MODULE_NAME = 'GoMage_LightCheckout';

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        $quoteSetup
            ->addAttribute(
                'quote',
                'lc_delivery_date',
                ['type' => Table::TYPE_DATE, 'required' => false]
            )
            ->addAttribute(
                'quote',
                'lc_delivery_date_time',
                ['type' => Table::TYPE_TIMESTAMP, 'required' => false]
            );

        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $salesSetup
            ->addAttribute(
                'order',
                'lc_delivery_date',
                ['type' => Table::TYPE_DATE, 'required' => false]
            )->addAttribute(
                'order',
                'lc_delivery_date_time',
                ['type' => Table::TYPE_TIMESTAMP, 'required' => false]
            );

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_grid'),
            'lc_delivery_date',
            [
                'type' => Table::TYPE_DATE,
                'comment' => 'Light Checkout Delivery Date'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_grid'),
            'lc_delivery_date_time',
            [
                'type' => Table::TYPE_TIMESTAMP,
                'comment' => 'Light Checkout Delivery Time From'
            ]
        );

        $setup->endSetup();
    }
}
