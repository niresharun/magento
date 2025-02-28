<?php

declare(strict_types=1);

namespace Ziffity\RequestQuote\Model\Pdf;

use Amasty\RequestQuote\Helper\Data;
use Amasty\RequestQuote\Model\Registry;
use Amasty\RequestQuote\Model\RegistryConstants;
use Magento\Framework\DataObject;
use Ziffity\RequestQuote\Helper\Data as QuoteHelper;

class HtmlGenerator extends \Amasty\RequestQuote\Model\Pdf\HtmlGenerator
{
    /**
     * @var Data
     */
    private $data;

    /**
     * @var PdfInformation
     */
    private $pdfInformation;

    /**
     * @var Template
     */
    private $template;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    public function __construct(
        Data $data,
        \Amasty\RequestQuote\Model\Pdf\PdfInformation $pdfInformation,
        \Amasty\RequestQuote\Model\Pdf\Template $template,
        Registry $registry,
        QuoteHelper $quoteHelper
    ) {
    	parent::__construct($data, $pdfInformation, $template, $registry);
        $this->data = $data;
        $this->pdfInformation = $pdfInformation;
        $this->template = $template;
        $this->registry = $registry;
        $this->quoteHelper = $quoteHelper;
    }

    public function getHtmlByQuote(): string
    {
    	$quote = $this->registry->registry(RegistryConstants::AMASTY_QUOTE);
        $template = $this->quoteHelper->getTemplateWithoutHeader();
        if($quote->getData('header')){
        	$template = $this->data->getTemplateContent();
        }
        $transportObject = new DataObject($this->pdfInformation->getQuoteDataForPdf());
        $template = $this->template->setTemplateText($template)
            ->setVars($transportObject->getData())
            ->setOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $quote->getStoreId()
                ]
            );

        return $template->processTemplate();
    }
}
