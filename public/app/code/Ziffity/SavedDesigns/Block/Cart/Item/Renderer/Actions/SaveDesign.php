<?php

namespace Ziffity\SavedDesigns\Block\Cart\Item\Renderer\Actions;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\View\Element\Template;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Request\Http;

/**
 * Class MoveToSaveDesign
 */
class SaveDesign extends Generic
{
    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    protected $request;

    /**
     * @param Template\Context $context
     * @param UrlInterface $urlInterface
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        UrlInterface $urlInterface,
        Http $request,
        array $data = []
    ) {
        $this->urlInterface = $urlInterface;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * Check whether "Save design" button is allowed in cart
     *
     * @return bool
     */
    public function isAllowInCart()
    {
        return true;
    }

    /**
     * Get JSON POST params for moving from cart to save design
     *
     * @return string
     */
    public function getSaveDesignParams()
    {
        return json_encode([
            'action' => $this->urlInterface
            ->getUrl('saveddesigns/save/cartitem'),
            'data' => ['item' => $this->getItem()->getId(),
            'request_quote'=>$this->checkIfRequestQuote(),
            'cart_id'=>$this->getItem()->getQuoteId()]
            ]
        );
    }

    public function checkIfRequestQuote()
    {
        $request = $this->request->getControllerModule();
        if (preg_match("/amasty/i",$request)){
            return true;
        }
        return false;
    }
}
