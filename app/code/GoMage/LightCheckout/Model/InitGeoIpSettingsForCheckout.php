<?php

namespace GoMage\LightCheckout\Model;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use GoMage\LightCheckout\Model\GeoIp\Core;
use GoMage\LightCheckout\Model\GeoIp\Record;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class InitGeoIpSettingsForCheckout
{
    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     * @param Filesystem $filesystem
     * @param RemoteAddress $remoteAddress
     * @param CountryFactory $countryFactory
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider,
        Filesystem $filesystem,
        RemoteAddress $remoteAddress,
        CountryFactory $countryFactory,
        RegionFactory $regionFactory
    ) {
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
        $this->filesystem = $filesystem;
        $this->remoteAddress = $remoteAddress;
        $this->countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    public function execute($jsLayout)
    {
        $isGeoIpEnabled = $this->checkoutConfigurationsProvider->getIsEnabledGeoIp();
        $filePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath()
            . '/geoip/GeoLiteCity.dat';

        if ($isGeoIpEnabled && file_exists($filePath)) {
            $ip = $this->remoteAddress->getRemoteAddress();

            $record = Core::getInstance($filePath, Core::GEOIP_STANDARD)->geoip_record_by_addr($ip);

            if ($this->checkoutConfigurationsProvider->getIsEnabledGeoIpForCountry() && $record) {
                $jsLayout = $this->changeValueForAddress($jsLayout, 'country_id', $record->country_code);
            }

            if ($this->checkoutConfigurationsProvider->getIsEnabledGeoIpForCity() && $record) {
                $jsLayout = $this->changeValueForAddress($jsLayout, 'city', $record->city);
            }

            if ($this->checkoutConfigurationsProvider->getIsEnabledGeoIpForZip() && $record) {
                $jsLayout = $this->changeValueForAddress($jsLayout, 'postcode', $record->postal_code);
            }

            if ($this->checkoutConfigurationsProvider->getIsEnabledGeoIpForState() && $record) {
                $jsLayout = $this->changeValueForAddress($jsLayout, 'region_id', $this->prepareRegion($record));
            }
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     * @param string $field
     * @param string $value
     *
     * @return array
     */
    private function changeValueForAddress($jsLayout, $field, $value)
    {
        $jsLayout['components']['checkout']['children']['shippingAddress']['children']
        ['shipping-address-fieldset']['children'][$field]['value'] = $value;

        $jsLayout['components']['checkout']['children']['billingAddress']['children']
        ['billing-address-fieldset']['children'][$field]['value'] = $value;

        return $jsLayout;
    }

    /**
     * @param Record $record
     *
     * @return mixed
     */
    private function prepareRegion(Record $record)
    {
        if ($record->country_code && $record->region) {
            $country = $this->countryFactory->create()->loadByCode($record->country_code);
            if ($country && $country->getId()) {
                $region = $this->regionFactory->create()->loadByCode($record->region, $country->getId());
                if ($region && $region->getId()) {
                    return $region->getId();
                }
            }
        }

        return $record->region;
    }
}
