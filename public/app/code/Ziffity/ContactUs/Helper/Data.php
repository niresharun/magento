<?php

namespace Ziffity\ContactUs\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @param $data
     * @return string
     */
    public function getNameFromTitle($data)
    {
        $identifier = '';
        if ($data) {
            $identifier = strtolower($data);
            $identifier = strip_tags($identifier);
            $identifier = stripslashes($identifier);
            $identifier = html_entity_decode($identifier);

            # Remove quotes (can't, etc.)
            $identifier = str_replace('\'', '', $identifier);

            # Replace non-alpha numeric with hyphens
            $match = '/[^a-z0-9]+/';
            $replace = '-';
            $identifier = preg_replace($match, $replace, $identifier);

            $identifier = trim($identifier, '-');
        }
        return $identifier;
    }
}
