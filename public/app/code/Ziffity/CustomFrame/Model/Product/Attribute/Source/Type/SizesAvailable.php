<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Magento\Framework\Data\OptionSourceInterface;

class SizesAvailable extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            foreach ($this->sizesData() as $item) {
                $this->_options[] =
                    ['label' => $item['value'], 'value' => $item['value']];
            }
        }
        return $this->_options;
    }

    /**
     * Sizes data in JSON format.
     *
     * @return mixed
     */
    public function sizesData()
    {
        //TODO: Have to implement stores configuration so they can be fetched
        // from the database directly.
        $data = '[
          {
            "value": "16 x 20"
          },
          {
            "value": "11 x 17"
          },
          {
            "value": "12 x 18"
          },
          {
            "value": "11 x 14"
          },
          {
            "value": "8.5 x 11"
          },
          {
            "value": "8 x 10"
          },
          {
            "value": "5 x 7"
          },
          {
            "value": "4 x 6"
          },
          {
            "value": "Create Your Size"
          },
          {
            "value": "48 x 72"
          },
          {
            "value": "36 x 48"
          },
          {
            "value": "22 x 28"
          },
          {
            "value": "8.5 x 14"
          },
          {
            "value": "10 x 12"
          },
          {
            "value": "9 x 12"
          },
          {
            "value": "10 x 20"
          },
          {
            "value": "16 x 16"
          },
          {
            "value": "14 x 22"
          },
          {
            "value": "13 x 19"
          },
          {
            "value": "12 x 36"
          },
          {
            "value": "12 x 24"
          },
          {
            "value": "12 x 20"
          },
          {
            "value": "20 x 30"
          },
          {
            "value": "20 x 28"
          },
          {
            "value": "20 x 24"
          },
          {
            "value": "20 x 20"
          },
          {
            "value": "18 x 30"
          },
          {
            "value": "18 x 24"
          },
          {
            "value": "18 x 18"
          },
          {
            "value": "17 x 23"
          },
          {
            "value": "17 x 22"
          },
          {
            "value": "16 x 24"
          },
          {
            "value": "36 x 42"
          },
          {
            "value": "36 x 36"
          },
          {
            "value": "30 x 40"
          },
          {
            "value": "30 x 36"
          },
          {
            "value": "27 x 41"
          },
          {
            "value": "27 x 40"
          },
          {
            "value": "27 x 39"
          },
          {
            "value": "24 x 48"
          },
          {
            "value": "24 x 36"
          },
          {
            "value": "24 x 30"
          },
          {
            "value": "24 x 24"
          },
          {
            "value": "22 x 34"
          },
          {
            "value": "96 x 96"
          },
          {
            "value": "84 x 96"
          },
          {
            "value": "84 x 84"
          },
          {
            "value": "72 x 96"
          },
          {
            "value": "72 x 84"
          },
          {
            "value": "72 x 72"
          },
          {
            "value": "60 x 96"
          },
          {
            "value": "60 x 84"
          },
          {
            "value": "60 x 72"
          },
          {
            "value": "60 x 60"
          },
          {
            "value": "48 x 96"
          },
          {
            "value": "48 x 84"
          },
          {
            "value": "48 x 60"
          },
          {
            "value": "48 x 48"
          },
          {
            "value": "42 x 42"
          },
          {
            "value": "40 x 60"
          },
          {
            "value": "40 x 50"
          },
          {
            "value": "36 x 96"
          },
          {
            "value": "36 x 84"
          },
          {
            "value": "36 x 72"
          },
          {
            "value": "36 x 60"
          },
          {
            "value": "24 x 96"
          },
          {
            "value": "24 x 84"
          },
          {
            "value": "24 x 72"
          },
          {
            "value": "24 x 60"
          },
          {
            "value": "23 x 35"
          },
          {
            "value": "36 x 12 (Panoramic)"
          },
          {
            "value": "24 x 8 (Panoramic)"
          },
          {
            "value": "15 x 5 (Panoramic)"
          },
          {
            "value": "30 x 30"
          },
          {
            "value": "10 x 13"
          },
          {
            "value": "14 x 14"
          },
          {
            "value": "12 x 16"
          },
          {
            "value": "8 x 8"
          },
          {
            "value": "5 x 5"
          },
          {
            "value": "30 x 96"
          },
          {
            "value": "30 x 84"
          },
          {
            "value": "30 x 72"
          },
          {
            "value": "30 x 60"
          },
          {
            "value": "30 x 48"
          },
          {
            "value": "18 x 96"
          },
          {
            "value": "18 x 84"
          },
          {
            "value": "18 x 72"
          },
          {
            "value": "18 x 60"
          },
          {
            "value": "18 x 48"
          }
        ]';
        return json_decode($data, true);
    }
}
