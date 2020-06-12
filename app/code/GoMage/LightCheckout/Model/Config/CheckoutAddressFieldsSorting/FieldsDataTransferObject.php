<?php

namespace GoMage\LightCheckout\Model\Config\CheckoutAddressFieldsSorting;

class FieldsDataTransferObject
{
    /**
     * @var array
     */
    private $notVisibleFields = [];

    /**
     * @var array
     */
    private $visibleFields = [];

    /**
     * @return array
     */
    public function getVisibleFields()
    {
        return $this->visibleFields;
    }

    /**
     * @param array $visibleFields
     */
    public function setVisibleFields($visibleFields)
    {
        $this->visibleFields = $visibleFields;
    }

    /**
     * @return array
     */
    public function getNotVisibleFields()
    {
        return $this->notVisibleFields;
    }

    /**
     * @param array $notVisibleFields
     */
    public function setNotVisibleFields($notVisibleFields)
    {
        $this->notVisibleFields = $notVisibleFields;
    }
}
