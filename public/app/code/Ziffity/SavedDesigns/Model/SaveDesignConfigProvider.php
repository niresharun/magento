<?php

namespace Ziffity\SavedDesigns\Model;

use Ziffity\ProductCustomizer\Model\ConfigProviderInterface;
use Magento\Framework\App\Request\Http;

class SaveDesignConfigProvider implements ConfigProviderInterface
{

    protected $customizerConfig = [];

    /**
     * @var Http
     */
    protected $request;

    /**
     * @param Http $request
     */
    public function __construct(Http $request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config['options']['save_design']['edit_mode'] = false;
        $config['options']['save_design']['share_code'] = null;
        if ($this->request->getParam('edit_mode') == 1){
            $config['options']['save_design']['edit_mode'] = true;
            if ($this->request->getParam('share_code')) {
                $config['options']['save_design']['share_code'] = $this->request
                    ->getParam('share_code');
            }
            return $config;
        }
        return $config;
    }

    /**
     * @param $config
     * @return void
     */
    public function setConfig($config)
    {
        $this->customizerConfig = $config;
    }
}
