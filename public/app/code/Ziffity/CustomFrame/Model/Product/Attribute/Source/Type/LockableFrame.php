<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Magento\Framework\Data\OptionSourceInterface;

class LockableFrame extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => 'Locking Tool', 'value' => 'Locking Tool'],
                ['label' => 'Lock & Key', 'value' => 'Lock & Key'],
                ['label' => 'Not Lockable', 'value' => 'Not Lockable']
            ];
        }
        return $this->_options;
    }
}
