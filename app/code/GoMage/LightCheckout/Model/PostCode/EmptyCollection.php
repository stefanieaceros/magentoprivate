<?php

namespace GoMage\LightCheckout\Model\PostCode;

use GoMage\LightCheckout\Model\ResourceModel\PostCode\Collection;

class EmptyCollection
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function execute()
    {
        $this->collection->walk('delete');
    }
}
