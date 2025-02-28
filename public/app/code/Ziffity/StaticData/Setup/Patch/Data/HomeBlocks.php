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
 * Class HomeBlocks
 *
 * Create CMS Block for Homepage Links
 */
class HomeBlocks implements DataPatchInterface
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
                'title' => 'Home / Categories Link Carousel',
                'identifier' => 'home-cat-link-carousel',
                'content' => '<style>#html-body [data-pb-style=D1WD4YL]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=CXV1B1Y]{display:flex;width:100%}#html-body [data-pb-style=G98Y79P]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:100%;align-self:stretch}</style>
                <div class="pagebuilder-column-group catlink-carousel" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="D1WD4YL">
                <div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="CXV1B1Y">
                <div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="G98Y79P">
                <div data-content-type="text" data-appearance="default" data-element="main">
                <ul>
                <li><a tabindex="0" href="#">Letter Boards</a></li>
                <li id="VCP4SK6"><a tabindex="0" href="#">Poster Frames</a></li>
                <li><a tabindex="0" href="#">Picture Frames</a></li>
                <li><a tabindex="0" href="#">Slide-In Frames</a></li>
                <li><a tabindex="0" href="#">Shadow Boxes</a></li>
                <li><a tabindex="0" href="#">Swing Frames</a></li>
                <li><a tabindex="0" href="#">Marker Boards</a></li>
                <li><a tabindex="0" href="#">Snap Frames</a></li>
                </ul>
                </div>
                </div>
                </div>
                </div>
                <div data-content-type="html" data-appearance="default" data-element="main">&lt;script&gt; require(["jquery","slick"], function($){ $(".catlink-carousel ul").slick({ centerMode: true, centerPadding: "100px", slidesToShow: 6, responsive: [ { breakpoint: 1440, settings: { centerMode: true, centerPadding: "90px", slidesToShow: 5 } }, { breakpoint: 1200, settings: { variableWidth: true, centerPadding: "60px", } } ] }); }); &lt;/script&gt;</div>'
            ],
            [
                'title' => 'Home / Display Frames Banner',
                'identifier' => 'home-display-frame-banner',
                'content' => '<style>#html-body [data-pb-style=LAJOV6X],#html-body [data-pb-style=OS2EOMU]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=OS2EOMU]{justify-content:flex-start;display:flex;flex-direction:column}#html-body [data-pb-style=LAJOV6X]{align-self:stretch}#html-body [data-pb-style=CJHC8CM]{display:flex;width:100%}#html-body [data-pb-style=H2JIRW0]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:41.6667%;align-self:stretch}#html-body [data-pb-style=QMG6U4F]{border-style:none}#html-body [data-pb-style=EMF1QYR],#html-body [data-pb-style=YXFNHPV]{max-width:100%;height:auto}#html-body [data-pb-style=VTQ5VK3]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:58.3333%;align-self:stretch}#html-body [data-pb-style=TCJ29G8]{display:inline-block}#html-body [data-pb-style=T9HFXWS]{text-align:center}@media only screen and (max-width: 768px) { #html-body [data-pb-style=QMG6U4F]{border-style:none} }</style>
                <div class="home-dfb mb-10 txt-16" data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="OS2EOMU">
                <div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="LAJOV6X">
                <div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="CJHC8CM">
                <div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="H2JIRW0">
                <figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="QMG6U4F"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/Display_Frames_You_Design_Online.jpg}}" alt="" data-element="desktop_image" data-pb-style="YXFNHPV"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/Display_Frames_You_Design_Online.jpg}}" alt="" data-element="mobile_image" data-pb-style="EMF1QYR"></figure>
                </div>
                <div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="VTQ5VK3">
                <h2 data-content-type="heading" data-appearance="default" data-element="main">Display Frames You Design Online</h2>
                <div data-content-type="text" data-appearance="default" data-element="main">
                <p>With our new online customizer, design poster and signage frames, shadow boxes and other custom displays for your business, organization, office or home décor. Hundreds of metal &amp; wood picture frame profiles and styles plus an array of other framing enhancements provide the tools to be creative and get the right display frame design for your project needs.</p>
                </div>
                <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main">
                <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="TCJ29G8"><a class="pagebuilder-button-primary" href="#" target="" data-link-type="default" data-element="link" data-pb-style="T9HFXWS"><span data-element="link_text">Let’s Get Started</span></a></div>
                </div>
                </div>
                </div>
                </div>
                </div>'
            ],
            [
                'title' => 'Home / Top Categories',
                'identifier' => 'home-top-categories',
                'content' => '<style>#html-body [data-pb-style=AS1S47C]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=EJLTEEA]{text-align:center}#html-body [data-pb-style=QCEONTH],#html-body [data-pb-style=QKLLGXT]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=I15DGTS]{display:inline-block}#html-body [data-pb-style=SK5CWFR]{text-align:center}#html-body [data-pb-style=JM61RL0],#html-body [data-pb-style=Y8JFJSB]{display:flex;width:100%}#html-body [data-pb-style=LH8L8TI],#html-body [data-pb-style=T8929K6]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:100%;align-self:stretch}#html-body [data-pb-style=PLNWLJ7]{text-align:center}#html-body [data-pb-style=VBK3KFL]{border-style:none}#html-body [data-pb-style=BXCXD55],#html-body [data-pb-style=RFEP1C2]{max-width:100%;height:auto}#html-body [data-pb-style=W3BE4GR]{border-style:none}#html-body [data-pb-style=NDV7YQG],#html-body [data-pb-style=ROVRVUH]{max-width:100%;height:auto}#html-body [data-pb-style=OP98IDA]{border-style:none}#html-body [data-pb-style=P6LTX25],#html-body [data-pb-style=QXXOREE]{max-width:100%;height:auto}#html-body [data-pb-style=DC0NII0]{border-style:none}#html-body [data-pb-style=B1FQNGV],#html-body [data-pb-style=JU4G97E]{max-width:100%;height:auto}#html-body [data-pb-style=QD9FKA7]{border-style:none}#html-body [data-pb-style=G8DO795],#html-body [data-pb-style=H44575M]{max-width:100%;height:auto}#html-body [data-pb-style=HE4TSW6]{border-style:none}#html-body [data-pb-style=LOLWKQM],#html-body [data-pb-style=R5OAX6J]{max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=DC0NII0],#html-body [data-pb-style=HE4TSW6],#html-body [data-pb-style=OP98IDA],#html-body [data-pb-style=QD9FKA7],#html-body [data-pb-style=VBK3KFL],#html-body [data-pb-style=W3BE4GR]{border-style:none} }</style>
<div class="home-top-cat mb-10 text-center" data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="AS1S47C">
<div class="pagebuilder-column-group sec-head" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="QCEONTH">
<div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="Y8JFJSB">
<div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="T8929K6">
<h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="PLNWLJ7">Shop our Top Categories</h2>
<div class="text-alt" data-content-type="text" data-appearance="default" data-element="main">
<p style="text-align: center;">Get inspired by our beautifully made art prints, posters and frames to help you create stylish gallery walls.</p>
</div>
</div>
</div>
</div>
<div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="QKLLGXT">
<div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="JM61RL0">
<div class="pagebuilder-column topcat-carousel" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="LH8L8TI">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="VBK3KFL"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/product-poster-frames.png}}" alt="Poster Frames" data-element="desktop_image" data-pb-style="BXCXD55"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/product-poster-frames.png}}" alt="Poster Frames" data-element="mobile_image" data-pb-style="RFEP1C2"></a>
<figcaption data-element="caption">Poster Frames</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="W3BE4GR"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/product-picture-frames.png}}" alt="Picture Frames" data-element="desktop_image" data-pb-style="NDV7YQG"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/product-picture-frames.png}}" alt="Picture Frames" data-element="mobile_image" data-pb-style="ROVRVUH"></a>
<figcaption data-element="caption">Picture Frames</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="OP98IDA"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/product-cork-frames.png}}" alt="Cork Frames" data-element="desktop_image" data-pb-style="P6LTX25"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/product-cork-frames.png}}" alt="Cork Frames" data-element="mobile_image" data-pb-style="QXXOREE"></a>
<figcaption data-element="caption">Cork Frames</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="DC0NII0"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/product-letter-frames.png}}" alt="Letter Frames" data-element="desktop_image" data-pb-style="B1FQNGV"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/product-letter-frames.png}}" alt="Letter Frames" data-element="mobile_image" data-pb-style="JU4G97E"></a>
<figcaption data-element="caption">Letter Frames</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="QD9FKA7"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/product-poster-frames.png}}" alt="Poster Frames" data-element="desktop_image" data-pb-style="H44575M"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/product-poster-frames.png}}" alt="Poster Frames" data-element="mobile_image" data-pb-style="G8DO795"></a>
<figcaption data-element="caption">Poster Frames</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="HE4TSW6"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/product-picture-frames.png}}" alt="Picture Frames" data-element="desktop_image" data-pb-style="LOLWKQM"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/product-picture-frames.png}}" alt="Picture Frames" data-element="mobile_image" data-pb-style="R5OAX6J"></a>
<figcaption data-element="caption">Picture Frames</figcaption>
</figure>
</div>
</div>
</div>
<div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="EJLTEEA">
<div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="I15DGTS"><a class="pagebuilder-button-primary" href="#" target="" data-link-type="default" data-element="link" data-pb-style="SK5CWFR"><span data-element="link_text">View More</span></a></div>
</div>
</div>
<div data-content-type="html" data-appearance="default" data-element="main">&lt;script&gt; require(["jquery","slick"], function($){ $(document).ready(function(){ $(".topcat-carousel").slick({ dots: false, infinite: false, speed: 300, slidesToShow: 4, slidesToScroll: 4, responsive: [ { breakpoint: 1460, settings: { slidesToShow: 4.2, slidesToScroll: 3 } }, { breakpoint: 1024, settings: { slidesToShow: 3.2, slidesToScroll: 3 } }, { breakpoint: 767, settings: { slidesToShow: 2.1, slidesToScroll: 2, centerMode: true, centerPadding: "5%", infinite: true, } }, { breakpoint: 560, settings: { slidesToShow: 1, slidesToScroll: 1, centerMode: true, centerPadding: "15%", infinite: true } } ] }); }); }); &lt;/script&gt;</div>'
            ],
            [
                'title' => 'Home / Shop By Theme old',
                'identifier' => 'home-shopbytheme-old',
                'content' => '<style>#html-body [data-pb-style=W244TWG],#html-body [data-pb-style=W5TB3YU]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=W5TB3YU]{justify-content:flex-start;display:flex;flex-direction:column}#html-body [data-pb-style=W244TWG]{align-self:stretch}#html-body [data-pb-style=DYVCB6D]{display:flex;width:100%}#html-body [data-pb-style=PD1DE6F]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:100%;align-self:stretch}#html-body [data-pb-style=UWNKNGR]{text-align:center}#html-body [data-pb-style=T2U8QMJ]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=FYP104R]{display:flex;width:100%}#html-body [data-pb-style=AEJ6HUP]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:33.3333%;align-self:stretch}#html-body [data-pb-style=B1X70AG]{border-style:none}#html-body [data-pb-style=NFSNQWC],#html-body [data-pb-style=XDY46D4]{max-width:100%;height:auto}#html-body [data-pb-style=B2U3BEY]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:66.6667%;align-self:stretch}#html-body [data-pb-style=N1L694E]{border-style:none}#html-body [data-pb-style=FW3TUI5],#html-body [data-pb-style=HCHTW2V]{max-width:100%;height:auto}#html-body [data-pb-style=HKC1H3Y]{border-style:none}#html-body [data-pb-style=K825GK2],#html-body [data-pb-style=VXHRBIY]{max-width:100%;height:auto}#html-body [data-pb-style=D95PW7W]{border-style:none}#html-body [data-pb-style=JMK5VXI],#html-body [data-pb-style=WLLOH9F]{max-width:100%;height:auto}#html-body [data-pb-style=LMLJ7QU]{border-style:none}#html-body [data-pb-style=FF79NWW],#html-body [data-pb-style=HAOSBBT]{max-width:100%;height:auto}#html-body [data-pb-style=YW6ODO3]{text-align:center}#html-body [data-pb-style=WNQO1O0]{display:inline-block}#html-body [data-pb-style=RIB6EVI]{text-align:center}@media only screen and (max-width: 768px) { #html-body [data-pb-style=B1X70AG],#html-body [data-pb-style=D95PW7W],#html-body [data-pb-style=HKC1H3Y],#html-body [data-pb-style=LMLJ7QU],#html-body [data-pb-style=N1L694E]{border-style:none} }</style>
<div class="home-by-thme mb-10 text-center" data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="W5TB3YU">
<div class="pagebuilder-column-group sec-head" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="W244TWG">
<div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="DYVCB6D">
<div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="PD1DE6F">
<h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="UWNKNGR">Shop by Theme</h2>
<div class="text-alt" data-content-type="text" data-appearance="default" data-element="main">
<p id="CJR9VHC" style="text-align: center;">Our display frames include simple photo picture frames, XL large, deep, lighted shadowbox display cases with shelves.<br>So, whatever you have; a printed sheet, dimensional items, objects or artwork.</p>
</div>
</div>
</div>
</div>
<div class="pagebuilder-column-group bytheme-grid" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="T2U8QMJ">
<div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="FYP104R">
<div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="AEJ6HUP">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="B1X70AG"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/008d7317-dbe5-4d62-b3da-fcba0171958f.png}}" alt="Poster Frames" data-element="desktop_image" data-pb-style="XDY46D4"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/008d7317-dbe5-4d62-b3da-fcba0171958f.png}}" alt="Poster Frames" data-element="mobile_image" data-pb-style="NFSNQWC"></a>
<figcaption data-element="caption">Sports &amp; Collectibles</figcaption>
</figure>
</div>
<div class="pagebuilder-column theme-collage" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="B2U3BEY">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="N1L694E"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/mat_frames-label7.2_1.png}}" alt="Poster Frames" data-element="desktop_image" data-pb-style="FW3TUI5"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/mat_frames-label7.2_1.png}}" alt="Poster Frames" data-element="mobile_image" data-pb-style="HCHTW2V"></a>
<figcaption data-element="caption">Movies &amp; Theatres</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="HKC1H3Y"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/portrait.png}}" alt="Poster Frames" data-element="desktop_image" data-pb-style="VXHRBIY"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/portrait.png}}" alt="Poster Frames" data-element="mobile_image" data-pb-style="K825GK2"></a>
<figcaption data-element="caption">Newspaper Frames</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="D95PW7W"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/20x20_frame_1.png}}" alt="Poster Frames" data-element="desktop_image" data-pb-style="WLLOH9F"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/20x20_frame_1.png}}" alt="Poster Frames" data-element="mobile_image" data-pb-style="JMK5VXI"></a>
<figcaption data-element="caption">Newspaper Frames</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="LMLJ7QU"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/24x60_landscape.png}}" alt="Poster Frames" data-element="desktop_image" data-pb-style="FF79NWW"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/24x60_landscape.png}}" alt="Poster Frames" data-element="mobile_image" data-pb-style="HAOSBBT"></a>
<figcaption data-element="caption">Newspaper Frames</figcaption>
</figure>
</div>
</div>
</div>
<div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="YW6ODO3">
<div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="WNQO1O0"><a class="pagebuilder-button-primary" href="#" target="" data-link-type="default" data-element="link" data-pb-style="RIB6EVI"><span data-element="link_text">Explore Now</span></a></div>
</div>
</div>'
            ],
            [
                'title' => 'Home / Shop By Theme',
                'identifier' => 'home-shopbytheme',
                'content' => '<style>#html-body [data-pb-style=FWB8R5C],#html-body [data-pb-style=QWMDWQJ]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=QWMDWQJ]{justify-content:flex-start;display:flex;flex-direction:column}#html-body [data-pb-style=FWB8R5C]{align-self:stretch}#html-body [data-pb-style=Y5LWEOB]{display:flex;width:100%}#html-body [data-pb-style=NVF22RW]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:100%;align-self:stretch}#html-body [data-pb-style=PFI0TML]{text-align:center}#html-body [data-pb-style=ISCEAN1]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=JXIACBU]{display:flex;width:100%}#html-body [data-pb-style=P3GUF0S]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:100%;align-self:stretch}#html-body [data-pb-style=TL0KOGV]{border-style:none}#html-body [data-pb-style=B9RC3YR],#html-body [data-pb-style=DAWDMFJ]{max-width:100%;height:auto}#html-body [data-pb-style=VBXYVX4]{border-style:none}#html-body [data-pb-style=HB8HIPT],#html-body [data-pb-style=R13OFVV]{max-width:100%;height:auto}#html-body [data-pb-style=NHR8M31]{border-style:none}#html-body [data-pb-style=CCL041A],#html-body [data-pb-style=LTVHAJ7]{max-width:100%;height:auto}#html-body [data-pb-style=XQU9I49]{border-style:none}#html-body [data-pb-style=DRDEWSM],#html-body [data-pb-style=W445C9M]{max-width:100%;height:auto}#html-body [data-pb-style=KP93A3R]{border-style:none}#html-body [data-pb-style=CBD4WJP],#html-body [data-pb-style=VARBIAD]{max-width:100%;height:auto}#html-body [data-pb-style=TIX6EXY]{border-style:none}#html-body [data-pb-style=CJKECE7],#html-body [data-pb-style=V8KGOF1]{max-width:100%;height:auto}#html-body [data-pb-style=C9D4YCC]{text-align:center}#html-body [data-pb-style=G5IGIQD]{display:inline-block}#html-body [data-pb-style=UCK1EJK]{text-align:center}@media only screen and (max-width: 768px) { #html-body [data-pb-style=KP93A3R],#html-body [data-pb-style=NHR8M31],#html-body [data-pb-style=TIX6EXY],#html-body [data-pb-style=TL0KOGV],#html-body [data-pb-style=VBXYVX4],#html-body [data-pb-style=XQU9I49]{border-style:none} }</style>
<div class="home-bytheme mb-10 text-center" data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="QWMDWQJ">
<div class="pagebuilder-column-group sec-head" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="FWB8R5C">
<div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="Y5LWEOB">
<div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="NVF22RW">
<h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="PFI0TML">Shop by Theme</h2>
<div class="text-alt" data-content-type="text" data-appearance="default" data-element="main">
<p id="G9K1X4G" style="text-align: center;">Our display frames include simple photo picture frames, XL large, deep, lighted shadowbox display cases with shelves.<br>So, whatever you have; a printed sheet, dimensional items, objects or artwork.</p>
</div>
</div>
</div>
</div>
<div class="pagebuilder-column-group fullbleed mb-6" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="ISCEAN1">
<div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="JXIACBU">
<div class="pagebuilder-column theme-carousel py-5 mb-4" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="P3GUF0S">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="TL0KOGV"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/product-poster-frames.png}}" alt="Poster Frames" data-element="desktop_image" data-pb-style="DAWDMFJ"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/product-poster-frames.png}}" alt="Poster Frames" data-element="mobile_image" data-pb-style="B9RC3YR"></a>
<figcaption data-element="caption">Poster Frames</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="VBXYVX4"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/product-picture-frames.png}}" alt="Picture Frames" data-element="desktop_image" data-pb-style="HB8HIPT"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/product-picture-frames.png}}" alt="Picture Frames" data-element="mobile_image" data-pb-style="R13OFVV"></a>
<figcaption data-element="caption">Picture Frames</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="NHR8M31"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/product-cork-frames.png}}" alt="Cork Frames" data-element="desktop_image" data-pb-style="LTVHAJ7"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/product-cork-frames.png}}" alt="Cork Frames" data-element="mobile_image" data-pb-style="CCL041A"></a>
<figcaption data-element="caption">Cork Frames</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="XQU9I49"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/product-letter-frames.png}}" alt="Letter Frames" data-element="desktop_image" data-pb-style="DRDEWSM"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/product-letter-frames.png}}" alt="Letter Frames" data-element="mobile_image" data-pb-style="W445C9M"></a>
<figcaption data-element="caption">Letter Frames</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="KP93A3R"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/product-poster-frames.png}}" alt="Poster Frames" data-element="desktop_image" data-pb-style="VARBIAD"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/product-poster-frames.png}}" alt="Poster Frames" data-element="mobile_image" data-pb-style="CBD4WJP"></a>
<figcaption data-element="caption">Poster Frames</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="TIX6EXY"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/product-picture-frames.png}}" alt="Picture Frames" data-element="desktop_image" data-pb-style="CJKECE7"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/product-picture-frames.png}}" alt="Picture Frames" data-element="mobile_image" data-pb-style="V8KGOF1"></a>
<figcaption data-element="caption">Picture Frames</figcaption>
</figure>
</div>
</div>
</div>
<div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="C9D4YCC">
<div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="G5IGIQD"><a class="pagebuilder-button-primary" href="#" target="" data-link-type="default" data-element="link" data-pb-style="UCK1EJK"><span data-element="link_text">View More</span></a></div>
</div>
</div>
<div data-content-type="html" data-appearance="default" data-element="main">&lt;script&gt; require(["jquery","slick"], function($){ $(document).ready(function(){ $(".theme-carousel").slick({ dots: false, infinite: false, speed: 300, slidesToShow: 4, slidesToScroll: 4, responsive: [ { breakpoint: 1460, settings: { slidesToShow: 4.2, slidesToScroll: 3 } }, { breakpoint: 1024, settings: { slidesToShow: 3.2, slidesToScroll: 3 } }, { breakpoint: 767, settings: { slidesToShow: 2.1, slidesToScroll: 2, centerMode: true, centerPadding: "5%", infinite: true, } }, { breakpoint: 560, settings: { slidesToShow: 1, slidesToScroll: 1, centerMode: true, centerPadding: "15%", infinite: true } } ] }); }); }); &lt;/script&gt;</div>'
            ],
            [
                'title' => 'Home / Get Creative',
                'identifier' => 'home-get-creative',
                'content' => '<style>#html-body [data-pb-style=DDYU5H7],#html-body [data-pb-style=HFX46ES]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=HFX46ES]{justify-content:flex-start;display:flex;flex-direction:column}#html-body [data-pb-style=DDYU5H7]{align-self:stretch}#html-body [data-pb-style=UK3ARCH]{display:flex;width:100%}#html-body [data-pb-style=PWGXCSB]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:100%;align-self:stretch}#html-body [data-pb-style=AM5PUEL]{text-align:center}#html-body [data-pb-style=PPKWVQC]{display:flex;width:100%}#html-body [data-pb-style=GCANKNO]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:100%;align-self:stretch}#html-body [data-pb-style=E4QSKA8]{border-style:none}#html-body [data-pb-style=IA1WMAI],#html-body [data-pb-style=Q9JSYPW]{max-width:100%;height:auto}#html-body [data-pb-style=MV8DPMH]{border-style:none}#html-body [data-pb-style=D803TR5],#html-body [data-pb-style=VS64QHJ]{max-width:100%;height:auto}#html-body [data-pb-style=UXQFE6M]{border-style:none}#html-body [data-pb-style=A2EBHIY],#html-body [data-pb-style=R3D5JAH]{max-width:100%;height:auto}#html-body [data-pb-style=BNN60B3]{border-style:none}#html-body [data-pb-style=MNC3G99],#html-body [data-pb-style=VJHKNS0]{max-width:100%;height:auto}#html-body [data-pb-style=T3Y96MY]{border-style:none}#html-body [data-pb-style=AJIM7QP],#html-body [data-pb-style=M28MVYL]{max-width:100%;height:auto}#html-body [data-pb-style=VR643Q1]{border-style:none}#html-body [data-pb-style=EVBEWY4],#html-body [data-pb-style=NEXJB1N]{max-width:100%;height:auto}#html-body [data-pb-style=B4V404X]{text-align:center}#html-body [data-pb-style=LL83EKW]{display:inline-block}#html-body [data-pb-style=AQSJUOQ]{text-align:center}@media only screen and (max-width: 768px) { #html-body [data-pb-style=BNN60B3],#html-body [data-pb-style=E4QSKA8],#html-body [data-pb-style=MV8DPMH],#html-body [data-pb-style=T3Y96MY],#html-body [data-pb-style=UXQFE6M],#html-body [data-pb-style=VR643Q1]{border-style:none} }</style>
<div class="homeget-creative mb-10 text-center" data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="HFX46ES">
<div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="DDYU5H7">
<div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="UK3ARCH">
<div class="pagebuilder-column sec-head" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="PWGXCSB">
<h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="AM5PUEL">Some Ideas…Get Creative!</h2>
<div class="text-alt" data-content-type="text" data-appearance="default" data-element="main">
<p style="text-align: center;">Whatever your needs for commercial or home decor, we have the design solution for you!</p>
</div>
</div>
</div>
<div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="PPKWVQC">
<div class="pagebuilder-column cms-col-3" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="GCANKNO">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="E4QSKA8"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/Cafe_Letterboards.jpg}}" alt="Cafe Letterboards" data-element="desktop_image" data-pb-style="Q9JSYPW"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/Cafe_Letterboards.jpg}}" alt="Cafe Letterboards" data-element="mobile_image" data-pb-style="IA1WMAI"></a>
<figcaption data-element="caption">Cafe Letterboards</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="MV8DPMH"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/Light_Boxes.jpg}}" alt="Poster Frames" data-element="desktop_image" data-pb-style="D803TR5"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/Light_Boxes.jpg}}" alt="Poster Frames" data-element="mobile_image" data-pb-style="VS64QHJ"></a>
<figcaption data-element="caption">Light Boxes</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="UXQFE6M"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/Poster_SwingFrames.jpg}}" alt="Picture Frames" data-element="desktop_image" data-pb-style="R3D5JAH"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/Poster_SwingFrames.jpg}}" alt="Picture Frames" data-element="mobile_image" data-pb-style="A2EBHIY"></a>
<figcaption data-element="caption">Poster SwingFrames</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="BNN60B3"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/Shadow_Box_Display_Cases.jpg}}" alt="Poster Frames" data-element="desktop_image" data-pb-style="VJHKNS0"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/Shadow_Box_Display_Cases.jpg}}" alt="Poster Frames" data-element="mobile_image" data-pb-style="MNC3G99"></a>
<figcaption data-element="caption">Shadow Box Display Cases</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="T3Y96MY"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/Hanging_Menu_Frames.jpg}}" alt="Hanging Menu Frames" data-element="desktop_image" data-pb-style="M28MVYL"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/Hanging_Menu_Frames.jpg}}" alt="Hanging Menu Frames" data-element="mobile_image" data-pb-style="AJIM7QP"></a>
<figcaption data-element="caption">Hanging Menu Frames</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="VR643Q1"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/home/Sign_Holder_Frames.jpg}}" alt="Sign Holder Frames" data-element="desktop_image" data-pb-style="NEXJB1N"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/home/Sign_Holder_Frames.jpg}}" alt="Sign Holder Frames" data-element="mobile_image" data-pb-style="EVBEWY4"></a>
<figcaption data-element="caption">Sign Holder Frames</figcaption>
</figure>
</div>
</div>
</div>
<div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="B4V404X">
<div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="LL83EKW"><a class="pagebuilder-button-primary" href="X" target="" data-link-type="default" data-element="link" data-pb-style="AQSJUOQ"><span data-element="link_text">Explore Now</span></a></div>
</div>
</div>'
            ],
            [
                'title' => 'Home / Building Customer Relationships',
                'identifier' => 'home-BCR',
                'content' => '<style>#html-body [data-pb-style=J29V7LU],#html-body [data-pb-style=X51F12G]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=J29V7LU]{justify-content:flex-start;display:flex;flex-direction:column}#html-body [data-pb-style=X51F12G]{align-self:stretch}#html-body [data-pb-style=X1QXYXC]{display:flex;width:100%}#html-body [data-pb-style=HAWPMCX]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:100%;align-self:stretch}#html-body [data-pb-style=YKSCHVP]{text-align:center}#html-body [data-pb-style=HQVG7EE]{display:flex;width:100%}#html-body [data-pb-style=Q9CQG8O]{justify-content:flex-start;display:flex;flex-direction:column;width:100%}#html-body [data-pb-style=Q9CQG8O],#html-body [data-pb-style=T8YRHUK]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=HMAHXIU]{display:flex;width:100%}#html-body [data-pb-style=SR0K0JD]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:100%;align-self:stretch}#html-body [data-pb-style=BTNS0A6]{border-style:none}#html-body [data-pb-style=DP7IY2Q],#html-body [data-pb-style=EN1GLNR]{max-width:100%;height:auto}#html-body [data-pb-style=S9UL6G0]{border-style:none}#html-body [data-pb-style=BDMK3LG],#html-body [data-pb-style=JTYWJ46]{max-width:100%;height:auto}#html-body [data-pb-style=NUW3T2A]{border-style:none}#html-body [data-pb-style=P557FAN],#html-body [data-pb-style=WTJL5AP]{max-width:100%;height:auto}#html-body [data-pb-style=SP3WY92]{border-style:none}#html-body [data-pb-style=HI7B7FJ],#html-body [data-pb-style=Q2LKK83]{max-width:100%;height:auto}#html-body [data-pb-style=GPD8FW8]{border-style:none}#html-body [data-pb-style=AD464BX],#html-body [data-pb-style=O7KXE60]{max-width:100%;height:auto}#html-body [data-pb-style=WS8K8B2]{border-style:none}#html-body [data-pb-style=E3GTX5U],#html-body [data-pb-style=NSVMBS3]{max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=BTNS0A6],#html-body [data-pb-style=GPD8FW8],#html-body [data-pb-style=NUW3T2A],#html-body [data-pb-style=S9UL6G0],#html-body [data-pb-style=SP3WY92],#html-body [data-pb-style=WS8K8B2]{border-style:none} }</style>
<div data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="J29V7LU">
<div class="pagebuilder-column-group bcr-content full-bleed bg-secondary" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="X51F12G">
<div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="X1QXYXC">
<div class="pagebuilder-column sec-head" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="HAWPMCX">
<h2 class="mb-0" data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="YKSCHVP">Building Customer Relationships</h2>
</div>
</div>
<div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="HQVG7EE">
<div class="pagebuilder-column bcr-for" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="Q9CQG8O">
<div data-content-type="text" data-appearance="default" data-element="main">
<div><img id="SYW9KBY" style="width: 490px; height: 372px;" src="{{media url=".renditions/wysiwyg/home/testimonial-1.jpg"}}" alt="" width="490" height="372"></div>
<blockquote>
<p>For the factory tour in Vermont, SwingFrames were added to Ben&nbsp;<br>and Jerry’s colorful, playful decor.&nbsp;<br>We designed our changeable poster SwingFrames with a blue&nbsp;<br>metal display frame for the top 10 most popular&nbsp;<br>ice cream flavors.</p>
<p><strong>Ben &amp; Jerry’s</strong></p>
</blockquote>
</div>
<div data-content-type="text" data-appearance="default" data-element="main">
<div><img id="SYW9KBY" src="{{media url=".renditions/wysiwyg/home/testimonial-1.jpg"}}" alt="" width="490" height="372"></div>
<blockquote>
<p>For the factory tour in Vermont, SwingFrames were added to Ben&nbsp;<br>and Jerry’s colorful, playful decor.&nbsp;<br>We designed our changeable poster SwingFrames with a blue&nbsp;<br>metal display frame for the top 10 most popular&nbsp;<br>ice cream flavors.</p>
<p><strong>Ben &amp; Jerry’s</strong></p>
</blockquote>
</div>
<div data-content-type="text" data-appearance="default" data-element="main">
<div id="FKTB897"><img id="SYW9KBY" src="{{media url=".renditions/wysiwyg/home/testimonial-1.jpg"}}" alt="" width="490" height="372"></div>
<blockquote>
<p>For the factory tour in Vermont, SwingFrames were added to Ben&nbsp;<br>and Jerry’s colorful, playful decor.&nbsp;<br>We designed our changeable poster SwingFrames with a blue&nbsp;<br>metal display frame for the top 10 most popular&nbsp;<br>ice cream flavors.</p>
<p><strong>Ben &amp; Jerry’s</strong></p>
</blockquote>
</div>
<div data-content-type="text" data-appearance="default" data-element="main">
<div><img id="SYW9KBY" src="{{media url=".renditions/wysiwyg/home/testimonial-1.jpg"}}" alt="" width="490" height="372"></div>
<blockquote>
<p>For the factory tour in Vermont, SwingFrames were added to Ben&nbsp;<br>and Jerry’s colorful, playful decor.&nbsp;<br>We designed our changeable poster SwingFrames with a blue&nbsp;<br>metal display frame for the top 10 most popular&nbsp;<br>ice cream flavors.</p>
<p><strong>Ben &amp; Jerry’s</strong></p>
</blockquote>
</div>
<div data-content-type="text" data-appearance="default" data-element="main">
<div><img id="SYW9KBY" src="{{media url=".renditions/wysiwyg/home/testimonial-1.jpg"}}" alt="" width="490" height="372"></div>
<blockquote>
<p>For the factory tour in Vermont, SwingFrames were added to Ben&nbsp;<br>and Jerry’s colorful, playful decor.&nbsp;<br>We designed our changeable poster SwingFrames with a blue&nbsp;<br>metal display frame for the top 10 most popular&nbsp;<br>ice cream flavors.</p>
<p><strong>Ben &amp; Jerry’s</strong></p>
</blockquote>
</div>
<div data-content-type="text" data-appearance="default" data-element="main">
<div><img id="SYW9KBY" src="{{media url=".renditions/wysiwyg/home/testimonial-1.jpg"}}" alt="" width="490" height="372"></div>
<blockquote>
<p>For the factory tour in Vermont, SwingFrames were added to Ben&nbsp;<br>and Jerry’s colorful, playful decor.&nbsp;<br>We designed our changeable poster SwingFrames with a blue&nbsp;<br>metal display frame for the top 10 most popular&nbsp;<br>ice cream flavors.</p>
<p><strong>Ben &amp; Jerry’s</strong></p>
</blockquote>
</div>
</div>
</div>
</div>
<div class="pagebuilder-column-group bcr-brands my-5 text-center" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="T8YRHUK">
<div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="HMAHXIU">
<div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="SR0K0JD">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="BTNS0A6"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/BenJerry_Home_Page_Logo_1_1.jpg}}" alt="" data-element="desktop_image" data-pb-style="DP7IY2Q"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/BenJerry_Home_Page_Logo_1_1.jpg}}" alt="" data-element="mobile_image" data-pb-style="EN1GLNR"></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="S9UL6G0"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/Annie_Sez_Home_Page_Logo.jpg}}" alt="" data-element="desktop_image" data-pb-style="JTYWJ46"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/Annie_Sez_Home_Page_Logo.jpg}}" alt="" data-element="mobile_image" data-pb-style="BDMK3LG"></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="NUW3T2A"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/1800_Flowers_Home_Page_Logo.jpg}}" alt="" data-element="desktop_image" data-pb-style="P557FAN"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/1800_Flowers_Home_Page_Logo.jpg}}" alt="" data-element="mobile_image" data-pb-style="WTJL5AP"></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="SP3WY92"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/2nd_City_1.png}}" alt="" data-element="desktop_image" data-pb-style="HI7B7FJ"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/2nd_City_1.png}}" alt="" data-element="mobile_image" data-pb-style="Q2LKK83"></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="GPD8FW8"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/Hyatt_Home_Page_Logo.jpg}}" alt="" data-element="desktop_image" data-pb-style="AD464BX"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/Hyatt_Home_Page_Logo.jpg}}" alt="" data-element="mobile_image" data-pb-style="O7KXE60"></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="WS8K8B2"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/Six_Flags_1.png}}" alt="" data-element="desktop_image" data-pb-style="NSVMBS3"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/Six_Flags_1.png}}" alt="" data-element="mobile_image" data-pb-style="E3GTX5U"></figure>
</div>
</div>
</div>
</div>
<div data-content-type="html" data-appearance="default" data-element="main">&lt;script&gt; require(["jquery","slick"], function($){ $(".bcr-content .bcr-for").slick({ centerMode: true, centerPadding: "13%", slidesToShow: 1, asNavFor: ".bcr-brands .pagebuilder-column", responsive: [ { breakpoint: 1700, settings: { centerPadding: "10%" } }, { breakpoint: 1700, settings: { centerPadding: "10%" } }, { breakpoint: 1400, settings: { centerPadding: "8%" } }, { breakpoint: 1200, settings: { centerPadding: "5%" } }, { breakpoint: 767, settings: { arrows: false, centerMode: false, centerPadding: "0" } } ] }); $(".bcr-brands .pagebuilder-column").slick({ dots: false, speed: 300, slidesToShow: 6, slidesToScroll: 1, asNavFor: ".bcr-content .bcr-for", focusOnSelect: true, responsive: [ { breakpoint: 767, settings: { slidesToShow: 1, centerMode: true, arrows: false, centerPadding: "30%" } }, { breakpoint: 420, settings: { slidesToShow: 1, centerMode: true, arrows: false, centerPadding: "25%" } } ] }); }); &lt;/script&gt; &lt;style&gt; @media(max-width:767px){ .bcr-brands { margin-left: -15px; margin-right: -15px; } .bcr-content.full-bleed { padding-left: 20px; padding-right: 20px; } .bcr-content div[data-content-type="text"]{ flex-wrap: wrap; } .bcr-content div[data-content-type="text"]&gt;div{ width: 100%; } .bcr-content img{ width: 100%!important; height: auto!important; } .bcr-content blockquote { width: 100%; margin: 0; padding-top: 20px; } } &lt;/style&gt;</div>'
            ]

        ];
    }
}
