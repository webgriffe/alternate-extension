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
        /** @var Mage_Catalog_Model_Product|null $product */
        $product = Mage::registry('current_product');

        /** @var Mage_Catalog_Model_Category|null $category */
        $category = Mage::registry('current_category');

        $map = array();

        /** @var Mage_Core_Model_Store $store */
        foreach (Mage::app()->getStores() as $store) {
            if (!$store->getIsActive()) {
                continue;
            }

            if ($product) {
                $rewrittenProductUrl = $this->rewrittenProductUrl(
                    $product->getId(),
                    $category ? $category->getId() : null,
                    $store
                );
                $url = $store->getBaseUrl() . $rewrittenProductUrl;
            } elseif ($category) {
                $url = $store->getBaseUrl() . $this->rewrittenCategoryUrl($category->getId(), $store);
            } else {
                $url = $store->getUrl('', array('_current' => true, '_use_rewrite' => true));
            }

            $hreflangCode = $this->getHreflangCodeFromLocaleCode($store->getId());
            $map[$hreflangCode] = $url;
        }

        return $map;
    }

    /**
     * @param $productId
     * @param $categoryId
     * @param Mage_Core_Model_Store $store
     * @return string
     * @throws \Mage_Core_Model_Store_Exception
     */
    protected function rewrittenProductUrl($productId, $categoryId, Mage_Core_Model_Store $store)
    {
        $coreUrl = Mage::getModel('core/url_rewrite');
        $idPath = sprintf('product/%d', $productId);
        if ($categoryId) {
            $idPath = sprintf('%s/%d', $idPath, $categoryId);
        }
        $coreUrl->setStoreId($store->getId());
        $coreUrl->loadByIdPath($idPath);

        return $this->addStoreSwitchUrl($store, $coreUrl->getRequestPath());
    }

    /**
     * @param $categoryId
     * @param Mage_Core_Model_Store $store
     * @return string
     * @throws \Mage_Core_Model_Store_Exception
     */
    protected function rewrittenCategoryUrl($categoryId, Mage_Core_Model_Store $store)
    {
        $coreUrl = Mage::getModel('core/url_rewrite');
        $idPath = sprintf('category/%d', $categoryId);
        $coreUrl->setStoreId($store->getId());
        $coreUrl->loadByIdPath($idPath);

        return $this->addStoreSwitchUrl($store, $coreUrl->getRequestPath());
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @param $requestPath
     * @return string
     * @throws \Mage_Core_Model_Store_Exception
     */
    protected function addStoreSwitchUrl(Mage_Core_Model_Store $store, $requestPath)
    {
        if (!Mage::getStoreConfigFlag(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL, $store->getCode()) &&
            ((int)Mage::app()->getStore()->getId() !== (int)$store->getId())
        ) {
            $firstQueryChar = '?';
            if (strpos($requestPath, '?') !== false) {
                $firstQueryChar = '&';
            }

            $requestPath .= $firstQueryChar . http_build_query(array('___store' => $store->getCode()));
        }

        return $requestPath;
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
}
