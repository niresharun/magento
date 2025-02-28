<?php

namespace Ziffity\StaticData\Setup\Patch\Data;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class CreateAccountCmsBlock
 *
 * Create CMS blocks for account access page Banners
 */
class AccountAccess implements DataPatchInterface
{
    /**
     * Enable Cms Block
     */
    const ENABLE = 1;

    /**
     * Enable Cms Block
     */
    const STORE_ID = 0;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var GetBlockByIdentifierInterface
     */
    private $getBlockByIdentifier;

    /**
     * @var BlockRepository
     */
    private $blockRepository;

    /**
     * GatewayMenuCmsBlock constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param GetBlockByIdentifierInterface $getBlockByIdentifier
     * @param BlockFactory $blockFactory
     * @param BlockRepository $blockRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        GetBlockByIdentifierInterface $getBlockByIdentifier,
        BlockFactory $blockFactory,
        BlockRepository $blockRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->getBlockByIdentifier = $getBlockByIdentifier;
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
    }
    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        foreach ($this->getData() as $blockData) {
            /** @var BlockInterface $existingBlock */
            try {
                $existingBlock = $this->getBlockByIdentifier->execute($blockData['identifier'], self::STORE_ID);
                $blockExists = true;
            } catch (NoSuchEntityException $exception) {
                $blockExists = false;
            }

            if ($blockExists && $existingBlock->getId() !== null) {
                $existingBlock->setContent($blockData['content']);
                $existingBlock->setTitle($blockData['title']);
                $this->blockRepository->save($existingBlock);
            } else {
                /** @var BlockInterface $newBlock */
                $newBlock = $this->blockFactory->create();
                $newBlock->setData($blockData);
                $newBlock->setIsActive(self::ENABLE);
                $newBlock->setStores([0]);
                $newBlock->setSortOrder(0);
                $this->blockRepository->save($newBlock);
            }
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * Get CMS block data.
     *
     * @return array
     */
    public function getData()
    {
        return [
            [
                'title' => 'Login / Banner Image',
                'identifier' => 'login-banner-img',
                'content' => '<style>#html-body [data-pb-style=DE3YN61]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=CCG75H1]{border-style:none}#html-body [data-pb-style=RVVM2P4],#html-body [data-pb-style=VYUKSOH]{max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=CCG75H1]{border-style:none} }</style>
                <div class="hover-frame" data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="DE3YN61">
                <figure class="cover-img" data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="CCG75H1"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/account/login-banner.png}}" alt="" data-element="desktop_image" data-pb-style="VYUKSOH"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/account/login-banner.png}}" alt="" data-element="mobile_image" data-pb-style="RVVM2P4"></figure>
                </div>'
            ],
            [
                'title' => 'Registration / Banner Image',
                'identifier' => 'reg-banner-img',
                'content' => '<style>#html-body [data-pb-style=DE3YN61]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=CCG75H1]{border-style:none}#html-body [data-pb-style=RVVM2P4],#html-body [data-pb-style=VYUKSOH]{max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=CCG75H1]{border-style:none} }</style>
                <div class="hover-frame" data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="DE3YN61">
                <figure class="cover-img" data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="CCG75H1"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=".renditions/wysiwyg/account/register-banner.png"}}" alt="" data-element="desktop_image" data-pb-style="VYUKSOH"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/account/login-banner.png}}" alt="" data-element="mobile_image" data-pb-style="RVVM2P4"></figure>
                </div>'
            ],
            [
                'title' => 'Forgot Password /  Banner Image',
                'identifier' => 'forgot-pw-banner-img',
                'content' => '<style>#html-body [data-pb-style=DE3YN61]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=CCG75H1]{border-style:none}#html-body [data-pb-style=RVVM2P4],#html-body [data-pb-style=VYUKSOH]{max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=CCG75H1]{border-style:none} }</style>
                <div class="hover-frame" data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="DE3YN61">
                <figure class="cover-img" data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="CCG75H1"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=".renditions/wysiwyg/account/forgot-banner.png"}}" alt="" data-element="desktop_image" data-pb-style="VYUKSOH"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/account/login-banner.png}}" alt="" data-element="mobile_image" data-pb-style="RVVM2P4"></figure>
                </div>'
            ]
        ];
    }
}
