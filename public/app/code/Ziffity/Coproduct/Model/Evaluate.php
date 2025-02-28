<?php

namespace Ziffity\Coproduct\Model;

use Ziffity\CustomFrame\Model\Product\Type;
use Ziffity\CustomFrame\Model\ResourceModel\QuantityClassification\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use function PHPUnit\Framework\throwException;

class Evaluate
{

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    protected $issetEvaluated = false;

    /**
     * @var
     */
    protected $product;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory
    )
    {
        $this->productRepository = $productRepository;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return Product
     */
    protected function getProduct()
    {
        return $this->product;
    }

    protected function setProduct($productId)
    {
        $this->product = $this->productRepository->getById($productId);
    }


    //TODO: Have removed the usage of this function from the
    // evaluateFormula method since this function cannot handle
    // multiple lookups , will remove this function later after testing is completed.
    public function lookupValue($attributeValue,$product)
    {
        $regex = '/lookup\\("[^"]*",\\s\\[[^\\]]*\\]\\)/i';
        if (preg_match_all($regex,$attributeValue,$output)){
            $value = $output[0];
            $range = $this->getQuantityClassification($attributeValue,$product);
            $replacement = str_replace($value[0],$range,$attributeValue);
            $calculation = $this->findAttributeValue($replacement,$product);
            return eval("return $calculation;");
        }
        $pattern = '/lookup\("([^"]+)", "[^"]+"\)/';
        if (preg_match($pattern, $attributeValue, $matches)) {
            $value = $matches[1];
            $range = $this->getQuantityClassification($attributeValue,$product);
            $replacement = str_replace($matches[0],$range,$attributeValue);
            $calculation = $this->findAttributeValue($replacement,$product);
            return eval("return $calculation;");
        }
        return $attributeValue;
    }

    public function evaluateLookupValue($attributeValue,$product,$evaluate = false)
    {
        $pattern = '/lookup\([^)]+\)/';
        $pattern2 = '/\(lookup\("[^"]+", "[^"]+"\)[^()]*\)/';
        $result = $this->multipleLookupEvaluation($pattern2,$attributeValue,$product,$evaluate);
        if ($result == $attributeValue) {
            return $this->multipleLookupEvaluation($pattern, $attributeValue, $product, $evaluate);
        }
        return $result;
    }

    public function multipleLookupEvaluation($pattern,$attributeValue,$product,$evaluate)
    {
        $calculation = [];
        $outputs = [];
        if (preg_match_all($pattern, $attributeValue, $output)) {
            foreach ($output[0] as $value) {
                $outputs[] = $value;
                $range = $this->getQuantityClassification($attributeValue, $product);
                preg_match_all($pattern, $value, $formula);
                $replacement = str_replace($formula[0][0], $range, $value);
                $toEvaluate = $this->findAttributeValue($replacement, $product);
                if (!$evaluate) {
                    $calculation[] = eval("return $toEvaluate;");
                }
                if ($evaluate) {
                    $calculation[] = $toEvaluate;
                }
            }
            return str_replace($outputs, $calculation, $attributeValue);
        }
        return $attributeValue;
    }

    public function findAttributeValue($replacement,$product)
    {
        $regex = '/\\[[A-Za-z_\d]+\\]/i';
        if(preg_match_all($regex,$replacement,$output)){
            $regex2 = '/[A-Za-z_\d]+/i';
            if(isset($output[0][0]) && preg_match_all($regex2,$output[0][0],$finalOutput)) {
                if (isset($finalOutput[0][0])) {
                    $value = $product->getData($this->replacePriceAttribute($finalOutput[0][0]));
                    return str_replace($output[0][0],$value,$replacement);
                }
            }
        }
        return $replacement;
    }

    public function replacePriceAttribute($arg)
    {
        if ($this->issetEvaluated && $arg == 'price'){
            return "evaluated_price";
        }
        return $arg;
    }

    public function findReplaceAttributeValue($replacement,$product)
    {
        if ($replacement) {
            $regex = '/\\[[A-Za-z_\d]+\\]/i';
            if (preg_match_all($regex, $replacement, $output)) {
                return $this->getReplacementAttributes($output, $replacement, $product);
            }
        }
        return $replacement;
    }

    public function getReplacementAttributes($output,$string,$product)
    {
        $attributes = [];
        $find = [];
        foreach ($output[0] as $key=>$item)
        {
            //TODO: Have to find the value using the $product object and store it in an array
            $attributes[$key] = $this->removeSquareBrackets($output[0][$key]);
            $find[$key] = $output[0][$key];
        }
        $replacement = [];
        foreach ($attributes as $key=>$item)
        {
            $replacement[$key] = $product->getData($this->replacePriceAttribute($item));
        }
        return str_replace($find, $replacement, $string);
    }

    public function removeSquareBrackets($string)
    {
        $pattern = "/\[|\]/"; // Matches square brackets
        return preg_replace($pattern, "", $string);
    }

    public function getQuantityClassification($inputString,$product)
    {
//        $pattern = '/\(lookup\("([^"]+)",/';
        $pattern = '/lookup\("([^"]+)",/';
        if (preg_match_all($pattern, $inputString, $output)) {
            return $this->findRangeWithOverallWidth($output[1],$inputString,$product);
        }
        return false;
    }

    public function decodePriceUsingReplacement($productId)
    {
        //TODO: Setting the random product id here
        $this->setProduct($productId);
        $attributeValue = $this->getPriceAttributeValue($productId);
        if ($this->findIfLookupMatches($attributeValue)){
            $lookup = $this->lookupValue($attributeValue,$this->getProduct());
            return $lookup;
        }
        return false;
    }

    public function evaluateFormula($product){
        $result = [];
        //First evaluate the size , price , value
        $sizeAttributeValue = $this->getAttribute($product,'customframe_size');
        if ($this->findIfLookupMatches($sizeAttributeValue)){
            $toEvaluateSize = $this->evaluateLookupValue($sizeAttributeValue,$product,false);
            $result['size'] = eval("return $toEvaluateSize");
        }
        if (!isset($result['size'])) {
            $size = $this->findReplaceAttributeValue($sizeAttributeValue, $product);
            //Evaluate the size result
            $result['size'] = $size!==null ? eval("return $size;") : $size;
            if ($result['size'] === null) {
                $result['size'] = $sizeAttributeValue;
            }
        }
        $product->setSize($result['size']);
        //Evaluate the price result
        $priceAttributeValue = $this->getAttribute($product,'customframe_price');
        $result['price'] = '';
        if ($this->findIfLookupMatches($priceAttributeValue)) {
            $toEvaluatePrice = $this->evaluateLookupValue($priceAttributeValue,$product);
            $result['price'] = eval("return $toEvaluatePrice;");
        }
        $pricePresent = $this->findPriceAttributeCode($priceAttributeValue,$product);
        if (empty($result['price']) && !$pricePresent) {
            $price = $this->findReplaceAttributeValue($priceAttributeValue, $product);
            $result['price'] = $price!==null ? eval("return $price;") : $price;
            if ($result['price'] === null){
                $result['price'] = $priceAttributeValue;
            }
        }

        $pricePresent == true ? $product->setPrice(0) : $this->setEvaluatedPrice($product,$result['price']);
        //Evaluate the value result
        $valueAttribute = $this->getAttribute($product,'customframe_value');
        if ($this->findIfLookupMatchesForValue($valueAttribute)) {
            $result['value'] = $this->evaluateLookupValue($valueAttribute,$product,false);
        }
        if (!isset($result['value'])) {
            $result['value'] = $this->findReplaceAttributeValue($valueAttribute, $product);
            if ($result['value'] === null){
                $result['value'] = $valueAttribute;
            }
        }
        $this->issetEvaluated = false;
        return $result;
    }

    public function setEvaluatedPrice($product,$price)
    {
        $this->issetEvaluated = true;
        $product->setEvaluatedPrice($price);
    }

    /**
     * This function checks if the product type is customframe and checks if
     * price attribute is present using preg_match
     *
     * @param string $value
     * @param Product $product
     * @return bool
     */
    public function findPriceAttributeCode($value, $product)
    {
        $pattern = "/price/";
        if ($product->getTypeId() == Type::TYPE_CODE  &&
            preg_match($pattern,$value)==1){
            return true;
        }
        return false;
    }

    public function findRangeWithOverallWidth($identifier,$attributeValue,$product)
    {
        $decodedClassification = [];
        //TODO: Let us assume the overall width to be 20 , afterwards lets get this from product dynamically.
        $range = $this->findRangeAttribute($attributeValue,$product);
        $model = $this->findModel($identifier);
        if ($model->getData()) {
            $decodedClassification = json_decode($model->getClassification(), true);
        }
        return $this->findQuantityUsingRange($decodedClassification,$range);
    }

    public function findRangeAttribute($attributeValue,$product)
    {
        $pattern = '/lookup\("[^"]*", \[([^]]+)\]\)/';
        if (preg_match($pattern, $attributeValue, $matches)) {
            $attributeCode = $matches[1];
            return $product->getData($this->replacePriceAttribute($attributeCode));
        }
        $pattern2 = '/lookup\("[^"]+", "([^"]+)"\)/';
        if (preg_match($pattern2, $attributeValue, $matches)) {
            $attributeCode = $matches[1];
            return $product->getData($this->replacePriceAttribute($attributeCode));
        }
        return null;
    }

    public function findQuantityUsingRange($data,$overallWidth)
    {
        foreach ($data as $datum)
        {
            $item = ['size_from'=>$datum['size_from'], 'size_to'=> ($datum['size_to']) ? $datum['size_to'] : '99999'];
            $minValue = min($item);
            $maxValue = max($item);
            if ($overallWidth >= $minValue && $overallWidth <= $maxValue){
                return $datum['qty'];
            }
        }
        return 0;
    }

    public function findModel($identifier)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('identifier',$identifier);
        return $collection->getFirstItem();
    }

    public function getPriceAttributeValue($product)
    {
        //TODO: Have to change this to getCustomAttribute
        $product = $this->getProduct();
        $value = $product->getCustomAttribute('customframe_price');
        $value = $value->getValue();
        return $value;
    }

    public function getAttribute($product,$attributeCode)
    {
        return $product->getData($this->replacePriceAttribute($attributeCode));
    }

    public function getSizeAttributeValue($product)
    {
        //TODO: Have to change this to getCustomAttribute
        $product = $this->getProduct();
        $value = $product->getCustomAttribute('customframe_size');
        $value = $value->getValue();
        return $value;
    }

    public function findIfLookupMatches($value)
    {
        if ($value) {
            $regex = '/lookup\\("[^"]*",\\s\\[[^\\]]*\\]\\)/i';
            if (!preg_match($regex, $value) == 1 ? true : false) {
                $regex2 = '/lookup/i';
                return preg_match($regex2, $value) == 1 ? true : false;
            }
            return true;
        }
        return $value;
    }

    public function findIfLookupMatchesForValue($value)
    {
        if ($value) {
            $pattern = '/{[^}]+}/';
            if (!preg_match($pattern, $value) ? true : false) {
                return $this->findIfLookupMatches($value);
            }
            return true;
        }
        return $value;
    }

}
