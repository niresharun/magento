<?php

namespace Ziffity\ProductCustomizer\Plugin;

use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Framework\App\Action\Action;
use Magento\Framework\DataObject;
use Ziffity\SavedDesigns\Helper\Data as SavedDesignsHelper;

class RedirectToOriginalProduct
{

    /**
     * @var SavedDesignsHelper
     */
    protected $savedDesignHelper;

    /**
     * @param SavedDesignsHelper $savedDesignHelper
     */
    public function __construct(SavedDesignsHelper $savedDesignHelper){
        $this->savedDesignHelper = $savedDesignHelper;
    }

    /**
     * This plugin checks if the share_code in the URL is available in the table or
     * else redirects to 404 not found page.
     *
     * @param Product $subject
     * @param bool|ModelProduct $result
     * @param int $productId
     * @param Action $controller
     * @param DataObject|null $params
     * @return bool|ModelProduct
     */
    public function afterInitProduct(Product $subject, bool|ModelProduct $result,
     $productId, $controller, $params = null): bool|ModelProduct
    {
        if ($result){
            $path = $controller->getRequest()->getParam('selection');
            if ($path == 'saved_designs'){
                if ($this->savedDesignHelper->findAdditionalData($controller
                    ->getRequest()->getParam('share_code')) === null){
                    return false;
                }
            }
        }
        return $result;
    }
}
