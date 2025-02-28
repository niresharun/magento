<?php
namespace Ziffity\CustomFrame\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;

class ProductImportRelatedAttributesFixes implements DataPatchInterface
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
     * @param ResourceConnection $resource
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        private ResourceConnection $resource
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
        $attributeId = $eavSetup->getAttributeId(Product::ENTITY, 'mat_color');
        $eavSetup->addAttributeOption([
            'values' => [ 'Green', 'Orange', 'Tan', 'Teal', 'Purple'],
            'attribute_id' => $attributeId,
        ]);
        $connection  = $this->resource->getConnection();
        $data = ["backend_type" => "text"];
        $frontend_input = "multiselect";
        $backend_type = "int";
        $where = ['frontend_input = ?' => $frontend_input, 'backend_type = ?' => $backend_type];
        $tableName = $connection->getTableName("eav_attribute");
        $connection->update($tableName, $data, $where);
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

