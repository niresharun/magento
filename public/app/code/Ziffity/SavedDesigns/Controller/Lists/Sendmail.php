<?php

/**
 * Controller to send email
 *
 */

namespace Ziffity\SavedDesigns\Controller\Lists;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Ziffity\SavedDesigns\Helper\Data as Helper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Ziffity\ProductCustomizer\Helper\Selections;

class Sendmail implements Action\HttpPostActionInterface
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Selections
     */
    protected $selections;

    /**
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param Helper $helper
     * @param JsonFactory $jsonFactory
     * @param LoggerInterface $logger
     * @param Selections $selections
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        ResultFactory $resultFactory,
        RequestInterface $request,
        ManagerInterface $messageManager,
        Helper $helper,
        JsonFactory $jsonFactory,
        LoggerInterface $logger,
        Selections $selections
    ) {
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->selections = $selections;
    }

    /**
     * Send Email Post Action
     *
     * @return void|Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $data = $this->request->getPostValue();
        $emailCustomer = $data['email_customer'];
        $emailOther = $data['email_other'];
        $nameCustomer = $data['name_customer'];
        $nameOther = $data['name_other'];

        //Send email
        $this->inlineTranslation->suspend();
        $product = $this->helper->getSavedDesignCollection($this->request->getParam('share_code'));
        try {
            $transport = $this->transportBuilder->setTemplateIdentifier(
                'saved_designs_share_to_friend'
            )->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getStoreId(),
                ]
            )->setTemplateVars(
                [
                    'customer' => $this->customerSession->getCustomerDataObject(),
                    'emailCustomer' => $emailCustomer,
                    'sender_name' => $nameCustomer,
                    'emailOther' => $emailOther,
                    'name' => $nameOther,
                    'store' => $this->storeManager->getStore(),
                    'product_id' => $product->getProductId(),
                    'product_name' => $product->getTitle(),
                    'product_url' =>  $this->copyUrl($product,$this->request->getParam('share_code')),
                    'product_image' => $this->request->getParam('image'),
                    'additional_data'=> $product->getAdditionalData(),
                    'your_selections' => $this->processAdditionalData($product),
                    'subject'=> $this->buildSubject($nameCustomer)
                ]
            )->setFrom(
                [
                    'email' => $this->getGeneralSenderMail(),
                    'name' => $nameCustomer
                ]
            )->addTo($emailOther)->setReplyTo($emailCustomer, $nameCustomer)
            ->getTransport();
            $transport->sendMessage();
            $this->messageManager->addSuccessMessage('Email Sent Successfully');
            $this->inlineTranslation->resume();
            return $this->jsonFactory->create()->setData(['success'=>true]);
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->critical($e);
            return $this->jsonFactory->create()->setData(['success'=>false]);
        }
    }

    /**
     * This function gets the copy URL using the product id and share code.
     *
     * @param object $product
     * @param string|null $shareCode
     * @return mixed
     */
    public function copyUrl($product, $shareCode)
    {
        $url = $this->helper->findUrlRewrite($product->getProductId());
        return $this->helper->buildShareUrl($url,$shareCode,false);
    }

    /**
     * This function builds the subject for the mail using the name string.
     *
     * @param string $name
     * @return string
     */
    public function buildSubject($name)
    {
        return "Take a look at what ".$name."'s has been eyeing";
    }

    /**
     * This function gets the mail address from the general contact from config.
     *
     * @return mixed
     */
    public function getGeneralSenderMail()
    {
        return $this->scopeConfig->getValue('trans_email/ident_general/email');
    }

    /**
     * This function processes the data for your selections.
     *
     * @param string $data
     * @return array
     */
    public function processAdditionalData($product)
    {
        $result = [];
        if($product && $product->getAdditionalData()) {
            $completedSteps = $this->selections->getCompletedStepsFromOptions($product->getAdditionalData());
            $data = $this->selections->getUnserializedData($product->getAdditionalData());
            $result = $this->selections->getSelections($data, $product, $completedSteps);
//            foreach (json_decode($data, true) as $key => $value) {
//                if (isset($value['active_item']['name']) && $value['active_item']['price']) {
//                    $result[$key]['selection_name'] = ucfirst($key);
//                    $result[$key]['value'] = $value['active_item']['name'];
//                }
//            }
        }
        return $result;
    }
}
