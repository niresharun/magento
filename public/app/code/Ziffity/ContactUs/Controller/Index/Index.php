<?php

namespace Ziffity\ContactUs\Controller\Index;

use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Ziffity\ContactUs\Block\ContactForm;
use Magento\Framework\App\Http;
use Magento\Framework\Controller\Result\JsonFactory;

class Index extends \Magento\Contact\Controller\Index\Index
{

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Http
     */
    protected $http;


    protected $resultJson;


    /**
     * @param Context $context
     * @param ConfigInterface $contactsConfig
     * @param LayoutFactory $layoutFactory
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJson
     * @param Http $http
     */
    public function __construct(
        Context $context,
        ConfigInterface $contactsConfig,
        LayoutFactory $layoutFactory,
        PageFactory $resultPageFactory,
        JsonFactory $resultJson,
        Http $http
    )
    {
        $this->layoutFactory = $layoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJson = $resultJson;
        $this->http = $http;
        parent::__construct($context, $contactsConfig);
    }


    /**
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $page = $this->resultPageFactory->create();
        $params = $this->getRequest()->getParams();
        $post = $this->getRequest()->getPost();
        $details = [];
        if (isset($params['inquiry']) && isset($params['isAjax'])) {
            try {
            $result = $this->resultJson->create();
            $details['message'] = 'SUCCESS';
            $details['content'] =  $page->getLayout()->createBlock(ContactForm::class)->
           setTemplate('Ziffity_ContactUs::inquire_list.phtml')->
           setData('inquireId', $params['inquiry'])->toHtml();
            } catch (\Exception $e) {
                $details['message'] = 'ERROR';
                $details['content_error'] = 'Something wrong. Please reload page and try again.';
            }
            return $result->setData($details);
        }
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
