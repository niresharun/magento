<?php

namespace Ziffity\ProductCustomizer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File as FileWriter;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Registry;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use \Magento\Catalog\Helper\Image;
use Magento\Quote\Api\CartItemRepositoryInterface;

class Data extends AbstractHelper
{

    const  CO_PRODUCTS = "Co-Products";

    /**
     * @var ProductOptionRepositoryInterface
     */
    protected $optionsRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    protected $_floatToFractionalHash = [];

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     *
     * @var Registry
     */
    protected $registry;

    /**
     * @var CartItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var FileWriter
     */
    protected $fileWriter;

    /**
     * Base directory
     *
     * @var string
     */
    protected $baseDir = 'media' . DIRECTORY_SEPARATOR .
    'catalog' . DIRECTORY_SEPARATOR . 'product' . DIRECTORY_SEPARATOR;

    /**
     * @param Context $context
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $registry
     * @param Image $imageHelper
     * @param CartItemRepositoryInterface $itemRepository
     * @param File $file
     * @param FileWriter $fileWriter
     */
    public function __construct(
        Context $context,
        ProductOptionRepositoryInterface $optionsRepository,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $registry,
        Image $imageHelper,
        CartItemRepositoryInterface $itemRepository,
        File $file,
        FileWriter $fileWriter
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->imageHelper = $imageHelper;
        $this->itemRepository = $itemRepository;
        $this->file = $file;
        $this->fileWriter = $fileWriter;
        parent::__construct($context);
    }

    /**
     * To get progress bar data
     *
     * @return array
     */
    public function getOptionGroupItems()
    {
        $options = [];
        $product =  $this->getProduct();
        foreach ($this->optionsRepository->getList($product->getSku()) as $option) {
            if ($option->getTitle() !== self::CO_PRODUCTS) {
                array_push($options, $option->getTitle());
            }
        }
        return $options;
    }

    /**
     * @param $product
     * @param $productData
     * @return string|null
     */
    public function loadProductImage($product, $imageType)
    {
        $img = null;
        if($product) {
            if ($imageType == 'layer') {
                $imgFile = $product->getImgLayer();
            } else {
                $imgFile = $product->getSmallImage();
            }
            // 'product_base_image' or any image code from vendor\magento\theme-frontend-luma\etc\view.xml
            $img = $this->imageHelper->init($product, 'product_small_image')
                ->setImageFile($imgFile)
                ->getUrl();
        }
        return $img;
    }

    /**
     * @param $title
     * @return array|string|string[]
     */
    public function getCode($title)
    {
        $code = '';
        if ($title) {
            $code = strToLower($title);
            $code = str_replace(" ", "_", $code);
        }
        return $code;
    }

     /**
     * Get product
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }


    /**
     * Convert formated fractional value to float.
     *
     * @param string $value Formatted fractional value.
     *
     * @return float
     */
    public function fractionalToFloat($value)
    {
        if (is_string($value)) {
            $value = preg_replace('/\s+/', ' ', $value);
            $value = trim($value);
            $value = explode(' ', $value);
            $value[0] = str_replace('\\', '/', trim($value[0]));
            $fractionalPart = 0;
            if (!empty($value[1])) {
                $value[1] = str_replace('\\', '/', trim($value[1]));
                $fractionalParts = explode('/', $value[1]);
                $fractionalPart = $fractionalParts[1] == 0 ? $fractionalParts[0] : $fractionalParts[0] / $fractionalParts[1];
//                $fractionalPart = $fractionalParts[0] / $fractionalParts[1];
            }

            if (strpos($value[0], '/') !== false) {
                $value[0] = explode('/', $value[0]);
                $result = $value[0][0];
                if ($value[0][1] > 0) {
                    $result = floatval($value[0][0] / $value[0][1]);
                }
                $value[0] = $result;
            }

            $value = floatval($value[0]) + floatval($fractionalPart);
        }

        return $value;
    }


