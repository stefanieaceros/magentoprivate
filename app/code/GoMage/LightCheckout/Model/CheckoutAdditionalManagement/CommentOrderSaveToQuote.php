<?php

namespace GoMage\LightCheckout\Model\CheckoutAdditionalManagement;

use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Save Comment Order to quote before submit order.
 */
class CommentOrderSaveToQuote
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * CommentOrderSaveToQuote constructor.
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        Session $checkoutSession,
        CartRepositoryInterface $quoteRepository
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * turn void
     */
    public function execute($comment)
    {
        if (isset($comment)) {
            $quote = $this->checkoutSession->getQuote();
            $quote->setCommentOrder($comment);
            $this->quoteRepository->save($quote);
        }
    }
}
