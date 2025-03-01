<?php

namespace Ziffity\CustomFrame\Ui\Component\Listing\Grid\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Backend\Model\UrlInterface;

class Action extends Column
{
    /**
     *  Edit Url Path 
     * */
    const ROW_EDIT_URL = 'ziffity_customframe/lists/edit';


    /**
     * Delete Url Path
     */
    const ROW_DELETE_URL = 'ziffity_customframe/lists/delete';
    
    /** 
     * @var UrlInterface 
     * */
    protected $_urlBuilder;

    /**
     * @var string
     */
    private $_editUrl;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     * @param string             $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->_urlBuilder->getUrl(
                            self::ROW_EDIT_URL,
                            ['id' => $item['id']]
                        ),
                        'label' => __('Edit'),
                    ];
                    $item[$name]['delete'] = [
                            'href' => $this->_urlBuilder->getUrl(
                                self::ROW_DELETE_URL,
                                [
                                    'id' => $item['id'],
                                ]
                            ),
                            'label' => __('Delete'),
                    ];
                }
            }
        }

        return $dataSource;
    }
}