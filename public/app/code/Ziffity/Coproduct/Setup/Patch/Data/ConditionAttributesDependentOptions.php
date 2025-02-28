<?php
namespace Ziffity\Coproduct\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Catalog\Model\Product;

class ConditionAttributesDependentOptions implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * CreateOpeningAttributes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        private ObjectManagerInterface $objectManager
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributeCodes = [
            'glass_type' => 'Ziffity\CustomFrame\Model\Product\Attribute\Source\Type\GlassType',
            'backingboard_type' => 'Ziffity\CustomFrame\Model\Product\Attribute\Source\Type\BackingBoardType',
            'supplier' => 'Ziffity\CustomFrame\Model\Product\Attribute\Source\Type\Supplier'
        ];

        foreach ($attributeCodes as $attributeCode => $source) {
            $attributeId = $eavSetup->getAttributeId(Product::ENTITY, $attributeCode);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $optionsInstance = $objectManager->get($source);
            $eavSetup->addAttributeOption([
                'values' => $optionsInstance->getOptionsValue(),
                'attribute_id' => $attributeId,
            ]);
            $eavSetup->updateAttribute(Product::ENTITY, $attributeCode, 'source_model', null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            \Ziffity\CustomFrame\Setup\Patch\Data\InstallM1Attributes::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}

