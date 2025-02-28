<?php

declare(strict_types=1);

namespace Ziffity\CustomFrame\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\Module\Dir\Reader;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as EavCollection;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\Validator\ValidateException;
use Psr\Log\LoggerInterface;
use Magento\Eav\Model\Entity\Type;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class RepairTemplateAttributes implements DataPatchInterface
{

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AttributeCollectionFactory
     */
    protected $attributeSetCollection;

    /**
     * @var EavSetupFactory
     */
    public $eavSetupFactory;

    /**
     * @var EavCollection
     */
    public $eavCollection;

    /**
     * @var File
     */
    public $file;

    /**
     * @var Csv
     */
    private $csv;

    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleDirReader;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Csv $csv
     * @param Reader $moduleDirReader
     * @param File $file
     * @param EavCollection $eavCollection
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeCollectionFactory $attributeSetCollection
     * @param LoggerInterface $logger
     */
    public function __construct
    (
        ModuleDataSetupInterface $moduleDataSetup,
        Csv $csv, Reader $moduleDirReader,
        File $file, EavCollection $eavCollection,
        EavSetupFactory $eavSetupFactory,
        AttributeCollectionFactory $attributeSetCollection,
        LoggerInterface $logger,
        Type $type
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->csv = $csv;
        $this->moduleDirReader = $moduleDirReader;
        $this->file = $file;
        $this->eavCollection = $eavCollection;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetCollection = $attributeSetCollection;
        $this->logger = $logger;
        $this->type = $type;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $data = [];
        $fileNames = ['attributes.csv','attributes2.csv','attributes3.csv'];
        foreach ($fileNames as $key=>$fileName) {
            $filePath = $this->moduleDirReader->getModuleDir('etc', 'Ziffity_CustomFrame')
                . '/'.$fileName;
            $this->csv->setDelimiter(",");
            $csvData = $this->csv->getData($filePath);
            $data[$key] = $this->rearrangeData($csvData);
        }
        $data = $this->mergeAttributeSetsGroups($data);
        $this->installAttributes($data);
    }

    /**
     * This function merges the attributes sets and groups from the 3 csv files.
     *
     * @param array $data
     * @return array
     */
    public function mergeAttributeSetsGroups($data)
    {
        $result = [];
        foreach ($data as $datum)
        {
            foreach ($datum as $value){
                if (in_array($value['attribute_id'],array_keys($result))){
                    foreach ($result[$value['attribute_id']] as $key=>$item){
                        if ($key == "attribute_set_and_group_names"){
                            $item = implode(",",
                                [$value['attribute_set_and_group_names'],$item]);
                        }
                        $value[$key] = $item;
                    }
                }
                $result[$value['attribute_id']] = $value;
            }
        }
        return $result;
    }

    /**
     * This function only installs those attributes which are not present in the eav_attribute table.
     *
     * @param array $data
     * @return void
     */
    public function installAttributes($data)
    {
        $attributesToUpdate = $this->getAttributesToUpdate();
        foreach ($data as $datum) {
            $attributeAlreadyInstalled = $this->checkIfAttributeInstalled($datum['attribute_code']);
            if (in_array($datum['attribute_code'], $attributesToUpdate) && $attributeAlreadyInstalled) {
                $this->addSelectAttribute($datum, $attributeAlreadyInstalled);
            }
        }
    }

    public function getAttributesToUpdate()
    {
        return [
            "adg_image_banner",
            "backingboard_type",
            "board_color",
            "box_depth",
            "box_dimension",
            "box_thickness",
            "back_of_moulding_width",
            "color_layer",
            "dry_erase_board_material",
            "expedited_shipping",
            "fabric_color",
            "fabric_selections",
            "fabric_style",
            "featured_product",
            "freight_in_factor",
            "glass_type",
            "dimension_1",
            "label_data",
            "labor_factor",
            "laminate_colors",
            "laminate_finish",
            "laminate_type",
            "layer_height",
            "layer_width",
            "letter_board_material",
            "matboard_overlap",
            "material_cost",
            "misc_factor1",
            "misc_factor2",
            "misc_factor3",
            "nickname_sizes",
            "overhead_factor",
            "packaging_factor",
            "shipping_profile",
            "show_qty_alert",
            "size_type",
            "supplier",
            "template_letter_set_colors",
            "template_letter_set_font",
            "template_letter_set_sizes",
            "template_letter_set_type",
            "waste_factor",
            "dimension_2","dimension_2_default","dimension_1_default",
            "depth_type",
            "frame_type","graphic_thickness_interior_depth",
            "template_backer_board_type",
            "template_featured_selections","template_frame_colors",
            "template_frame_material",
            "template_frame_width","template_interior_depths",
            "template_letterboard_colors",
            "template_letterboard_material","template_locking",
            "template_matboard_included",
            "template_menu_orientation","template_menu_pages",
            "template_mounted_graphics",
            "template_multiple_mat_openings","template_orientation",
            "template_outdoor_application",
            "template_product_type","template_sizes",
            "template_total_matboard","template_with_header",
            "template_with_labels","template_with_lights",
            "template_with_shelves","featured_selections",
            "frame_color","frame_featured_selections","frame_finish",
            "frame_height","frame_material",
            "frame_rabbet_depth","frame_shape","frame_style",
            "frame_tone","frame_width",
            "mat_color","mat_height","mat_type",
            "mat_width","nielsen_profile",
            "pattern_mat",
            "width_of_moulding_for_shell"];
    }

    /**
     * This function checks if the attribute is installed in M2 or not.
     *
     * @param string $attributeCode
     * @return bool
     */
    public function checkIfAttributeInstalled($attributeCode)
    {
        $collection = $this->eavCollection->create();
        $collection->addFieldToFilter('attribute_code',$attributeCode)
            ->addFieldToFilter('is_user_defined',1)
            ->getFirstItem();
        if (!empty($collection->getData())) {
            return $collection->getFirstItem()->getAttributeId();
        }
        return false;
    }

    /**
     * This function installs the attribute and add it in attribute sets and groups.
     *
     * @param array $data
     * @return void
     * @throws LocalizedException
     * @throws ValidateException
     * @throws \Exception
     */
    public function addSelectAttribute($data,$attributeId)
    {
        try {
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            //TODO: Property mapper to follow when adding the attribute with in-built methods.
            /*$eavUpdateData = [
                'required' => (int)$data['is_required'],
                'user_defined' => (int)$data['is_user_defined'],
                'filterable' => (int)$data['is_filterable'],
                'is_visible' => (int)$data['is_visible'],
                'searchable' => (int)$data['is_searchable'],
                'comparable' => (int)$data['is_comparable'],
                'is_visible_on_front' => (int)$data['is_visible_on_front'],
                'is_html_allowed_on_front' => (int)$data['is_html_allowed_on_front'],
                'is_unique' => (int)$data['is_unique'],
                'global' => (int)$data['is_global'],
                'used_in_product_listing' => (int)$data['used_in_product_listing'],
                'used_for_promo_rules' => (int)$data['is_used_for_promo_rules'],
                'is_used_for_price_rules' => (int)$data['is_used_for_price_rules'],
                'filterable_in_search' => (int)$data['is_filterable_in_search'],
                'used_for_sort_by' => (int)$data['used_for_sort_by'],
                'visible_in_advanced_search' => (int)$data['is_visible_in_advanced_search']
            ];*/
            $eavUpdateData = [
                'is_required' => (int)$data['is_required'],
                'is_user_defined' => (int)$data['is_user_defined'],
                'is_unique' => (int)$data['is_unique']
            ];
            $catalogUpdateData = [
                'is_filterable' => (int)$data['is_filterable'],
                'is_visible' => (int)$data['is_visible'],
                'is_searchable' => (int)$data['is_searchable'],
                'is_comparable' => (int)$data['is_comparable'],
                'is_visible_on_front' => (int)$data['is_visible_on_front'],
                'is_html_allowed_on_front' => (int)$data['is_html_allowed_on_front'],
                'is_global' => (int)$data['is_global'],
                'used_in_product_listing' => (int)$data['used_in_product_listing'],
                'is_used_for_promo_rules' => (int)$data['is_used_for_promo_rules'],
                'is_used_for_price_rules' => (int)$data['is_used_for_price_rules'],
                'is_filterable_in_search' => (int)$data['is_filterable_in_search'],
                'used_for_sort_by' => (int)$data['used_for_sort_by'],
                'is_visible_in_advanced_search' => (int)$data['is_visible_in_advanced_search']
            ];
            //For updating the eav_attribute table
            $eavSetup->updateAttribute(Product::ENTITY,$attributeId,$eavUpdateData);
            //For updating the catalog_eav_attribute table
            $this->moduleDataSetup->updateTableRow(
                'catalog_eav_attribute',
                'attribute_id',
                $attributeId,
                $catalogUpdateData,
                null,
                'entity_type_id',
                Product::ENTITY
            );
        }catch (LocalizedException $exception){
            $this->logger->error($exception->getMessage());
        } catch (ValidateException $exception){
            $this->logger->error($exception->getMessage());
        } catch (\Exception $exception){
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * This function finds out the attribute set id using attribute set name.
     *
     * @param string $attributeSetName
     * @return int attributeSetId
     */
    public function getAttributeSetId($attributeSetName)
    {
        $entityTypeId = $this->type->loadByCode(\Magento\Catalog\Model\Product::ENTITY)->getId();
        $attributeSetCollection = $this->attributeSetCollection->create()
            ->addFieldToSelect('attribute_set_id')
            ->addFieldToFilter('attribute_set_name', $attributeSetName)
            ->addFieldToFilter('entity_type_id', ['eq' => $entityTypeId])
            ->getFirstItem()
            ->toArray();
        return (int) $attributeSetCollection['attribute_set_id'];
    }

    /**
     * This function will re-arrange the data as in key=>value pairs for every attribute.
     *
     * @param array $data
     * @return array
     */
    public function rearrangeData($data)
    {
        $attributes = [];
        $result = [];
        foreach ($data as $key=>$datum){
            if ($key == 0) {
                $attributes = $datum;
                break;
            }
        }

        foreach ($data as $key=>$datum){
            if ($key!==0) {
                foreach ($attributes as $item=>$attribute) {
                    $result[$key][$attribute] = $datum[$item];
                }
            }
        }

        return $result;
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
        return [
            InstallM1AttributeSets::class,
            InstallM1Attributes::class
        ];
    }
}
