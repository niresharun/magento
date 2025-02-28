<?php

namespace Ziffity\ContactUs\Block;

use Magento\Contact\Block\ContactForm as CoreContactForm;
use Magento\Framework\View\Element\Template;
use Ziffity\ContactUs\Model\Inquires;
use Ziffity\ContactUs\Model\Inquire;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Main contact form block
*/
class ContactForm extends CoreContactForm
{

    /**
     * @var Inquires
     */
    protected $inquireSorce;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var Inquire
     */
    protected $inquire;

    /**
     * @param Inquires $inquireSorce
     * @param Inquire $inquire
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $jsonSerializer
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Inquires $inquireSorce,
        Inquire $inquire,
        ScopeConfigInterface $scopeConfig,
        Json $jsonSerializer,
        Template\Context $context,
        array $data = []
    )
    {
        $this->inquireSorce = $inquireSorce;
        $this->inquire = $inquire;
        $this->scopeConfig = $scopeConfig;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }


    /**
     * @return array|array[]
     */
    public function getInquiryList()
    {
        $inquireList = $this->inquireSorce->getAllOptions();
        return $inquireList;
    }

    /**
     * @param $inquireId
     * @return array
     */
    public function getInquireListOptions($inquireId)
    {
        $currentOptions = [];
        $inquiresAllOptions = $this->inquire->toOptionArray();
        foreach ($inquiresAllOptions as $key => $inquireOption) {
            if (isset($inquireOption[$inquireId])){
                $currentOptions[$key][$inquireId] = $inquireOption[$inquireId];
            }
        }
        if(empty($currentOptions)){
            $arr = [];
            $arr['name'] = $inquireId;
            $arr['value'] = '';
            $currentOptions[0][$inquireId] = $arr;
        }
        return $currentOptions;
    }

}
