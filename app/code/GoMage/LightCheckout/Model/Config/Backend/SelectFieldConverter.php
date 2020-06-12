<?php

namespace GoMage\LightCheckout\Model\Config\Backend;

use Magento\Framework\App\Config\Value;

class SelectFieldConverter extends Value
{
    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        $result = [];
        $value = $this->getValue();
        foreach ($value as $data) {
            if (is_array($data)) {
                $result[] = $data;
            }
        }
        $this->setValue(json_encode($result));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function afterLoad()
    {
        $value = json_decode($this->getValue(), true);
        if (is_array($value)) {
            $this->setValue($value);
        }

        return $this;
    }
}
