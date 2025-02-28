<?php

namespace Ziffity\Dashboard\Block;

use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Html\Link\Current as CoreCurrent;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Escaper;

class Current extends CoreCurrent implements SortLinkInterface
{
    private Escaper $escaper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->escaper = $escaper;
    }

    /**
     * Get href URL
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl($this->getPath());
    }

    /**
     * @inheritDoc
     */
    private function getMca()
    {
        $routeParts = [
            (string) $this->_request->getModuleName(),
            (string) $this->_request->getControllerName(),
            (string) $this->_request->getActionName(),
        ];

        $parts = [];
        $pathParts = explode('/', trim($this->_request->getPathInfo(), '/'));
        foreach ($routeParts as $key => $value) {
            if (isset($pathParts[$key]) && $pathParts[$key] === $value) {
                $parts[] = $value;
            }
        }
        return implode('/', $parts);
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml()
    {
        $highlight = '';

        if ($this->getIsHighlighted()) {
            $highlight = ' current';
        }

        if (($this->getClass())&&($this->isCurrent())) {
            $html = '<li class="nav item current ' . $this->getClass(). '">';
            $html .= '<strong' . $this->getAttributesHtml() . '>'
                    . $this->escaper->escapeHtml(__($this->getLabel()))
                    . '</strong>';

            $html .= '</li>';

        } elseif($this->isCurrent()){
            $html = '<li class="nav item current ' .str_replace(' ', '_', strtolower($this->getLabel())). '">';
            $html .= '<strong' . $this->getAttributesHtml() . '>'
            . $this->escaper->escapeHtml(__($this->getLabel()))
            . '</strong>';

            $html .= '</li>';
        } elseif(($this->getClass())){
            $html = '<li class="nav item ' . $this->getClass() . '"><a href="' . $this->escaper->escapeHtml($this->getHref()) . '"';
            $html .= $this->getTitle() ? ' title="' . $this->escaper->escapeHtml(__($this->getTitle())) . '"' : '';
            $html .= $this->getAttributesHtml() . '>';

            if ($this->getIsHighlighted()) {
                $html .= '<strong';
                $html .= $this->getAttributesHtml() . '>';
            }

            $html .= $this->escaper->escapeHtml(__($this->getLabel()));

            if ($this->getIsHighlighted()) {
                $html .= '</strong>';
            }

            $html .= '</a></li>';
        }
        else {
            $html = '<li class="nav item ' . str_replace(' ', '_', strtolower($this->getLabel())) . $highlight . '"><a href="' . $this->escaper->escapeHtml($this->getHref()) . '"';
            $html .= $this->getTitle() ? ' title="' . $this->escaper->escapeHtml(__($this->getTitle())) . '"' : '';
            $html .= $this->getAttributesHtml() . '>';

            if ($this->getIsHighlighted()) {
                $html .= '<strong';
                $html .= $this->getAttributesHtml() . '>';
            }

            $html .= $this->escaper->escapeHtml(__($this->getLabel()));

            if ($this->getIsHighlighted()) {
                $html .= '</strong>';
            }

            $html .= '</a></li>';
        }

        return $html;
    }

    /**
     * @inheritDoc
     */
    private function getAttributesHtml()
    {
        $attributesHtml = '';
        $attributes = $this->getAttributes();
        if ($attributes) {
            foreach ($attributes as $attribute => $value) {
                $attributesHtml .= ' ' . $attribute . '="' . $this->escaper->escapeHtml($value) . '"';
            }
        }

        return $attributesHtml;
    }

    /**
     * @inheritDoc
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}