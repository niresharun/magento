<?php
namespace Ziffity\Checkout\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddContactInfo implements DataPatchInterface
{
    private $moduleDataSetup;
    private $blockFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $blockData = [
            'title' => 'Contact Information',
            'identifier' => 'success-page-contact-info',
            'content' => '<div class="contact-info">
                            <strong>DisplayFrames Customer Service</strong>
                            <p><strong>Phone:</strong> 123-456-7890</p>
                            <p><strong>Email:</strong> contactus@displayframes.com</p>
                        </div>',
            'is_active' => 1,
            'stores' => [0],
        ];

        $block = $this->blockFactory->create();
        $block->setData($blockData);
        $block->save();

        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
