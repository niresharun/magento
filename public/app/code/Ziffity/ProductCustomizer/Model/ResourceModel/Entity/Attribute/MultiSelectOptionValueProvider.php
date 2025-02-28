<?php

namespace Ziffity\ProductCustomizer\Model\ResourceModel\Entity\Attribute;

use \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionValueProvider;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class MultiSelectOptionValueProvider extends OptionValueProvider
{
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param ResourceConnection $connection
     */
    public function __construct(ResourceConnection $connection)
    {
        $this->connection = $connection->getConnection();
    }

    /**
     * Get EAV attribute option values by option ids
     *
     * @param int[] $valueIds
     * @return string[]|null
     */
    public function getMultiple(Array $valueIds): ?Array
    {
        $values = [];
        $select = $this->connection->select()
            ->from($this->connection->getTableName('eav_attribute_option_value'), 'value')
            ->where('option_id in (?)', $valueIds);

            $result = $this->connection->fetchAll($select);

        if ($result !== false) {
            $values = array_column($result, 'value');
            return $values;
        }
        return null;
    }
}
