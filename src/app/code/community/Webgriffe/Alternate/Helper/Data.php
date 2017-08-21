<?php

class Webgriffe_Alternate_Helper_Data extends Mage_Core_Helper_Abstract
{
    const SWITCH_STORE_PARAM_CODE = '___store';

    /**
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getCurrentUrlForStore(Mage_Core_Model_Store $store)
    {
        /** @var Mage_Catalog_Model_Product|null $product */
        $product = Mage::registry('current_product');

        $categoryId = Mage::app()->getRequest()->getParam('category');
        /** @var Mage_Catalog_Model_Category|null $category */
        $category = Mage::getModel('catalog/category')->load($categoryId);

        if ($product) {
            $rewrittenProductUrl = $this->rewrittenProductUrl(
                $product->getId(),
                $category ? $category->getId() : null,
                $store
            );
            $url = $store->getBaseUrl() . $rewrittenProductUrl;
            return $url;
        } elseif ($category) {
            $url = $store->getBaseUrl() . $this->rewrittenCategoryUrl($category->getId(), $store);
            return $url;
        } else {
            $url = $store->getUrl(
                '*/*/*',
                array('_use_rewrite' => true, '_forced_secure' => true)
            );
            return $url;
        }
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

        $requestPath = $this->addStoreSwitchUrl($store, $coreUrl->getRequestPath());
        $requestPath = $this->addQueryParams($requestPath);
        return $requestPath;
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
            $requestPath .= $this->getFirstQueryChar($requestPath) . http_build_query(
                    array(self::SWITCH_STORE_PARAM_CODE => $store->getCode())
                );
        }

        return $requestPath;
    }

    /**
     * @param string $requestPath
     * @return string
     */
    protected function addQueryParams($requestPath)
    {
        $requestQuery = Mage::app()->getRequest()->getQuery();
        unset($requestQuery[self::SWITCH_STORE_PARAM_CODE]); // this is handled by addStoreSwitchUrl
        if (!empty($requestQuery)) {
            $requestPath .= $this->getFirstQueryChar($requestPath) . http_build_query($requestQuery);
        }
        return $requestPath;
    }

    /**
     * @param string $requestPath
     * @return string
     */
    protected function getFirstQueryChar($requestPath)
    {
        return strpos($requestPath, '?') !== false ? '&' : '?';
    }
}
