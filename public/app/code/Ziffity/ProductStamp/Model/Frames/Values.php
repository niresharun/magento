<?php
namespace Ziffity\ProductStamp\Model\Frames;

class Values extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @var \Ziffity\ProductStamp\Helper\Data
     */
	protected $helper;

    /**
     * @param \Ziffity\ProductStamp\Helper\Data $helper
     */
    public function __construct(
        \Ziffity\ProductStamp\Helper\Data $helper
    ){
        $this->helper = $helper;
    }

    /**
     * @return array|null
     */
	public function getAllOptions()
	{
		$frameOptions = $this->helper->getFrameOptions();

		$this->_options=[
			['label'=>'Select Options', 'value'=>'']
		];
		foreach ($frameOptions as $key => $value) {
			$this->_options[] = ['label'=> $key, 'value'=> $key];
		}
		return $this->_options;
	}
}
