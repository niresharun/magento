<?php
namespace Ziffity\AbandonedQuote\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Ziffity\AbandonedQuote\Block\Adminhtml\Form\Field\Sender;
use Ziffity\AbandonedQuote\Block\Adminhtml\Form\Field\EmailTemplate;

/**
 * Class Ranges
 */
class Email extends AbstractFieldArray
{
    /**
     * @var Sender
     */
    private $sender;

    /**
     * @var EmailTemplate
     */
    private $mail;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('send_after', ['label' => __('Send after'), 'class' => 'required-entry']);
        $this->addColumn('sender', [
            'label' => __('Sender'), 
            'renderer' => $this->getSender()]);
        $this->addColumn('email', [
            'label' => __('Email Template'),
            'renderer' => $this->getMail()]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $tax = $row->getTax();
        if ($tax !== null) {
            $options['option_' . $this->getSender()->calcOptionHash($tax)] = 'selected="selected"';
        }
        $email = $row->getEmail();
        if ($email !== null) {
            $options['option_' . $this->getMail()->calcOptionHash($email)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return Sender
     * @throws LocalizedException
     */
    private function getSender()
    {
        if (!$this->sender) {
            $this->sender = $this->getLayout()->createBlock(
                Sender::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->sender;
    }

     /**
     * @return EmailTemplate
     * @throws LocalizedException
     */
    private function getMail()
    {
        if (!$this->mail) {
            $this->mail = $this->getLayout()->createBlock(
                EmailTemplate::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->mail;
    }
}