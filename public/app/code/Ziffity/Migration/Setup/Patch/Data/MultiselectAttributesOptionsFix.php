<?php
namespace Ziffity\Migration\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Catalog\Model\Product;

class MultiselectAttributesOptionsFix implements DataPatchInterface
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
            'dimension_1' => 'Ziffity\CustomFrame\Model\Product\Attribute\Source\Type\CommonDimensions',
            'dimension_2' => 'Ziffity\CustomFrame\Model\Product\Attribute\Source\Type\CommonDimensions',
            'graphic_thickness_interior_depth' => 'Ziffity\CustomFrame\Model\Product\Attribute\Source\Type\Fractional',
            'additional_tabs' => 'Ziffity\CustomFrame\Model\Product\Attribute\Source\Type\AdditionalTabs'
        ];
        foreach ($attributeCodes as $attributeCode => $source) {
            $attributeId = $eavSetup->getAttributeId(Product::ENTITY, $attributeCode);

            $optionsInstance = $this->objectManager->get($source);
            $optionSource = $optionsInstance->getAllOptions();
            if ($attributeCode == "additional_tabs") {
                $optionSourceLabels = array_column($optionSource, 'value');
            } else {
                $optionSourceLabels = array_column($optionSource, 'label');
            }
            $optionSourceLabels = array_map('strval', $optionSourceLabels);

            $eavSetup->addAttributeOption([
                'values' => $optionSourceLabels,
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

