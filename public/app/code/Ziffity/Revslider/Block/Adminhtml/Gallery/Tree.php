<?php

namespace Ziffity\Revslider\Block\Adminhtml\Gallery;

use \Nwdthemes\Revslider\Helper\Images;

class Tree extends \Nwdthemes\Revslider\Block\Adminhtml\Gallery\Tree {

    protected $serializer;

    /**
     * @param array $serializer
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Nwdthemes\Revslider\Helper\Gallery\Images $cmsWysiwygImages,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        array $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct($context, $cmsWysiwygImages, $registry,$data);
    }

    public function getTreeJson() {
        $storageRoot = $this->_cmsWysiwygImages->getStorageRoot();
        $collection = $this->_coreRegistry->registry(
            'nwdthemes_revslider_gallery_storage'
        )->getDirsCollection(
            $this->_cmsWysiwygImages->getCurrentPath()
        );
        $jsonArray = [];
        foreach ($collection as $item) {
            $path = substr($item->getFilename(), strlen($storageRoot));
            if ( ! in_array($path, explode(',', Images::IMAGE_DIR_EXCLUDES)))
            $jsonArray[] = [
                'text' => $this->_cmsWysiwygImages->getShortFilename($item->getBasename(), 20),
                'id' => $this->_cmsWysiwygImages->convertPathToId($item->getFilename()),
                'path' => $path,
                'cls' => 'folder',
            ];
        }
        return $this->serializer->serialize($jsonArray);
    }

    public function getTreeLoaderUrl() {
        return $this->getUrl('nwdthemes_revslider/*/treeJson');
    }

    public function getRootNodeName() {
        return __('Slider Revolution Media Gallery');
    }

    public function getTreeCurrentPath() {
        $treePath = ['root'];
        if ($path = $this->_coreRegistry->registry('nwdthemes_revslider_gallery_storage')->getSession()->getCurrentPath()) {
            $path = str_replace($this->_cmsWysiwygImages->getStorageRoot(), '', $path);
            $relative = [];
            foreach (explode('/', $path) as $dirName) {
                if ($dirName) {
                    $relative[] = $dirName;
                    $treePath[] = $this->_cmsWysiwygImages->idEncode(implode('/', $relative));
                }
            }
        }
        return $treePath;
    }

}
