<?php
declare(strict_types=1);

namespace Ziffity\Netterms\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\App\Area;
use Magento\Framework\View\Element\Template;

/**
 * Netterms data helper
 */
class Data extends AbstractHelper
{
    const XML_PATH_EMAIL_TEMPLATE = 'payment/netterms/email_template';
    const XML_PATH_SALES_REPRESENTATIVE_NAME = 'trans_email/ident_sales/name';
    const XML_PATH_SALES_REPRESENTATIVE_EMAIL = 'trans_email/ident_sales/email';
    const XML_PATH_EMAIL_APPLICATION_FILE = 'payment/netterms/pdf';
    const APPLICATION_FILE_BASE_DIR = 'payment/netterms/pdf/';

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var Template
     */
    protected $blockTemplate;

    /**
     * constructor
     *
     * @param Context $context,
     * @param TransportBuilder $transportBuilder,
     * @param StoreManagerInterface $storeManager,
     * @param StateInterface $inlineTranslation
     * @param Template $blockTemplate
     */
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        Template $blockTemplate
    )
    {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->blockTemplate = $blockTemplate;
        parent::__construct($context);
    }

    /**
     * @param string $customerEmail
     * @param [] $orderData
     * @return void
     */
    public function sentNonApprovedEmail($customerEmail, $orderData)
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        $templateId = $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_TEMPLATE,
            $storeScope
        );
        $salesRepresentativeName = $this->scopeConfig->getValue(
            self::XML_PATH_SALES_REPRESENTATIVE_NAME,
            $storeScope
        );
        $salesRepresentativeEmail = $this->scopeConfig->getValue(
            self::XML_PATH_SALES_REPRESENTATIVE_EMAIL,
            $storeScope
        );
        $applicationFile = $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_APPLICATION_FILE,
            $storeScope
        );
        $orderData['application_url'] = $this->blockTemplate->getViewFileUrl('Ziffity_Netterms::files/Credit_Application_and_W-9_Form.pdf', ['area' => Area::AREA_FRONTEND]);

        $applicationFileBaseDir = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA ) . self::APPLICATION_FILE_BASE_DIR;
        $applicationFileUrl = $applicationFileBaseDir;
        if ($applicationFile) {
            $applicationFileUrl = $applicationFileBaseDir . $applicationFile;
            $orderData['application_url'] = $applicationFileUrl;
        }

        try {
            $orderData['application_url'] = $applicationFileUrl;

            $from = ['email' => $salesRepresentativeEmail, 'name' => $salesRepresentativeName];
            $this->inlineTranslation->suspend();

            $templateOptions = [
                'area' => Area::AREA_FRONTEND,
                'store' => $orderData['store_id']
            ];

            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($orderData)
                ->setFrom($from)
                ->addTo($customerEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }
}
