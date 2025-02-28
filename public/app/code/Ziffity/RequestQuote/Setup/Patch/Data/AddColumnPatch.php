<?php
declare(strict_types=1);

namespace Ziffity\RequestQuote\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Amasty\QuoteAttributes\Model\Attribute\Data\Text;
class AddColumnPatch implements DataPatchInterface
{
    private $moduleDataSetup;
    private $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId = 10;

        $attributes = [
            [
                'code' => 'shipping_state',
                'label' => 'Shipping State',
                'input' => 'text',
                'type' => 'varchar',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 3,
                'multiline_count' => 1,
                'store_id' => 1,
                'data_model' => Text::class,
                'frontend_class' => null,
            ],
            [
                'code' => 'shipping_street',
                'label' => 'Shipping Street',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 1,
                'multiline_count' => 1,
                'store_id' => 1,
                'data_model' => Text::class,
                'frontend_class' => null,
            ],
            [
                'code' => 'shipping_city',
                'label' => 'Shipping City',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 2,
                'multiline_count' => 1,
                'store_id' => 1,
                'data_model' => Text::class,
                'frontend_class' => null,
            ],
            [
                'code' => 'shipping_country',
                'label' => 'Shipping Country',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_orders' => 4,
                'multiline_count' => 1,
                'store_id' => 1,
                'data_model' => Text::class,
                'frontend_class' => null,
            ],
            [
                'code' => 'shipping_zipcode',
                'label' => 'Shipping ZipCode',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 5,
                'multiline_count' => 1,
                'store_id' => 1,
                'data_model' => Text::class,
                'frontend_class' => 'validate-digits',
            ],
        ];

        foreach ($attributes as $attributeData) {
            $eavSetup->addAttribute($entityTypeId, $attributeData['code'], $attributeData);
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function getAliases()
    {
        return [];
    }
    public static function getDependencies()
    {
        return [];
    }
}
