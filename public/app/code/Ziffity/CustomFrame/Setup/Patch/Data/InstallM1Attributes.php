<?php

declare(strict_types=1);

namespace Ziffity\CustomFrame\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\Module\Dir\Reader;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as EavCollection;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validator\ValidateException;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class InstallM1Attributes implements DataPatchInterface
{

    protected $objectManager;

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
     */
    public function __construct
    (
        ModuleDataSetupInterface $moduleDataSetup,
        Csv $csv, Reader $moduleDirReader,
        File $file, EavCollection $eavCollection,
        EavSetupFactory $eavSetupFactory,
        AttributeCollectionFactory $attributeSetCollection,
        ObjectManagerInterface $objectManager
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->csv = $csv;
        $this->moduleDirReader = $moduleDirReader;
        $this->file = $file;
        $this->eavCollection = $eavCollection;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetCollection = $attributeSetCollection;
        $this->objectManager = $objectManager;
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
     * @return array
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function installAttributes($data)
    {
        foreach ($data as $datum) {
            $attributeAlreadyInstalled = $this->checkIfAttributeInstalled($datum['attribute_code']);
            if (!$attributeAlreadyInstalled) {
                $result = $this->convertNullToEmptyValues($datum);
                if ($result) {
                    //TODO: Have to change the name
                    $this->addSelectAttribute($result);
                }
            }
        }
    }

    //TODO: Have to remove this function in later future since new function can
    //TODO: cause any issues because of this reason keeping this as failsafe.
    /**
     * This function installs the attribute and add it in attribute sets and groups.
     *
     * @param array $data
     * @return void
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function addAttribute($data)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(Product::ENTITY,
            $data['attribute_code'],[
                'type'=> $data['backend_type'],
                'label'=> $data['frontend_label'],
                'input'=> $data['frontend_input'],
                'required'=> $data['is_required'],
                'user_defined'=> $data['is_user_defined'],
                'filterable'=> $data['is_filterable'],
                'is_visible'=> $data['is_visible'],
                'searchable'=> $data['is_searchable'],
                'comparable'=> $data['is_comparable'],
                'is_visible_on_front'=> $data['is_visible_on_front'],
                'is_html_allowed_on_front'=> $data['is_html_allowed_on_front'],
                'is_unique'=> $data['is_unique'],
                'global'=> $data['is_global'],
                'used_in_product_listing'=> $data['used_in_product_listing'],
                'used_for_promo_rules'=> $data['is_used_for_promo_rules'],
                'is_used_for_price_rules'=> $data['is_used_for_price_rules'],
                'filterable_in_search'=> $data['is_filterable_in_search'],
                'used_for_sort_by'=> $data['used_for_sort_by'],
                'apply_to'=> $data['apply_to'],
                'visible_in_advanced_search'=> $data['is_visible_in_advanced_search'],
                'position'=> $data['position'],
                'backend'=> $data['backend_model'],
                'frontend'=> $data['frontend_model'],
                'note' => $data['note'],
                'default_value' => $data['default_value'],
                'frontend_input_renderer' => $data['frontend_input_renderer'],
                'search_weight' => $data['search_weight'],
            ]);
        // get default attribute set id
        $this->explodeAttributeSetAndGroup($data);
    }

    /**
     * This function assigns the attribute sets and groups to the attribute.
     *
     * @param array $data
     * @param string $attributeSetId
     * @param string $attributeGroupName
     * @return void
     */
    public function assignAttributeSetGroup($data, $attributeSetId, $attributeGroupName)
    {
        //your custom attribute group/tab
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attrGroupId = $eavSetup->getAttributeGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            $attributeGroupName
        );
        if (!$attrGroupId) {
            $eavSetup->addAttributeGroup(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeSetId,
                $attributeGroupName // attribute group name
            );
        }
        //add attribute to group
        $eavSetup->addAttributeToGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            $attributeGroupName, // attribute group
            $data['attribute_code'] // attribute code
        );
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
        ->getFirstItem();
        if (!empty($collection->getData())) {
            return true;
        }
        return false;
    }

    /**
     * This function converts the NULL data received from CSV sheet to empty
     * string and skips the attribute containing giftcard as product type.
     *
     * @param array $data
     * @return array
     */
    public function convertNullToEmptyValues($data)
    {
        $result = [];
        foreach ($data as $key=>$value){
            if ($key == "apply_to" && strpos($value,"giftcard") !== false){
                if (strpos($value,",") !== false){
                    $applyTo = explode(",",$value);
                    $applyToProduct = [];
                    foreach ($applyTo as $item){
                        if ($item!=="giftcard") {
                            $applyToProduct[] = $item;
                        }
                    }
                    $value = implode(",",$applyToProduct);
                }
                if ($value == "giftcard") {
                    $result = false;
                    break;
                }
            }
            $result[$key] = $value == "NULL" ? '' : $value;
        }
        return $result;
    }

    public function processValues($data,$attribute)
    {
        $result = [];
        foreach ($data as $value) {
            try {
                if (isset($value['label'])) {
                    if ($value['value'] !== ''){
                        $result['values'][strval($value['value'])] = $value['label'];
                    }
                }
            } catch (\Exception $exception){
                throw new \Exception($exception->getMessage());
            }
        }
        return $result;
    }

    /**
     * This function installs the attribute and add it in attribute sets and groups.
     *
     * @param array $data
     * @return void
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function addSelectAttribute($data)
    {
        //TODO: Have to remove the catch statement in near future , for error
        //TODO: debugging and pring the error messages it is being used.
        try {
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $attributeData = [
                'type' => $data['backend_type'],
                'label' => $data['frontend_label'],
                'input' => $data['frontend_input'],
                'required' => $data['is_required'],
                'user_defined' => $data['is_user_defined'],
                'filterable' => $data['is_filterable'],
                'is_visible' => $data['is_visible'],
                'searchable' => $data['is_searchable'],
                'comparable' => $data['is_comparable'],
                'is_visible_on_front' => $data['is_visible_on_front'],
                'is_html_allowed_on_front' => $data['is_html_allowed_on_front'],
                'is_unique' => $data['is_unique'],
                'global' => $data['is_global'],
                'used_in_product_listing' => $data['used_in_product_listing'],
                'used_for_promo_rules' => $data['is_used_for_promo_rules'],
                'is_used_for_price_rules' => $data['is_used_for_price_rules'],
                'filterable_in_search' => $data['is_filterable_in_search'],
                'used_for_sort_by' => $data['used_for_sort_by'],
                'apply_to' => $data['apply_to'],
                'visible_in_advanced_search' => $data['is_visible_in_advanced_search'],
                'position' => $data['position'],
                'source' => $data['source_model'],
                'backend' => $data['backend_model'],
                'frontend' => $data['frontend_model'],
                'note' => $data['note'],
                'default_value' => $data['default_value'],
                'frontend_input_renderer' => $data['frontend_input_renderer'],
                'search_weight' => $data['search_weight']
            ];
            if(($data['frontend_input'] == 'select' || $data['frontend_input'] == 'multiselect') &&
                $data['is_filterable'] == "1" && $data['source_model'] !== "NULL"
                && class_exists($data['source_model'])) {
                $optionsInstance = $this->objectManager->get($data['source_model']);
                $attributeData['option'] = $this->processValues($optionsInstance->getAllOptions(), $data);
                $attributeData['source'] = null;
            }
            $eavSetup->addAttribute(Product::ENTITY,$data['attribute_code'],$attributeData);
            // get default attribute set id
            $this->explodeAttributeSetAndGroup($data);
        }catch (LocalizedException $exception){
            echo "Found error with this attribute";
            echo $data['attribute_code'];
            throw new LocalizedException(__($exception->getMessage()));
        } catch (ValidateException $exception){
            echo "Found error with this attribute";
            echo $data['attribute_code'];
            throw new ValidateException($exception->getMessage());
        } catch (\Exception $exception){
            echo print_r($data);
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * This function converts the value from the array to another array.
     *
     * @param array $data
     * @return void
     */
    public function explodeAttributeSetAndGroup($data)
    {
        if (!is_array($data['attribute_set_and_group_names']) && strpos($data['attribute_set_and_group_names'],',')==false){
            $data['attribute_set_and_group_names'] = [$data['attribute_set_and_group_names']];
        }
        if (!is_array($data['attribute_set_and_group_names']) && strpos($data['attribute_set_and_group_names'],',')){
            $data['attribute_set_and_group_names'] = explode(",",$data['attribute_set_and_group_names']);
        }
        if (is_array($data['attribute_set_and_group_names'])) {
            foreach ($data['attribute_set_and_group_names'] as $attribute_set_name) {
                $explodedData = explode("|",$attribute_set_name);
                $attributeSetId = $this->getAttributeSetId($explodedData[0]);
                $this->assignAttributeSetGroup($data,$attributeSetId,$explodedData[1]);
            }
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
        $attributeSetCollection = $this->attributeSetCollection->create()
            ->addFieldToSelect('attribute_set_id')
            ->addFieldToFilter('attribute_set_name', $attributeSetName)
            ->addFieldToFilter('entity_type_id',"4")
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
        return [InstallM1AttributeSets::class];
    }
}
