<?php
declare(strict_types=1);

namespace Ziffity\Coproduct\Plugin\Catalog\Controller\Adminhtml\Product\Initialization;

use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\SerializerInterface;
use Ziffity\Coproduct\Model\Product\Type\Coproduct;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper as ProductHelper;
use Magento\Framework\App\RequestInterface;
use Ziffity\Coproduct\Model\RuleFactory;

class ConditionUpdater
{

    /**
     * @param SerializerInterface $serializer
     * @param RequestInterface $request
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        private SerializerInterface $serializer,
        private RequestInterface $request,
        private RuleFactory $ruleFactory
    ) {
    }

    /**
     * Prepare openings data.
     *
     * @param ProductHelper $subject
     * @param Product $product
     * @param array $productData
     * @return []
     */
    public function beforeInitializeFromData(ProductHelper $subject, Product $product, array $productData)
    {
        $productTypeId = $this->request->getParam('type');
        $ruleData = $this->request->getParam('rule');

        if (($product->getTypeId() == Coproduct::TYPE_CODE || $productTypeId == Coproduct::TYPE_CODE) && isset($ruleData['conditions'])) {
            $rule = $this->ruleFactory->create();
            $rule->loadPost($ruleData);
            $conditionsSerialized = $this->serializer->serialize($rule->getConditions()->asArray());
            $productData['conditions'] = $conditionsSerialized;
        }
        return [$product, $productData];
    }
}
