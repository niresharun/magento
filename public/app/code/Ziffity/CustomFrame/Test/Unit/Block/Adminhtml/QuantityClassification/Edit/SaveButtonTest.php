<?php

declare(strict_types=1);

namespace Ziffity\CustomFrame\Test\Unit\Block\Adminhtml\QuantityClassification\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Ziffity\CustomFrame\Block\Adminhtml\QuantityClassification\Edit\SaveButton;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveButtonTest extends TestCase
{
    /**
     * @var SaveButton|MockObject
     */
    protected $model;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    protected function setUp(): void
    {
        $this->registryMock = $this->createMock(Registry::class);
        $contextMock = $this->createMock(Context::class);

        $this->model = (new ObjectManager($this))->getObject(
            SaveButton::class,
            [
                'context' => $contextMock,
                'registry' => $this->registryMock
            ]
        );
    }

    /**
     * Unit test for \Ziffity\CustomFrame\Test\Unit\Block\Adminhtml\QuantityClassification\Edit\SaveButton::getButtonData() method
     */
    public function testGetButtonData()
    {
        $data = [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];

        $this->assertEquals($data, $this->model->getButtonData());
    }
}
