<?php

namespace GoMage\LightCheckout\Model\Api;

use Magento\Framework\ObjectManagerInterface;

class Factory
{
    const NAMESPACE_FACTORY = 'GoMage\LightCheckout\Model\Api';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param $className
     * @return mixed
     */
    public function get($className)
    {
        $className = static::NAMESPACE_FACTORY . '\\' . $className;
        return $this->objectManager->get($className);
    }
}
