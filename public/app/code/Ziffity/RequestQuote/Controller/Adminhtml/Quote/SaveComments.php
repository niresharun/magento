<?php
namespace Ziffity\RequestQuote\Controller\Adminhtml\Quote;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Area;
use Magento\Store\Model\Store;
use Magento\Framework\Data\Form\FormKey\Validator;
use Amasty\RequestQuote\Block\Adminhtml\Quote\AbstractQuote;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;

class SaveComments extends Action implements HttpPostActionInterface
{
    public const USER = 'Admin';

    protected $quoteCommentFactory;
    protected $quoteFactory;
    protected $transportBuilder;
    protected $inlineTranslation;
    protected $customerSession;
    protected $quoteData;
    protected $formKeyValidator;
    protected $resultRedirect;
    protected $resultJsonFactory;
    protected $resultPageFactory;
    protected $messageManager;


    public function __construct(
        Context $context,
        \Ziffity\RequestQuote\Model\QuoteCommentFactory $quoteCommentFactory,
        \Amasty\RequestQuote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Customer\Model\Session $customerSession,
        AbstractQuote $quoteData,
        Validator $formKeyValidator,
        ResultFactory $resultRedirect,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
    ) {
        $this->quoteCommentFactory = $quoteCommentFactory;
        $this->quoteFactory = $quoteFactory;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->customerSession = $customerSession;
        $this->quoteData = $quoteData;
        $this->formKeyValidator = $formKeyValidator;
        $this->messageManager = $context->getMessageManager();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirect = $resultRedirect;
        $this->resultPageFactory= $resultPageFactory;
        parent::__construct($context);
    }
    /**
     * Add order comment action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try{
            $data = $this->getRequest()->getPost('history');
            if (empty($data['comment'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The comment is missing. Enter and try again.')
                );
            }
            $comment = trim(strip_tags($data['comment']));
            $status = $data['status'];
            $notify = $data['is_customer_notified'] ?? false;
            $visible = $data['is_visible_on_front'] ?? false;
            $quoteId = $this->getRequest()->getParam('quote_id');
            $quoteModel = $this->quoteFactory->create()->loadByIdWithoutStore($quoteId);
            if($quoteModel){
                $quoteModel->setStatus($status);
                $quoteModel->save();
            }
            if ($notify == 1) {
                $this->sendMail($data);
            }
            $model = $this->quoteCommentFactory->create();
            $sampleData = [
                "quote_id" => $quoteId,
                "comment" => $comment,
                "quote_status" => $status,
                "author" => self::USER,
                "customer_notified" => 1,
                "is_customer_notified" => $notify,
                "is_visible_on_frontend"=> $visible
            ];
            $model->setData($sampleData)->save();
            $this->messageManager->addSuccessMessage(
                __('Sucessfully Updated')
            );
            return $this->resultPageFactory->create();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('We cannot add quote comment.')];
        }
        if (is_array($response)) {
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData($response);
            return $resultJson;
        }
        $result = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
        return $result->setPath('amasty_quote/quote/view');
    }
    public function sendMail($data): array
    {
        try {
            $templateId = "email_comment_template";
            $senderEmail = "owner@example.com";
            $senderName = "Admin";
            $receiverEmail = $this->getCustomerData();
            $this->inlineTranslation->suspend();
            $sender = [
                'email' => $senderEmail, 'name' => $senderName
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars([
                    'name' => "Admin",
                    'items' => $data['comment'],
                ])
                ->setFromByScope($sender)
                ->addTo($receiverEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->messageManager->addSuccessMessage('Email Sent Successfully');
            $this->inlineTranslation->resume();
            return ['is_error' => false];
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage('Something went wrong');
            return ['is_error' => true];
        }
    }
    public function getCustomerData()
    {
        $emailData =  $this->quoteData->getQuote()->getData('customer_email');
        return $emailData;
    }
}
