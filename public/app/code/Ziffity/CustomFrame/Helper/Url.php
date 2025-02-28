<?php

namespace Ziffity\CustomFrame\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Url extends \Ziffity\CustomFrame\Helper\Image
{

//    /**
//     * Checks if current page secured
//     *
//     * @return boolean
//     */
//    public function isCurrentlySecured()
//    {
//        if ($this->_secured === null) {
//            $this->_secured = Mage::app()->getStore()->isCurrentlySecure();
//        }
//
//        return $this->_secured;
//    }

    /**
     * Get current product
     *
     * @return \Magento\Catalog\Model\Product|mixed
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Product Get Data Json  Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getProductDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/getProductData',
            ['product_id' => $productId]
        );
    }

    /**
     * Product Get Data Json  Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getSelectionHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/getSelections',
            ['id' => $productId]
        );
    }

    /**
     * Product Get Data Json  Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getPriceHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/getPriceData',
            ['id' => $productId]
        );
    }

    /**
     * Product Get Default Data Json  Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getProductDefaultDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/getProductDefaultData',
            ['product_id' => $productId]
        );
    }

    /**
     * Product Data Json Save Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getProductDataSaveUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/saveProductJson',
            ['product_id' => $productId]
        );
    }

    /**
     * Product Data Json Save Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getProductDataSaveNoValidateUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/saveProductJson',
            ['product_id' => $productId, 'validation' => 0]
        );
    }

    /**
     * Product Details Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getDetailsHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/getDetailsHtml',
            ['id' => $productId]
        );
    }

    /**
     * Size Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getSizeHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl('customizer/tabs/sizeHtml', ['id' => $productId]);
    }

    /**
     * Size Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getSizeDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/sizeData',
            ['id' => $productId]
        );
    }

    /**
     * Frame Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getFrameHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/frameHtml',
            ['id' => $productId]
        );
    }

    /**
     * Frame Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getFrameDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/layer',
            ['relation' => 'frame', 'product_id' => $productId]
        );
    }

    /**
     * Frame Details Popup Url
     *
     * @param integer $productId Product id.
     * @param integer $parent    Parent Product id.
     *
     * @return string
     */
    public function getFramePopupUrl($productId, $parent = null)
    {
        if (!$parent) {
            $parentProduct = $this->getCurrentProduct();
            if ($parentProduct->getId() !== $productId) {
                $parent = $this->getCurrentProduct()->getId();
            }
        }
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/framePopup',
            ['id' => $productId, 'parent' => $parent]
        );
    }

    /**
     * Fabric Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getFabricHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/fabricHtml',
            ['id' => $productId]
        );
    }

    /**
     * Fabric Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getFabricDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/layer',
            ['relation' => 'fabric', 'product_id' => $productId]
        );
    }

    /**
     * Frame Details Popup Url
     *
     * @param integer $productId Product id.
     * @param integer $parent    Parent Product id.
     *
     * @return string
     */
    protected function getFabricPopupUrl($productId, $parent = null)
    {
        if (!$parent) {
            $parentProduct = $this->getCurrentProduct();
            if ($parentProduct->getId() !== $productId) {
                $parent = $this->getCurrentProduct()->getId();
            }
        }
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/fabricPopup',
            ['id' => $productId, 'parent' => $parent]
        );
    }

    /**
     * Dryeraseboard Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    protected function getDryeraseboardHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/dryeraseboardHtml',
            ['id' => $productId]
        );
    }

    /**
     * Dryeraseboard Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    protected function getDryeraseboardDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/layer',
            ['relation' => 'dryeraseboard', 'product_id' => $productId]
        );
    }

    /**
     * Dryeraseboard Details Popup Url
     *
     * @param integer $productId Product id.
     * @param integer $parent    Parent Product id.
     *
     * @return string
     */
    protected function getDryeraseboardPopupUrl($productId, $parent = null)
    {
        if (!$parent) {
            $parentProduct = $this->getCurrentProduct();
            if ($parentProduct->getId() !== $productId) {
                $parent = $this->getCurrentProduct()->getId();
            }
        }
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/dryeraseboardPopup',
            ['id' => $productId, 'parent' => $parent]
        );
    }

    /**
     * Chalk Board Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    protected function getChalkboardsHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/chalkboardsHtml',
            ['id' => $productId]
        );
    }

    /**
     * Chalk Board Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    protected function getChalkboardsDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/layer',
            ['relation' => 'chalkboards', 'product_id' => $productId]
        );
    }

    /**
     * Chalk Board Details Popup Url
     *
     * @param integer $productId Product id.
     * @param integer $parent    Parent Product id.
     *
     * @return string
     */
    protected function getChalkboardsPopupUrl($productId, $parent = null)
    {
        if (!$parent) {
            $parentProduct = $this->getCurrentProduct();
            if ($parentProduct->getId() !== $productId) {
                $parent = $this->getCurrentProduct()->getId();
            }
        }
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/chalkboardsPopup',
            ['id' => $productId, 'parent' => $parent]
        );
    }

    /**
     * Mat html url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getMatHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/matHtml',
            ['id' => $productId]
        );
    }

    /**
     * Mat sizes url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getMatSizeslUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/matSizes',
            ['id' => $productId]
        );
    }

    /**
     * Mat html url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    protected function getMatTopHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/matTopHtml',
            ['id' => $productId]
        );
    }

    /**
     * Mat html url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    protected function getMatBottomHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/matBottomHtml',
            ['id' => $productId]
        );
    }

    /**
     * Mat html url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    protected function getMatMiddleHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/matMiddleHtml',
            ['id' => $productId]
        );
    }

    /**
     * Mat Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    protected function getMatTopDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/layer',
            ['relation' => 'top_mat', 'product_id' => $productId]
        );
    }

    /**
     * Mat Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    protected function getMatBottomDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/layer',
            ['relation' => 'bottom_mat', 'product_id' => $productId]
        );
    }

    /**
     * Mat Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    protected function getMatMiddleDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/layer',
            ['relation' => 'middle_mat', 'product_id' => $productId]
        );
    }

    /**
     * Mat Details Popup Url
     *
     * @param integer $productId Product id.
     * @param integer $parent    Parent Product id.
     *
     * @return string
     */
    protected function getMatPopupUrl($productId, $parent = null)
    {
        if (!$parent) {
            $parentProduct = $this->getCurrentProduct();
            if ($parentProduct->getId() !== $productId) {
                $parent = $this->getCurrentProduct()->getId();
            }
        }

        return $this->_urlBuilder->getUrl(
            'customizer/tabs/matPopup',
            ['id' => $productId, 'parent' => $parent]
        );
    }

    /**
     * Mat List Details Popup Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getMatListPopupUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/matListPopup',
            ['id' => $productId]
        );
    }

    /**
     * Glass Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    protected function getGlassHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/glassHtml',
            ['id' => $productId]
        );
    }

    /**
     * Glass Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    protected function getGlassDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/layer',
            ['relation' => 'glass', 'product_id' => $productId, 'nav' => 0]
        );
    }

    /**
     * Glass Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    protected function getAccessoriesHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/accessoriesHtml',
            ['id' => $productId]
        );
    }

    /**
     * Glass Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    protected function getAccessoriesDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/layer',
            [
            //                '_secure'    => $this->isCurrentlySecured(),
                'relation'   => 'accessories',
                'product_id' => $productId,
                'nav'        => 0,
            ]
        );
    }

    /**
     * PostFinish Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getPostFinishHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/postfinishHtml',
            ['id' => $productId]
        );
    }

    /**
     * PostFinish Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getPostFinishDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/layer',
            [
            //                '_secure'    => $this->isCurrentlySecured(),
                'relation'   => 'postfinish',
                'product_id' => $productId,
                'nav'        => 0,
            ]
        );
    }

    /**
     * BackingBoard Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getBackingBoardHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/backingboardHtml',
            ['id' => $productId]
        );
    }

    /**
     * BackingBoard Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getBackingBoardDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/layer',
            [
            //                '_secure'    => $this->isCurrentlySecured(),
                'relation'   => 'backingboard',
                'product_id' => $productId,
                'nav'        => 0,
            ]
        );
    }

    /**
     * Lighting Tab Html Url
     *
     * @param integer $productId Product id.
     * @param array   $variables HTML vars.
     *
     * @return string
     */
    public function getLightingHtmlUrl($productId, array $variables = [])
    {
        $vars = ['id' => $productId];

        if (!empty($variables)) {
            $vars = array_merge($vars, $variables);
        }

        return $this->_urlBuilder->getUrl(
            'customizer/tabs/lightingHtml',
            $vars
        );
    }

    /**
     * Shelves Tab Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getShelvesHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/shelvesHtml',
            ['id' => $productId]
        );
    }

    /**
     * Add-Ons Tab Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getAddonsHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/addonsHtml',
            ['id' => $productId]
        );
    }

    /**
     * Add-Ons Tab Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getRestoreUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/restore',
            ['product_id' => $productId]
        );
    }

    /**
     * Exterior Finish Tab Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getExteriorFinishHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/exteriorFinishHtml',
            ['id' => $productId]
        );
    }

    /**
     * Exterior Finish Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getExteriorFinishDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/layer',
            ['relation' => 'laminate_exterior', 'product_id' => $productId]
        );
    }

    /**
     * Interior Finish Tab Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getInteriorFinishHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/interiorFinishHtml',
            ['id' => $productId]
        );
    }

    /**
     * Exterior Finish Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getInteriorFinishDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/layer',
            ['relation' => 'laminate_interior', 'product_id' => $productId]
        );
    }

    /**
     * Interior Finish Tab Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getLaminateFinishHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/laminateFinishHtml',
            ['id' => $productId]
        );
    }

    /**
     * Interior Finish Tab Html Url
     *
     * @param integer $productId Product id.
     * @param integer $parent    Parent Product id.
     *
     * @return string
     */
    public function getLaminatePopupUrl($productId, $parent = null)
    {
        if (!$parent) {
            $parentProduct = $this->getCurrentProduct();
            if ($parentProduct->getId() !== $productId) {
                $parent = $this->getCurrentProduct()->getId();
            }
        }
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/laminatePopupHtml',
            ['id' => $productId, 'parent' => $parent]
        );
    }

    /**
     * Letter Board Tab Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getLetterboardHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/letterboardHtml',
            ['id' => $productId]
        );
    }

    /**
     * Letter Board Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getLetterboardDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/layer',
            ['relation' => 'letterboard', 'product_id' => $productId]
        );
    }

    /**
     * Letter Board Tab Html Url
     *
     * @param integer $productId Product id.
     * @param integer $parent    Parent Product id.
     *
     * @return string
     */
    public function getLetterboardPopupHtmlUrl($productId, $parent = null)
    {
        if (!$parent) {
            $parentProduct = $this->getCurrentProduct();
            if ($parentProduct->getId() !== $productId) {
                $parent = $this->getCurrentProduct()->getId();
            }
        }
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/letterboardPopupHtml',
            ['id' => $productId, 'parent' => $parent]
        );
    }

    /**
     * Labels Data URL.
     *
     * @param int $productId Product Id.
     *
     * @return string
     */
    public function getLabelsDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/labelsData',
            ['id' => $productId]
        );
    }

    /**
     * Labels html url.
     *
     * @param int $productId Product Id.
     *
     * @return string
     */
    public function getLabelsHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/labelsHtml',
            ['id' => $productId]
        );
    }

    /**
     * Labels Data URL.
     *
     * @param int $productId Product Id.
     *
     * @return string
     */
    public function getHeadersDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/headersData',
            ['id' => $productId]
        );
    }

    /**
     * Labels html url.
     *
     * @param int $productId Product Id.
     *
     * @return string
     */
    public function getHeadersHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/headersHtml',
            ['id' => $productId]
        );
    }

    /**
     * Corkboards Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getCorkboardsHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/corkboardsHtml',
            ['id' => $productId]
        );
    }

    /**
     * Corkboards Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getCorkboardsDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/layer',
            ['relation' => 'corkboards', 'product_id' => $productId]
        );
    }

    /**
     * Frame Details Popup Url
     *
     * @param integer $productId Product id.
     * @param integer $parent    Parent Product id.
     *
     * @return string
     */
    public function getCorkboardsPopupUrl($productId, $parent = null)
    {
        if (!$parent) {
            $parentProduct = $this->getCurrentProduct();
            if ($parentProduct->getId() !== $productId) {
                $parent = $this->getCurrentProduct()->getId();
            }
        }
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/corkboardsPopup',
            ['id' => $productId, 'parent' => $parent]
        );
    }
}
