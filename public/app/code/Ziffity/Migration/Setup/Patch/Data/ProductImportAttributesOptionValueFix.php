<?php
declare(strict_types=1);
namespace Ziffity\Migration\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Api\AttributeRepositoryInterface;

class ProductImportAttributesOptionValueFix implements DataPatchInterface
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
     * @param Csv $csv
     * @param Reader $moduleDirReader
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        private Csv $csv,
        private Reader $moduleDirReader,
        private AttributeRepositoryInterface $attributeRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $filePath = $this->moduleDirReader->getModuleDir('Setup', 'Ziffity_Migration')
            . '/attribute_options.csv';

        $this->csv->setDelimiter(",");
        $csvData = $this->csv->getData($filePath);

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach ($csvData as $optionData) {
            $attributeCode = $optionData[0];
            if (strpos($attributeCode, "attribute_code")) continue;
            $attribute = $this->attributeRepository->get($optionData[1], $attributeCode);
            $attributeId =  $attribute->getAttributeId();
            $optionDataArray = explode(" | ", $optionData[2]);
            if ($attributeId && count($optionDataArray)) {
                $eavSetup->addAttributeOption([
                    'values' => $optionDataArray,
                    'attribute_id' => $attributeId,
                ]);
            }
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

