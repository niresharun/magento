<?php

namespace Ziffity\SavedDesigns\Plugin;

class TransportBuilder
{
    public $attachmentManager;

    public function __construct(
        \Ziffity\SavedDesigns\Model\AttachmentManager $attachmentManager
    ) {
        $this->attachmentManager = $attachmentManager;
    }

    public function beforeSetTemplateIdentifier($subject, $templateId)
    {
        $this->attachmentManager->resetParts();
        $this->attachmentManager->setTemplateId($templateId);
        return [$templateId];
    }

    public function beforeSetTemplateVars($subject, $templateVars)
    {
        $this->attachmentManager->setTemplateVars($templateVars);
        return [$templateVars];
    }
}
