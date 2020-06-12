<?php

namespace GoMage\LightCheckout\Model;

use Magento\Framework\Model\AbstractModel;

class SocialCustomer extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(\GoMage\LightCheckout\Model\ResourceModel\SocialCustomer::class);
    }

    /**
     * @param $identify
     * @param $type
     *
     * @return $this
     */
    public function getSocialCustomerByIdentifierAndType($identify, $type)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('social_id', $identify)
            ->addFieldToFilter('type', $type);
        $collection->getSelect()->limit(1);

        return $collection->getFirstItem();
    }

    /**
     * @param $identifier
     * @param $customerId
     * @param $type
     *
     * @return $this
     */
    public function createSocialCustomer($identifier, $customerId, $type)
    {
        $this->setData([
            'social_id' => $identifier,
            'customer_id' => $customerId,
            'type' => $type
        ])
            ->setId(null)
            ->save();

        return $this;
    }
}
