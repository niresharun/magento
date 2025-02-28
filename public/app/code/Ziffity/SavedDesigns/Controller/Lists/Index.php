<?php
namespace Ziffity\SavedDesigns\Controller\Lists;

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\UrlInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
	protected $_pageFactory;

	 /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param UrlInterface $urlInterface
     * @param SessionFactory $customerSession
     */
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		UrlInterface $urlInterface,
        SessionFactory $customerSession
		)
	{
		parent::__construct($context);
		$this->_pageFactory = $pageFactory;
		$this->customerSession = $customerSession;
        $this->urlInterface = $urlInterface;
	}

    /**
     * @return void
     * @throws \Magento\Framework\Exception\SessionException
     */
	public function execute()
	{
		$customerSession = $this->customerSession->create();
		if (!$customerSession->isLoggedIn()) {
            $customerSession->setAfterAuthUrl($this->urlInterface->getCurrentUrl());
            $customerSession->authenticate();
        } else {
        	$this->_view->loadLayout();
			$this->_view->renderLayout();
        }
	}
}
