<?php

declare(strict_types=1);

namespace Amasty\RequestQuote\Model\Pdf;

class Template extends \Magento\Email\Model\AbstractTemplate implements \Magento\Framework\Mail\TemplateInterface
{
    /**
     * @var array
     */
    private $vars = [];

    protected function getFilterFactory()
    {
        return $this->getData('filterFactory');
    }

    public function getType()
    {
        return self::TYPE_HTML;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function processTemplate()
    {
        // Support theme fallback for PDF templates
        $isDesignApplied = $this->applyDesignConfig();

        // fix for 2.3.4 and newer
        $this->setData('is_legacy', 1);

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($this->_getVars());

        if ($isDesignApplied) {
            $this->cancelDesignConfig();
        }

        return $text;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return '';
    }

    public function setVars(array $vars): Template
    {
        $this->vars = $vars;
        return $this;
    }

    protected function _getVars(): array
    {
        return $this->vars;
    }

    /**
     * @param array $options
     * @return Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setOptions(array $options)
    {
        return $this->setDesignConfig($options);
    }
}
