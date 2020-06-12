<?php


namespace GoMage\LightCheckout\Controller\Social;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;

class Callback extends Action
{
    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     */
    public function __construct(Context $context, RawFactory $resultRawFactory)
    {
        parent::__construct($context);

        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($this->checkRequest('hauth_start', false)
            && ($this->checkRequest('error_reason', 'user_denied')
                && $this->checkRequest('error', 'access_denied')
                && $this->checkRequest('error_code', '200')
                && $this->checkRequest('hauth_done', 'Facebook')
                || ($this->checkRequest('hauth_done', 'Twitter') && $this->checkRequest('denied'))
            )) {
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();

            return $resultRaw->setContents("<script>window.close();</script>");
        }

        \Hybrid_Endpoint::process();
    }

    /**
     * @param $key
     * @param null|string $value
     *
     * @return bool|mixed
     */
    private function checkRequest($key, $value = null)
    {
        $param = $this->getRequest()->getParam($key, false);

        if ($value) {
            return $param == $value;
        }

        return $param;
    }
}
