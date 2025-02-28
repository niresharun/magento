<?php
declare(strict_types=1);

namespace Ziffity\AbandonedQuote\Block\Adminhtml\Form\Field;


use Magento\Framework\View\Element\Html\Select;
use Magento\Config\Model\Config\Source\Email\Identity as EmailIdentitySource;

class Sender extends Select
{
    /**
     * @var EmailIdentitySource
     */
    private $emailIdentitySource;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        EmailIdentitySource $emailIdentitySource,
        array $data = []
    ) {
        $this->emailIdentitySource = $emailIdentitySource;
        parent::__construct($context, $data);
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render field as select element HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->emailIdentitySource->toOptionArray());
        }
        return parent::_toHtml();
    }

}
