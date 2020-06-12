<?php

namespace GoMage\LightCheckout\Model\GetAddressByPostCode;

interface ResponseDataInterface
{
    const CITY = 'city';
    const COUNTRY = 'country';
    const COUNTRY_ID = 'country_id';
    const REGION = 'region';
    const REGION_ID = 'region_id';
    const REDIRECT_URL = 'redirect_url';
    const ENABLE_FIELDS = 'enable_fields';

    /**
     * @return string
     */
    public function getCity();

    /**
     * @param $city
     *
     * @return $this
     */
    public function setCity($city);

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @param $country
     *
     * @return $this
     */
    public function setCountry($country);

    /**
     * @return string
     */
    public function getCountryId();

    /**
     * @param $countryId
     *
     * @return $this
     */
    public function setCountryId($countryId);

    /**
     * @return string
     */
    public function getRegion();

    /**
     * @param $region
     *
     * @return $this
     */
    public function setRegion($region);

    /**
     * @return string
     */
    public function getRegionId();

    /**
     * @param $regionId
     *
     * @return $this
     */
    public function setRegionId($regionId);

    /**
     * @return bool
     */
    public function getEnableFields();

    /**
     * @param $disableFields
     *
     * @return $this
     */
    public function setEnableFields($disableFields);

    /**
     * Get redirect url.
     *
     * @return string
     */
    public function getRedirectUrl();

    /**
     * Set redirect url.
     *
     * @param $url
     *
     * @return $this
     */
    public function setRedirectUrl($url);
}
