<?php

class Webgriffe_Alternate_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_PATH_WEBGRIFFE_ALTERNATE_INCLUDE_REGION = 'catalog/webgriffe_alternate/include_region';
    const XML_PATH_WEBGRIFFE_ALTERNATE_OVERRIDE_REGION = 'catalog/webgriffe_alternate/override_region';
    const XML_PATH_WEBGRIFFE_ALTERNATE_OVERRIDE_REGION_VALUE = 'catalog/webgriffe_alternate/override_region_value';

    public function isIncludeRegionEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_WEBGRIFFE_ALTERNATE_INCLUDE_REGION, $store);
    }

    public function isOverrideRegionEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_WEBGRIFFE_ALTERNATE_OVERRIDE_REGION, $store);
    }

    public function getOverrideRegionValue($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_WEBGRIFFE_ALTERNATE_OVERRIDE_REGION_VALUE, $store);
    }
}