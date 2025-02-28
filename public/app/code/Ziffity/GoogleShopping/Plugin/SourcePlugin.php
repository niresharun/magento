<?php

namespace Ziffity\GoogleShopping\Plugin;

use Magmodules\GoogleShopping\Helper\Source;

class SourcePlugin
{
    /**
     * @param Source $subject
     * @param array $result
     * @return array
     */
    public function afterGetProductFilters(Source $subject, $result)
    {
        $result['type_id'][] = 'customframe';

        return $result;
    }
}