    /**
     * Convert float value to fractional string (e.g. 0.125 => 1/8).
     *
     * @param float $n         Float value.
     * @param float $tolerance Tolerance.
     *
     * @return array
     */
    public function floatToFractional($n, $tolerance = 1.e-6)
    {
        if (!$n) {
            return [
                'decimal'    => 0,
                'fractional' => [
                    'top'     => 0,
                    'bottom'  => 0,
                    'decimal' => 0,
                ],
            ];
        }
        $hashId = $n . $tolerance;
        if (array_key_exists($hashId, $this->_floatToFractionalHash)) {
            return $this->_floatToFractionalHash[$n . $tolerance];
        }

        $decimalPartOne = 1;
        $decimalPartTwo = 0;
        $fractionalPartOne = 0;
        $fractionalPartTwo = 1;
        $b = 1 / $n;
        do {
            $b = 1 / $b;
            $a = floor($b);
            $aux = $decimalPartOne;
            $decimalPartOne = $a * $decimalPartOne + $decimalPartTwo;
            $decimalPartTwo = $aux;
            $aux = $fractionalPartOne;
            $fractionalPartOne = $a * $fractionalPartOne + $fractionalPartTwo;
            $fractionalPartTwo = $aux;
            $b -= $a;
        } while (abs($n - $decimalPartOne / $fractionalPartOne) > $n * $tolerance);

        $converted = [
            'decimal'    => 0,
            'fractional' => [
                'top'     => $decimalPartOne,
                'bottom'  => $fractionalPartOne,
                'decimal' => $decimalPartOne / $fractionalPartOne,
            ],
        ];
        if ($decimalPart = (int) ($decimalPartOne / $fractionalPartOne)) {
            $converted = [
                'decimal'    => $decimalPart,
                'fractional' => [
                    'top'     => 0,
                    'bottom'  => 0,
                    'decimal' => 0,
                ],
            ];
            if ($fractionalPart = $decimalPartOne % $fractionalPartOne) {
                $converted = [
                    'decimal'    => $decimalPart,
                    'fractional' => [
                        'top'     => $fractionalPart,
                        'bottom'  => $fractionalPartOne,
                        'decimal' => $fractionalPart / $fractionalPartOne,
                    ],
                ];
            }
        }
        $this->_floatToFractionalHash[$hashId] = $converted;

        return $converted;
    }

    /**
     * Calculate additional percentrage.
     *
     * @param float $price   Price.
     * @param float $percent Percentage.
     *
     * @return float
     */
    public function calculatePricePercentage($price, $percent)
    {
        return ($price / 100) * $percent;
    }

    /**
     * This function retrieves only the image data from base64 encoded which is
     * further passed through for saving it as a file.
     *
     * @param string $canvasData
     * @return mixed|null
     */
    public function generateImage($canvasData)
    {
        try {
            if ($canvasData) {
                // extract image data from base64 data string
                $pattern = '/data:image\/(.+);base64,(.*)/';
                preg_match($pattern, $canvasData, $matches);
                if (empty($matches)) {
                    list($dataType, $imageData) = explode(';', $canvasData);
                    // image file extension
                    $imageExtension = explode('/', $dataType)[1];
                    // base64-encoded image data
                    list(, $encodedImageData) = explode(',', $imageData);
                    // decode base64-encoded image data
                    $decodedImageData = base64_decode($encodedImageData);
                }
                if (!empty($matches)) {
                    // image file extension
                    $imageExtension = $matches[1];
                    // base64-encoded image data
                    $encodedImageData = $matches[2];
                    // decode base64-encoded image data
                    $decodedImageData = base64_decode($encodedImageData);
                    // save image data as file
                }
                return $this->saveFile($decodedImageData, $imageExtension);
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * This function saves the image data into file , if the filename already exists
     * in the directory then while loop run until unique file name is generated.
     *
     * @param string $decodedImageData
     * @param string $imageExtension
     * @return mixed|null
     * @throws FileSystemException
     */
    public function saveFile($decodedImageData, $imageExtension)
    {
        $file = $this->generateUniqueFileName($imageExtension);
        do {
            if (!$this->fileWriter->isExists($file['path'])) {
                $this->fileWriter->filePutContents($file['path'], $decodedImageData);
                return $file['filename'];
            }
        }while ($this->saveFile($decodedImageData,$imageExtension));
        return null;
    }

    /**
     * This function generates unique file name for the saving the canvas as a file
     *
     * @param string $imageExtension
     * @return array
     */
    public function generateUniqueFileName($imageExtension)
    {
        $this->file->setAllowCreateFolders(true);
        $path = $this->getBaseDir('canvas');
        $this->file->open(['path' => $path]);
        $filename = sprintf('%s.%s', uniqid('canvas' . '_'), $imageExtension);
        return ['path'=>$path . DIRECTORY_SEPARATOR . $filename,'filename'=>$filename];
    }

    /**
     * Retrieve Base Dir.
     *
     * @param string $path
     * @return string
     */
    public function getBaseDir($path)
    {
        return $this->baseDir.$path;
    }

}
