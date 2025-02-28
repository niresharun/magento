<?php

namespace Ziffity\RequestQuote\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Ziffity\RequestQuote\Model\QuoteCommentFactory;
use Amasty\RequestQuote\Model\QuoteFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Controller\ResultFactory;

class Comments extends Action
{
    public const USER = 'Customer';

    protected $resultRedirect;
    protected $quoteFactory;
    protected $QuoteComment;
    protected $messageManager;
    protected $formKeyValidator;

    public function __construct(
        Context $context,
        QuoteFactory $quoteFactory,
        QuoteCommentFactory $QuoteComment,
        ManagerInterface $messageManager,
        Validator $formKeyValidator,
        ResultFactory $resultRedirect
    ) {
        $this->QuoteComment = $QuoteComment;
        $this->quoteFactory = $quoteFactory;
        $this->messageManager = $messageManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->resultRedirect = $resultRedirect;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
        if ($this->formKeyValidator->validate($this->getRequest())) {
            $post = $this->getRequest()->getParams();

            if(array_key_exists('comments',$post)){
                $comment = $post['comments'];
            }

            $quoteId = $this->getRequest()->getParam('quote_id');
            $amastyModel = $this->quoteFactory->create()->load($quoteId);
            $status = $amastyModel->getData('status');
            $model = $this->QuoteComment->create();
            $sampleData = [
                "quote_id" => $quoteId,
                "comment" => $comment,
                "quote_status" => $status,
                "author" => self::USER,
                "is_visible_on_frontend"=> 1,
            ];
            $model->setData($sampleData)->save();
            $this->messageManager->addSuccessMessage(
                __('Sucessfully Updated')
            );
        }
        if (!$post) {
            $this->messageManager
                ->addErrorMessage(__('Failed to Update'));
        }
        $result->setRefererUrl();
        return $result;
    }
}
