<?php

namespace Ziffity\SavedDesigns\Model;

use Magento\Framework\DataObject;
use Ziffity\SavedDesigns\Model\Pdf\Pdf;
use Ziffity\SavedDesigns\Model\Pdf\PdfFactory;
use Ziffity\SavedDesigns\Helper\Data;
use Amasty\RequestQuote\Model\Pdf\Template;
use Magento\Framework\Registry;

class AttachmentManager
{

    protected $registry;

    protected $template;

    protected $data;

    protected $pdfFactory;

    public $scopeConfig;

    public $mimePartInterfaceFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\MimePartInterfaceFactory $mimePartInterfaceFactory,
        PdfFactory $pdfFactory,Data $data,Template $template,Registry $registry
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory;
        $this->pdfFactory = $pdfFactory;
        $this->data = $data;
        $this->template = $template;
        $this->registry = $registry;
    }

    private $templateId;

    private $templateVars = [];

    private $parts = null;

    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
    }

    public function setTemplateVars($templateVars)
    {
        $this->templateVars = $templateVars;
    }

    public function getTemplateId()
    {
        return $this->templateId;
    }

    public function getTemplateVars()
    {
        return $this->templateVars;
    }

    public function resetParts()
    {
        $this->parts = null;
    }

    public function getParts()
    {
        return $this->parts;
    }

    public function addPart($part)
    {
        $this->parts[] = $part;
    }

    public function collectParts()
    {
        $this->parts = [];
        switch ($this->getTemplateId()) {
            case 'saved_designs_share_to_friend':
                $this->attachInvoicePDF();
                break;
        }
    }

    public function getStoreId()
    {
        $vars = $this->getTemplateVars();
        if (!isset($vars['store'])) {
            return null;
        }

        $store = $vars['store'];
        return $store->getId();
    }

    public function attachInvoicePDF()
    {
        $transportObject = new DataObject($this->getTemplateVars());
        $this->registry->register('share_additional_data',$transportObject->getData(),true);
        $pdf = $this->pdfFactory->create();
        $html = $this->getHtml($transportObject);
        $pdf->setHtml($html);
        $fileContent = $pdf->render();
        $fileName = 'invoice.pdf';
        $attachmentPart = $this->mimePartInterfaceFactory->create(
            [
                'content' => $fileContent,
                'type' => 'application/pdf',
                'fileName' => $fileName,
                'disposition' => \Zend\Mime\Mime::DISPOSITION_ATTACHMENT,
                'encoding' => \Zend\Mime\Mime::ENCODING_BASE64
            ]
        );
        $this->addPart($attachmentPart);
    }

    public function getHtml($transportObject): string
    {
        $template = $this->data->getTemplateContent();
        /** @var Template $template */
        $template = $this->template->setTemplateText($template)
            ->setVars($transportObject->getData())
            ->setOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->getStoreId()
                ]
            );
        return $template->processTemplate();
    }
}
