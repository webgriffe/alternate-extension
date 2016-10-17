<?php

class Webgriffe_Alternate_Model_Observer
{
    public function addAlternateLinksToHeadBlock(Varien_Event_Observer $event)
    {
        /** @var Mage_Core_Model_Layout $layout */
        $layout = $event->getData('layout');

        /** @var Mage_Page_Block_Html_Head $headBlock */
        $headBlock = $layout->getBlock('head');

        if (!$headBlock) {
            return;
        }

        foreach ($this->getAlternateUrlsMap() as $localeCode => $url) {
            $headBlock->addLinkRel('alternate" hreflang="' . $localeCode, $url);
        }
    }

    /**
     * @return array
     * @throws Mage_Core_Exception
     */
    protected function getAlternateUrlsMap()
    {
        $map = array();

        /** @var Mage_Core_Model_Store $store */
        foreach (Mage::app()->getStores() as $store) {
            if (!$store->getIsActive()) {
                continue;
            }

            $url = $this->getCurrentUrlForStore($store);

            $hreflangCode = $this->getHreflangCodeFromLocaleCode($store->getId());
            $map[$hreflangCode] = $url;
        }

        return $map;
    }

    /**
     * @param $storeId
     * @return string
     */
    protected function getHreflangCodeFromLocaleCode($storeId)
    {
        $helper = Mage::helper('webgriffe_alternate/config');
        $localeCode = str_replace('_', '-', Mage::getStoreConfig('general/locale/code', $storeId));
        $languageCode = substr($localeCode, 0, strpos($localeCode, '-'));

        if ($helper->isIncludeRegionEnabled($storeId) === false) {
            return $languageCode;
        }

        $overrideRegion = $helper->getOverrideRegionValue($storeId);
        if ($helper->isOverrideRegionEnabled($storeId) && !empty($overrideRegion)) {
            return $languageCode . '-' . strtoupper($overrideRegion);
        }

        return $localeCode;
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    protected function getCurrentUrlForStore(Mage_Core_Model_Store $store)
    {
        return Mage::helper('webgriffe_alternate')->getCurrentUrlForStore($store);
    }
}
