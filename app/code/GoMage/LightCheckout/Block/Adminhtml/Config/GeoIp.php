<?php

namespace GoMage\LightCheckout\Block\Adminhtml\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Filesystem;

class GeoIp extends Field
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->filesystem = $context->getFilesystem();
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $filePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath()
            . '/geoip/GeoLiteCity.dat';

        if (!file_exists($filePath)) {
            $element->setDisabled(true);
            $element->setValue(0);
            if ($element->getId() == 'gomage_light_checkout_configuration_geoip_enable') {
                $element->setComment(
                    sprintf(
                        __('To use GeoIP you need to upload GeoliteCity.dat file to folder pub/media/geoip.'
                            .' Read more in the <a target="_blank" href="%s">Installation Guide</a>'),
                        'https://wiki.gomage.com/hc/en-us/articles/115002196431-GeoIP-Settings'
                    )
                );
            }
        }

        return parent::_getElementHtml($element);
    }
}
