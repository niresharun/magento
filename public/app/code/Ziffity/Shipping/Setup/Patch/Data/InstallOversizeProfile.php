<?php

declare(strict_types=1);

namespace Ziffity\Shipping\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Ziffity\Shipping\Model\Product\Attribute\Source\OversizeProfile;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class InstallOversizeProfile implements DataPatchInterface
{

    /**
     * @var EavSetupFactory
     */
    private $eavSetup;

    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup, EavSetupFactory $eavSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetup = $eavSetup;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $eavSetup = $this->eavSetup->create(['setup'=>$this->moduleDataSetup]);
        $eavSetup->addAttribute('catalog_product', 'oversize_profile', [
            'input'              => 'select',
            'group'=>'general',
            'type'               => 'int',
            'label'              => 'Oversize Profile',
            'visible'            => true,
            'required'           => false,
            'user_defined'               => false,
            'searchable'                 => false,
            'filterable'                 => false,
            'comparable'                 => false,
            'visible_on_front'           => false,
            'visible_in_advanced_search' => false,
            'is_html_allowed_on_front'   => false,
            'used_for_promo_rules'       => false,
            'source'                     => OversizeProfile::class,
            'frontend_class'             => '',
            'global'                     =>  ScopedAttributeInterface::SCOPE_GLOBAL,
            'unique'                     => false
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
