<?php

namespace Ziffity\SavedDesigns\Plugin;

/**
 * Plugin class for Magento\Framework\Mail\MimeMessage
 */
class MimeMessage
{
    /**
     * @var \Ziffity\SavedDesigns\Model\AttachmentManager
     */
    public $attachmentManager;

    /**
     * MimeMessage constructor.
     * @param \Ziffity\SavedDesigns\Model\AttachmentManager $attachmentManager
     */
    public function __construct(
        \Ziffity\SavedDesigns\Model\AttachmentManager $attachmentManager
    ) {
        $this->attachmentManager = $attachmentManager;
    }

    /**
     * Add attachment part in the end of email parts
     * @param $subject
     * @param $parts
     * @return array
     */
    public function afterGetParts($subject, $parts)
    {
        if (!empty($parts) && $this->attachmentManager->getParts() === null) {
            $this->attachmentManager->collectParts();
            $additionalParts = $this->attachmentManager->getParts();
            if (!empty($additionalParts)) {
                foreach ($additionalParts as $aPart) {
                    $parts[] = $aPart;
                }
            }
        }

        return $parts;
    }
}
