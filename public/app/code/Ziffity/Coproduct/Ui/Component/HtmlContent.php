<?php
namespace Ziffity\Coproduct\Ui\Component;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Ziffity\Coproduct\Model\Product\Type\Coproduct;
use Magento\Catalog\Model\Locator\LocatorInterface;

class HtmlContent extends \Magento\Ui\Component\HtmlContent
{
    /**
     * @param ContextInterface $context
     * @param BlockInterface $block
     * @param LocatorInterface $locator
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        BlockInterface $block,
        private LocatorInterface $locator,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $block, $components, $data);
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        if ($this->locator->getProduct()->getTypeId() === Coproduct::TYPE_CODE) {
            $wrapper = $this->getData('wrapper');
            $wrapper['canShow'] = true;
            $this->setData('wrapper', $wrapper);
        }
        parent::prepare();
    }
}
