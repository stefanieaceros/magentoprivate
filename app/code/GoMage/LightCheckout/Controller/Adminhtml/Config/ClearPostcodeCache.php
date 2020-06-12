<?php

namespace GoMage\LightCheckout\Controller\Adminhtml\Config;

use GoMage\Core\Helper\Data;
use GoMage\LightCheckout\Model\PostCode\EmptyCollection;
use GoMage\LightCheckout\Setup\InstallData;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;

class ClearPostcodeCache extends Action
{
    /**
     * @type JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var EmptyCollection
     */
    private $emptyCollection;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param EmptyCollection $emptyCollection
     * @param Data $helper
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        EmptyCollection $emptyCollection,
        Data $helper
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->emptyCollection = $emptyCollection;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $success = true;
        $message = __('Cache was successfully cleared');

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();

        if ($this->helper->isA(InstallData::MODULE_NAME)) {
            try {
                $this->emptyCollection->execute();
            } catch (\Exception $e) {
                $success = false;
                $message = $e->getMessage();
            }
        }

        return $result->setData(['success' => $success, 'message' => $message]);
    }

    /**
     * @inheritdoc
     */
    public function _isAllowed()
    {
        if (!$this->helper->isA(InstallData::MODULE_NAME)) {
            return false;
        }

        return parent::_isAllowed();
    }
}
