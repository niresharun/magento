<?php
namespace Ziffity\Coproduct\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class QtyClassificationData implements DataPatchInterface
{
    private $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable('product_quantity_classification');

        $data = [
            [
                'list_name' => 'Snap Frame - Springs Width',
                'classification' => '[{"size_from":"0","size_to":"30.99","qty":"4","record_id":"0"},{"size_from":"30.99","size_to":"40.99","qty":"6","record_id":"1"},{"size_from":"40.99","size_to":"50.99","qty":"8","record_id":"2"},{"size_from":"50.99","size_to":"60.99","qty":"10","record_id":"3"},{"size_from":"60.99","size_to":"70","qty":"14","record_id":"4"},{"size_from":"70","size_to":"80.99","qty":"16","record_id":"5"},{"size_from":"80.99","size_to":"90.99","qty":"18","record_id":"6"},{"size_from":"90.99","size_to":"100.99","qty":"20","record_id":"7"},{"size_from":"100.99","size_to":"110.99","qty":"22","record_id":"8"},{"size_from":"110.99","size_to":"121.75","qty":"24","record_id":"9"}]',
                'identifier' => 'snap_frame_springs_width',
            ],
            [
                'list_name' => 'Snap Frame - Springs Height',
                'classification' => '[{"size_from":"0","size_to":"30.99","qty":"4","record_id":"0"},{"size_from":"30.99","size_to":"40.99","qty":"6","record_id":"1"},{"size_from":"40.99","size_to":"50.99","qty":"8","record_id":"2"},{"size_from":"50.99","size_to":"60.99","qty":"10","record_id":"3"},{"size_from":"60.99","size_to":"70","qty":"12","record_id":"4"},{"size_from":"70","size_to":"80.99","qty":"16","record_id":"5"},{"size_from":"80.99","size_to":"90.99","qty":"18","record_id":"6"},{"size_from":"90.99","size_to":"100.99","qty":"20","record_id":"7"},{"size_from":"100.99","size_to":"110.99","qty":"22","record_id":"8"},{"size_from":"110.99","size_to":"121.75","qty":"24","record_id":"9"}]',
                'identifier' => 'snap_frame_springs_height'
            ],
            [
                'list_name' => 'LED Clamps - Height',
                'classification' => '[{"size_from":"0","size_to":"35.99","qty":"4","record_id":"0"},{"size_from":"35.99","size_to":"49.99","qty":"6","record_id":"1"},{"size_from":"49.99","size_to":"63.99","qty":"8","record_id":"2"},{"size_from":"63.99","size_to":"77.99","qty":"10","record_id":"3"},{"size_from":"77.99","size_to":"91.99","qty":"12","record_id":"4"},{"size_from":"91.99","size_to":"","qty":"14","record_id":"5"}]',
                'identifier' => 'number_of_led_clamps_height'
            ],
            [
                'list_name' => 'LED Clamps - Width',
                'classification' => '[{"size_from":"0","size_to":"35.99","qty":"4","record_id":"0"},{"size_from":"35.99","size_to":"49.99","qty":"6","record_id":"1"},{"size_from":"49.99","size_to":"63.99","qty":"8","record_id":"2"},{"size_from":"63.99","size_to":"77.99","qty":"10","record_id":"3"},{"size_from":"77.99","size_to":"91.99","qty":"12","record_id":"4"},{"size_from":"91.99","size_to":"","qty":"14","record_id":"5"}]',
                'identifier' => 'number_of_led_clamps_width'
            ],
            [
                'list_name' => 'Snap Frame - Alpina Security Screws  - Height',
                'classification' => '[{"size_from":"0","size_to":"40","qty":"2","record_id":"0"},{"size_from":"40","size_to":"","qty":"4","record_id":"1"}]',
                'identifier' => 'snap_frames_number_of_alpina_security_screws_height'
            ],
            [
                'list_name' => 'Snap Frame - Alpina Security Screws - Width',
                'classification' => '[{"size_from":"0","size_to":"40","qty":"2","record_id":"0"},{"size_from":"40","size_to":"","qty":"4","record_id":"1"}]',
                'identifier' => 'snap_frames_number_of_alpina_security_screws_width'
            ],
            [
                'list_name' => 'Hinge Generic',
                'classification' => '[{"size_from":"0","size_to":"24","qty":"2","record_id":"0"},{"size_from":"24","size_to":"36","qty":"3","record_id":"1"},{"size_from":"36","size_to":"48","qty":"4","record_id":"2"},{"size_from":"48","size_to":"72","qty":"5","record_id":"3"},{"size_from":"72","size_to":"84","qty":"6","record_id":"4"},{"size_from":"84","size_to":"","qty":"7","record_id":"5"}]',
                'identifier' => 'how_many_hinges'
            ],
            [
                'list_name' => 'Stopper',
                'classification' => '[{"size_from":"0","size_to":"14.99","qty":"1","record_id":"0"},{"size_from":"14.99","size_to":"","qty":"2","record_id":"1"}]',
                'identifier' => 'how_many_stoppers'
            ]
        ];

        foreach ($data as $row) {
            $connection->insert($table, $row);
        }
    }
}
