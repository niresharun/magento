<?php
declare(strict_types=1);

namespace Ziffity\ProductStamp\Block\Adminhtml;

class ImageButton extends \Magento\Backend\Block\Template
{
    protected $_template = 'Ziffity_ProductStamp::config/array_serialize/swatch_image.phtml';

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\View\Asset\Repository $assetRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        array $data = []
    ) {
        $this->assetRepository = $assetRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\View\Asset\Repository
     */
    public function getAssertRepository(): \Magento\Framework\View\Asset\Repository
    {
        return $this->assetRepository;
    }
}
