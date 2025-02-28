<?php
namespace Ziffity\ContactUs\Model\Inquires\Frontend;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Ziffity\ContactUs\Block\Adminhtml\Form\Field\InquireColumn;

class DynamicRows extends AbstractFieldArray
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    private $dropdownRenderer;

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn('name', ['label' => __('Inquires'), 'renderer' => $this->getDropdownRenderer()]);
        $this->addColumn('value', ['label' => __('Title')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Inquire Option');
    }

    /**
     * @param DataObject $row
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row) {
        $options = [];
        $dropdownField = $row->getDropdownField();
        if ($dropdownField !== null) {
            $options['option_extra_attr_' . $this->getDropdownRenderer()->calcOptionHash($dropdownField)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDropdownRenderer() {
        if (!$this->dropdownRenderer) {
            $this->dropdownRenderer = $this->getLayout()->createBlock(
                InquireColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->dropdownRenderer;
    }
}
