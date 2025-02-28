<?php

declare(strict_types=1);

namespace Ziffity\RequestQuote\Helper;

use Amasty\RequestQuote\Block\Pdf\PdfTemplate;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    public const CONFIG_PATH_PDF_WITHOUT_HEADER = 'amasty_request_quote/pdf/template_content_noheader';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;


    /**
     * @var PdfTemplate
     */
    private $pdfTemplate;


    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PdfTemplate $pdfTemplate
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->pdfTemplate = $pdfTemplate;
    }

    public function getTemplateWithoutHeader(): string
    {
        $template = $this->scopeConfig->getValue(self::CONFIG_PATH_PDF_WITHOUT_HEADER, ScopeInterface::SCOPE_STORE);
        if (!$template) {
            $template = $this->pdfTemplate->toHtml();
        }

        return $template;
    }
}
