<?php

namespace GoMage\LightCheckout\Model\SocialCustomer;

use Magento\Framework\Url;

class BaseAuthUrlProvider
{

    /**
     * @var Url
     */
    private $urlBuilder;

    /**
     * BaseAuthUrlProvider constructor.
     * @param Url $urlBuilder
     */
    public function __construct(Url $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function get($type)
    {
        $url = $this->urlBuilder->getUrl(
            'lightcheckout/social/login',
            [
                'type' => $type,
            ]
        );
        return str_replace('/index.php', '', $url);
    }
}
