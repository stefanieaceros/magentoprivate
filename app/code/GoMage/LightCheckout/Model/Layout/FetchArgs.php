<?php

namespace GoMage\LightCheckout\Model\Layout;

use Magento\Framework\Config\Converter\Dom\Flat;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\View\LayoutFactory;
use Psr\Log\LoggerInterface;

class FetchArgs
{
    /**
     * @var Flat
     */
    private $flatConverter;

    /**
     * @var InterpreterInterface
     */
    private $argumentInterpreter;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LayoutFactory $layoutFactory
     * @param InterpreterInterface $argumentInterpreter
     * @param LoggerInterface $logger
     * @param Flat $checkoutConverterDomFlat
     */
    public function __construct(
        LayoutFactory $layoutFactory,
        InterpreterInterface $argumentInterpreter,
        LoggerInterface $logger,
        Flat $checkoutConverterDomFlat
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->argumentInterpreter = $argumentInterpreter;
        $this->flatConverter = $checkoutConverterDomFlat;
        $this->logger = $logger;
    }

    /**
     * Fetch data from layout.
     *
     * @param array|string $handles
     * @param string $xpath
     *
     * @return array
     */
    public function execute($handles, $xpath)
    {
        $result = [];
        try {
            $layoutXml = $this->layoutFactory->create()
                ->getUpdate()
                ->load($handles)
                ->asSimplexml();
            $searchResult = $layoutXml->xpath($xpath);

            if ($searchResult) {
                foreach ($searchResult as $element) {
                    $elementDom = dom_import_simplexml($element);
                    $data = $this->argumentInterpreter->evaluate(
                        $this->flatConverter->convert($elementDom)
                    );

                    $result = $this->merge($result, $data);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $result;
    }

    /**
     * @param array $target
     * @param array $source
     *
     * @return array
     */
    private function merge(array $target, array $source)
    {
        foreach ($source as $key => $value) {
            if (is_array($value)) {
                if (!isset($target[$key])) {
                    $target[$key] = [];
                }
                $target[$key] = $this->merge($target[$key], $value);
            } else {
                $target[$key] = $value;
            }
        }

        return $target;
    }
}
