<?php
declare(strict_types=1);

namespace Ziffity\ProductStamp\Model;

class ArrayFileModifier
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var string
     */
    private $requestName;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $requestName
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        string $requestName = 'groups'
    ) {
        $this->request = $request;
        $this->requestName = $requestName;
    }

    /**
     * @return array|false
     */
    public function modify()
    {
        $files = [];
        $filesList = $this->request->getFiles($this->requestName);
        if ($filesList != null) {
            $requestFiles = $this->parseRequest(
                $this->request->getFiles($this->requestName)
            );
            $files = [];
            foreach ($requestFiles as $id => $file) {
                $data = array_shift($file);
                if (!$data['tmp_name']) {
                    continue;
                }
                $files[$id] = $data;
            }
            $_FILES = $files;
            return $files;
        }
        return false;
    }

    /**
     * @param $requestFiles
     * @return mixed
     */
    private function parseRequest($requestFiles)
    {
        if (isset($requestFiles['value'])) {
            return $requestFiles['value'];
        }
        if (is_array($requestFiles)) {
            $requestFiles = array_shift($requestFiles);
            return $this->parseRequest($requestFiles);
        }
        return $requestFiles;
    }
}
