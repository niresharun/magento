<?php
declare(strict_types=1);

namespace Ziffity\ProductStamp\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Ui\Component\Listing\Columns;

class ImageFields extends AbstractFieldArray
{
    const IMAGE_FIELD = 'image';
    const NAME_FIELD = 'name';

    /**
     * @var $imageRenderer
     */
    private $imageRenderer;

    /**
     * @return Columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            self::NAME_FIELD,
            [
                'label' => __('Frame Name'),
                'class' => 'required-entry'
            ]
        );

        $this->addColumn(
            self::IMAGE_FIELD,
            [
                'label' => __('Frame Image'),
                'renderer' => $this->getImageRenderer(),
                'class' => 'required-entry'
            ]
        );

        $this->_addAfter       = false;
        $this->_addButtonLabel = __('Add');
    }

    private function getImageRenderer()
    {
        if (!$this->imageRenderer) {
            $this->imageRenderer = $this->getLayout()->createBlock(
                \Ziffity\ProductStamp\Block\Adminhtml\Form\Field\ImageColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->imageRenderer;
    }
}
