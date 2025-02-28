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
 * Class CmsBlocks
 *
 * Create CMS Block for Homepage Links
 */
class CmsBlocks implements DataPatchInterface
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
                'title' => 'Header/ Promo',
                'identifier' => 'header-promo',
                'content' => <<<HTML
    <div class="header-promo text-center">
        <span>FREE GROUND SHIPPING on most Snap Frame Orders</span>
        <span class="close dficon-x">&nbsp;</span>
    </div>
    HTML
            ],
            [
                'title' => 'Home/ Hero',
                'identifier' => 'home-hero',
                'content' => <<<HTML
    {{block class="Nwdthemes\Revslider\Block\Revslider" alias="home-her0"}}
    HTML
            ],
            [
                'title' => 'PDP / Tour',
                'identifier' => 'pdp-tour',
                'content' => <<<HTML
<div class="d-none"><ol id="tourTipContent"><li data-class="save-design" class="sd-tip" data-options="tipLocation:top;tipAnimation:fade" >
<p>To save current design</p></li><li data-class="table-selected" class="sd-tip" data-options="tipLocation:top;tipAnimation:fade" data-button="Close" ><p>Selected Options</p></li>
</ol>
<script>
require([
    'jquery',
    'joyride'
], function($) {
    // joyride for tour
    $('.tour').on('click', function() {
        $('#tourTipContent').joyride({
            autoStart: true,
            modal: true,
            expose: false
        });
    });
});</script></div>
HTML
            ],
            [
                'title' => 'Footer / Links',
                'identifier' => 'footer-links',
                'content' => <<<HTML
    <div class="d-md-flex flex-bw">
        <div>
            <h5>Products</h5>
            <ul>
                <li><a href="#" title="All Products">All Products</a></li>
                <li><a href="#" title="Frame Type">Frame Type</a></li>
                <li><a href="#" title="Theme">Theme</a></li>


            </ul>
        </div>
        <div>
            <h5>Company</h5>
            <ul>

                <li><a href="#" title="About Us">About Us</a></li>
                <li><a href="#" title="Our Story">Our Story</a></li>
                <li><a href="#" title="Business Services">Business Services</a></li>
                <li><a href="#" title="Purchase Orders Welcome">Purchase Orders Welcome</a></li>
                <li><a href="#" title="Quotes & Estimates">Quotes & Estimates</a></li>
                <li><a href="#" title="Resources">Resources</a></li>
                <li><a href="#" title="View our Display Shop">View our Display Shop</a></li>
            </ul>
        </div>
        <div>
            <h5>Customer Services</h5>
            <ul>
                <li><a href="#" title="Ordering Information">Ordering Information</a></li>
                <li><a href="#" title="Shipping Information">Shipping Information</a></li>
                <li><a href="#" title="Track Order/Order Status">Track Order/Order Status</a></li>
                <li><a href="#" title="Returns">Returns</a></li>
                <li><a href="#" title="International Orders">International Orders</a></li>
                <li><a href="#" title="International Orders">Saved Designs</a></li>
            </ul>
        </div>
        <div>
            <h5>Contact</h5>
            <ul>
                <li><a href="#" title="Contact Us">Contact Us</a></li>
            </ul>
        </div>
        <div class="follow-us">
            <h5>Follow Us</h5>
            <div>
            <a href="#" title="you-tube"><i class="dficon-youtube1"><span>you-tube</span></i></a>
            <a href="#" title="instagram"><i class="dficon-instagram"><span>instagram</span></i></a>
            <a href="#" title="linkedin"><i class="dficon-linkedin1"><span>Linkedin</span></i></a>
            <a href="#" title="Facebook"><i class="dficon-fb1"><span>Facebook</span></i></a>
            <a href="#" title="twitter"><i class="dficon-twitter1"><span>twitter</span></i></a>
            <a href="#" title="pinterest"><i class="dficon-pinterest"><span>pinterest</span></i></a>
            </div>
        </div>
    </div>
    HTML
            ],
            [
                'title' => 'Footer / Logos',
                'identifier' => 'footer-logos',
                'content' =>
                '<style>#html-body [data-pb-style=EFUCS0L]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=H02TRYU]{border-style:none}#html-body [data-pb-style=M06TK9N],#html-body [data-pb-style=PN8T3WY]{max-width:100%;height:auto}#html-body [data-pb-style=GGGCJA9]{border-style:none}#html-body [data-pb-style=BDNJ7OS],#html-body [data-pb-style=BDUELJY]{max-width:100%;height:auto}#html-body [data-pb-style=DYFC9IF]{border-style:none}#html-body [data-pb-style=S16YLCI],#html-body [data-pb-style=TWCH3HP]{max-width:100%;height:auto}#html-body [data-pb-style=RUG2AKD]{border-style:none}#html-body [data-pb-style=B402YDQ],#html-body [data-pb-style=VKEYAU6]{max-width:100%;height:auto}#html-body [data-pb-style=YGA62HL]{border-style:none}#html-body [data-pb-style=L78NQGC],#html-body [data-pb-style=QSX876P]{max-width:100%;height:auto}#html-body [data-pb-style=X0I22WF]{border-style:none}#html-body [data-pb-style=O9X6OTL],#html-body [data-pb-style=VRQ0UM6]{max-width:100%;height:auto}#html-body [data-pb-style=DHB8Y3X]{border-style:none}#html-body [data-pb-style=CQDCKWB],#html-body [data-pb-style=LFL66M0]{max-width:100%;height:auto}#html-body [data-pb-style=TBU2QS2]{border-style:none}#html-body [data-pb-style=AUY1G3F],#html-body [data-pb-style=SME2V9C]{max-width:100%;height:auto}#html-body [data-pb-style=G60LTSU]{border-style:none}#html-body [data-pb-style=KIFSCJY],#html-body [data-pb-style=NADW92P]{max-width:100%;height:auto}#html-body [data-pb-style=MRUQNW4]{border-style:none}#html-body [data-pb-style=KVGVBRH],#html-body [data-pb-style=NM406CQ]{max-width:100%;height:auto}#html-body [data-pb-style=OCI015L]{border-style:none}#html-body [data-pb-style=CRBO43Y],#html-body [data-pb-style=MBO3L4W]{max-width:100%;height:auto}#html-body [data-pb-style=MRNWFD1]{border-style:none}#html-body [data-pb-style=GMVBNAV],#html-body [data-pb-style=IVL3MW4]{max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=DHB8Y3X],#html-body [data-pb-style=DYFC9IF],#html-body [data-pb-style=G60LTSU],#html-body [data-pb-style=GGGCJA9],#html-body [data-pb-style=H02TRYU],#html-body [data-pb-style=MRNWFD1],#html-body [data-pb-style=MRUQNW4],#html-body [data-pb-style=OCI015L],#html-body [data-pb-style=RUG2AKD],#html-body [data-pb-style=TBU2QS2],#html-body [data-pb-style=X0I22WF],#html-body [data-pb-style=YGA62HL]{border-style:none} }</style>
<div data-content-type="row" data-appearance="contained" data-element="main">
<div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="EFUCS0L">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="H02TRYU"><a title="" href="https://www.bulletinboards4sale.com/" target="_blank" rel="noopener" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/logo_210x.png}}" alt="logo" data-element="desktop_image" data-pb-style="PN8T3WY"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/logo_210x.png}}" alt="logo" data-element="mobile_image" data-pb-style="M06TK9N"></a></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="GGGCJA9"><a title="" href="https://www.floorstands.com/" target="_blank" rel="noopener" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/floorstand.png}}" alt="logo" data-element="desktop_image" data-pb-style="BDNJ7OS"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/floorstand.png}}" alt="logo" data-element="mobile_image" data-pb-style="BDUELJY"></a></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="DYFC9IF"><a title="" href="https://www.letterboards4sale.com/" target="_blank" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/letterboard.png}}" alt="logo" data-element="desktop_image" data-pb-style="TWCH3HP"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/letterboard.png}}" alt="logo" data-element="mobile_image" data-pb-style="S16YLCI"></a></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="RUG2AKD"><a title="" href="https://www.lightboxes4sale.com/" target="_blank" rel="noopener" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/lightbox.png}}" alt="logo" data-element="desktop_image" data-pb-style="B402YDQ"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/lightbox.png}}" alt="logo" data-element="mobile_image" data-pb-style="VKEYAU6"></a></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="YGA62HL"><a title="" href="https://www.outdoordisplaycases.com/" target="_blank" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/outdoor.png}}" alt="logo" data-element="desktop_image" data-pb-style="L78NQGC"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/outdoor.png}}" alt="logo" data-element="mobile_image" data-pb-style="QSX876P"></a></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="X0I22WF"><a title="" href="https://www.posterdisplays4sale.com/" target="_blank" rel="noopener" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/posterdisplay.png}}" alt="logo" data-element="desktop_image" data-pb-style="VRQ0UM6"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/posterdisplay.png}}" alt="logo" data-element="mobile_image" data-pb-style="O9X6OTL"></a></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="DHB8Y3X"><a title="" href="https://www.shadowboxes.com/" target="_blank" rel="noopener" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/Shadowboxes.png}}" alt="logo" data-element="desktop_image" data-pb-style="LFL66M0"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/Shadowboxes.png}}" alt="logo" data-element="mobile_image" data-pb-style="CQDCKWB"></a></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="TBU2QS2"><a title="" href="https://www.snapframes4sale.com/" target="_blank" rel="noopener" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/Group-6_210x.png}}" alt="logo" data-element="desktop_image" data-pb-style="AUY1G3F"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/Group-6_210x.png}}" alt="logo" data-element="mobile_image" data-pb-style="SME2V9C"></a></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="G60LTSU"><a title="" href="https://www.swingframes4sale.com/" target="_blank" rel="noopener" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/swingframe.png}}" alt="logo" data-element="desktop_image" data-pb-style="NADW92P"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/swingframe.png}}" alt="logo" data-element="mobile_image" data-pb-style="KIFSCJY"></a></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="MRUQNW4"><a title="" href="https://www.swingpanels.com/" target="_blank" rel="noopener" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/Group-8_210x.png}}" alt="Logo" data-element="desktop_image" data-pb-style="NM406CQ"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/Group-8_210x.png}}" alt="Logo" data-element="mobile_image" data-pb-style="KVGVBRH"></a></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="OCI015L"><a title="" href="https://www.swingframe.com/" target="_blank" rel="noopener" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/SwingFrame.png}}" alt="logo" data-element="desktop_image" data-pb-style="CRBO43Y"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/SwingFrame.png}}" alt="logo" data-element="mobile_image" data-pb-style="MBO3L4W"></a></figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="MRNWFD1"><a title="" href="https://www.displays4sale.com/" target="_blank" rel="noopener" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=wysiwyg/Displays4Sale.png}}" alt="logo" data-element="desktop_image" data-pb-style="IVL3MW4"><img class="pagebuilder-mobile-only" title="" src="{{media url=wysiwyg/Displays4Sale.png}}" alt="logo" data-element="mobile_image" data-pb-style="GMVBNAV"></a></figure>
</div>
</div>'
            ],
            [
                'title' => 'Footer / Bottom Links',
                'identifier' => 'footer-bottom-links',
                'content' => <<<HTML
                <small><a href="#" title="Privacy Policy">Privacy Policy</a> <a href="#" title="Terms Of Use">Terms Of Use</a>
                </small>
    HTML
            ],
            [
                'title' => 'Footer /  info cards',
                'identifier' => 'footer-info-cards',
                'content' => '<style>#html-body [data-pb-style=FR0A3UA]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=IRR37RF]{display:flex;width:100%}#html-body [data-pb-style=L1WLAV3]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:25%;align-self:stretch}#html-body [data-pb-style=JHX190V]{border-style:none}#html-body [data-pb-style=G3TH1GE],#html-body [data-pb-style=N7K1DWG]{max-width:100%;height:auto}#html-body [data-pb-style=VLN5CF8]{display:inline-block}#html-body [data-pb-style=P95KID3]{text-align:center}#html-body [data-pb-style=HBMJ3F8]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:25%;align-self:stretch}#html-body [data-pb-style=IO8S0BM]{border-style:none}#html-body [data-pb-style=INLKY3U],#html-body [data-pb-style=RLTXC71]{max-width:100%;height:auto}#html-body [data-pb-style=R75M0R3]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:25%;align-self:stretch}#html-body [data-pb-style=NQ43Q5V]{border-style:none}#html-body [data-pb-style=CF6LDSW],#html-body [data-pb-style=F6M87LM]{max-width:100%;height:auto}#html-body [data-pb-style=E1RW127]{display:inline-block}#html-body [data-pb-style=MX7F20B]{text-align:center}#html-body [data-pb-style=TQ9JVLL]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:25%;align-self:stretch}#html-body [data-pb-style=Q669PVC]{border-style:none}#html-body [data-pb-style=S9NKCKD],#html-body [data-pb-style=TNWAB6T]{max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=IO8S0BM],#html-body [data-pb-style=JHX190V],#html-body [data-pb-style=NQ43Q5V],#html-body [data-pb-style=Q669PVC]{border-style:none} }</style>
<div class="pagebuilder-column-group footer-info-cards img-zoom cms-col-4 mb-8 text-alt" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="FR0A3UA">
<div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="IRR37RF">
<div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="L1WLAV3">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="JHX190V"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/footer/The_Swingframe_Advantage_copy.jpg}}" alt="" data-element="desktop_image" data-pb-style="G3TH1GE"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/footer/The_Swingframe_Advantage_copy.jpg}}" alt="" data-element="mobile_image" data-pb-style="N7K1DWG"></figure>
<div data-content-type="text" data-appearance="default" data-element="main">
<h5>The SwingFrame Advantage</h5>
<p>For over 25 years, thousands of Corporations, Retail Stores, Businesses, Organizations, Institutions…</p>
</div>
<div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main">
<div class="link-overlay" data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="VLN5CF8"><a class="pagebuilder-button-link" href="#" target="" data-link-type="default" data-element="link" data-pb-style="P95KID3"><span data-element="link_text">Read More</span></a></div>
</div>
</div>
<div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="HBMJ3F8">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="IO8S0BM"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/footer/Purchase_Orders.jpg}}" alt="" data-element="desktop_image" data-pb-style="RLTXC71"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/footer/Purchase_Orders.jpg}}" alt="" data-element="mobile_image" data-pb-style="INLKY3U"></figure>
<div data-content-type="text" data-appearance="default" data-element="main">
<h5>We accept Purchase Orders</h5>
<p>Lorem Ipsum has been the industry’s standard dummy text ever since the 1500s, when an unknown printer…</p>
</div>
</div>
<div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="R75M0R3">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="NQ43Q5V"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/footer/Customer_Support_copy.jpg}}" alt="" data-element="desktop_image" data-pb-style="F6M87LM"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/footer/Customer_Support_copy.jpg}}" alt="" data-element="mobile_image" data-pb-style="CF6LDSW"></figure>
<div data-content-type="text" data-appearance="default" data-element="main">
<h5>Customer Support</h5>
<p>24/7 Our product and customer service specialists are ready to assist you. Contact us via Email, Phone, Fax or Mail.</p>
</div>
<div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main">
<div class="link-overlay" data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="E1RW127"><a class="pagebuilder-button-link" href="#" target="" data-link-type="default" data-element="link" data-pb-style="MX7F20B"><span data-element="link_text">Contact</span></a></div>
</div>
</div>
<div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="TQ9JVLL">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="Q669PVC"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/footer/Quotes_welcomed_copy.jpg}}" alt="" data-element="desktop_image" data-pb-style="S9NKCKD"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/footer/Quotes_welcomed_copy.jpg}}" alt="" data-element="mobile_image" data-pb-style="TNWAB6T"></figure>
<div data-content-type="text" data-appearance="default" data-element="main">
<h5>Quotes Welcomed</h5>
<p>Lorem Ipsum has been the industry’s standard dummy text ever since the 1500s, when an unknown printer…</p>
</div>
</div>
</div>
</div>'
            ],
            [
                'title' => 'Footer / You Design it We Build It',
                'identifier' => 'footer-design-build',
                'content' => '<style>#html-body [data-pb-style=I7HIWR5]{justify-content:center;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;text-align:center}#html-body [data-pb-style=D1IPRW3]{display:inline-block}#html-body [data-pb-style=S9ATMMM]{text-align:center}</style>
                <div class="bg-primary-dark text-white full-bleed u-we-action mb-8" data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="I7HIWR5">
                <h3 data-content-type="heading" data-appearance="default" data-element="main">You Design it. We Build It!</h3>
                <div class="btn-outline-white" data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main">
                <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="D1IPRW3"><a class="pagebuilder-button-secondary" href="#" target="" data-link-type="default" data-element="link" data-pb-style="S9ATMMM"><span data-element="link_text">Let’s Get Started</span></a></div>
                </div>
                </div>'
            ],
            [
                'title' => 'CLP / Products',
                'identifier' => 'clp-products',
                'content' => '<style>#html-body [data-pb-style=ORIIKY6],#html-body [data-pb-style=TGTW6NJ]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=TGTW6NJ]{justify-content:flex-start;display:flex;flex-direction:column}#html-body [data-pb-style=ORIIKY6]{align-self:stretch}#html-body [data-pb-style=Y8DFL48]{display:flex;width:100%}#html-body [data-pb-style=C3EKOME]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:58.3333%;align-self:stretch}#html-body [data-pb-style=DEDR5KG]{border-style:none}#html-body [data-pb-style=D3DL6A4],#html-body [data-pb-style=P2R7NT8]{max-width:100%;height:auto}#html-body [data-pb-style=TE7M4RX]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:41.6667%;align-self:stretch}#html-body [data-pb-style=R78L0D1]{border-style:none}#html-body [data-pb-style=RKHWP3T],#html-body [data-pb-style=SOQHRGQ]{max-width:100%;height:auto}#html-body [data-pb-style=VP5RF25]{display:flex;width:100%}#html-body [data-pb-style=TE46LK5]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:58.3333%;align-self:stretch}#html-body [data-pb-style=DUC53E3]{border-style:none}#html-body [data-pb-style=KBWQTSI],#html-body [data-pb-style=M0V3TKQ]{max-width:100%;height:auto}#html-body [data-pb-style=NJO0XM3]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:41.6667%;align-self:stretch}#html-body [data-pb-style=OGJBA3Q]{border-style:none}#html-body [data-pb-style=LLMYRDK],#html-body [data-pb-style=PA5HJXR]{max-width:100%;height:auto}#html-body [data-pb-style=JO2TEB1]{border-style:none}#html-body [data-pb-style=J9LLECW],#html-body [data-pb-style=VWR2U5E]{max-width:100%;height:auto}#html-body [data-pb-style=S5WOVP3]{display:flex;width:100%}#html-body [data-pb-style=EDE4WSF]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:100%;align-self:stretch}#html-body [data-pb-style=CEFXL5G]{border-style:none}#html-body [data-pb-style=BDB1KMR],#html-body [data-pb-style=O2XF844]{max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=CEFXL5G],#html-body [data-pb-style=DEDR5KG],#html-body [data-pb-style=DUC53E3],#html-body [data-pb-style=JO2TEB1],#html-body [data-pb-style=OGJBA3Q],#html-body [data-pb-style=R78L0D1]{border-style:none} }</style>
<div data-content-type="row" data-appearance="contained" data-element="main">
<div class="figcaption" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="TGTW6NJ">
<div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="ORIIKY6">
<div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="Y8DFL48">
<div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="C3EKOME">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="DEDR5KG"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/clp/poster-frame.png}}" alt="Poster Frame" data-element="desktop_image" data-pb-style="P2R7NT8"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/clp/poster-frame.png}}" alt="Poster Frame" data-element="mobile_image" data-pb-style="D3DL6A4"></a>
<figcaption data-element="caption">Poster Frame</figcaption>
</figure>
</div>
<div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="TE7M4RX">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="R78L0D1"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/clp/picture-frame.png}}" alt="Picture Frame" data-element="desktop_image" data-pb-style="RKHWP3T"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/clp/picture-frame.png}}" alt="Picture Frame" data-element="mobile_image" data-pb-style="SOQHRGQ"></a>
<figcaption data-element="caption">Picture Frame</figcaption>
</figure>
</div>
</div>
<div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="VP5RF25">
<div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="TE46LK5">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="DUC53E3"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/clp/cork-boards.png}}" alt="Cork Boards" data-element="desktop_image" data-pb-style="KBWQTSI"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/clp/cork-boards.png}}" alt="Cork Boards" data-element="mobile_image" data-pb-style="M0V3TKQ"></a>
<figcaption data-element="caption">Cork Boards</figcaption>
</figure>
</div>
<div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="NJO0XM3">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="OGJBA3Q"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/clp/letter-boards.png}}" alt="Letter Boards" data-element="desktop_image" data-pb-style="LLMYRDK"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/clp/letter-boards.png}}" alt="Letter Boards" data-element="mobile_image" data-pb-style="PA5HJXR"></a>
<figcaption data-element="caption">Letter Boards</figcaption>
</figure>
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="JO2TEB1"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/clp/display-boards.png}}" alt="Display Boards" data-element="desktop_image" data-pb-style="VWR2U5E"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/clp/display-boards.png}}" alt="Display Boards" data-element="mobile_image" data-pb-style="J9LLECW"></a>
<figcaption data-element="caption">Display Boards</figcaption>
</figure>
</div>
</div>
<div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="S5WOVP3">
<div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="EDE4WSF">
<figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="CEFXL5G"><a title="" href="#" target="" data-link-type="default" data-element="link"><img class="pagebuilder-mobile-hidden" title="" src="{{media url=.renditions/wysiwyg/clp/shadow-boxes.png}}" alt="Shadow Boxes" data-element="desktop_image" data-pb-style="BDB1KMR"><img class="pagebuilder-mobile-only" title="" src="{{media url=.renditions/wysiwyg/clp/shadow-boxes.png}}" alt="Shadow Boxes" data-element="mobile_image" data-pb-style="O2XF844"></a>
<figcaption data-element="caption">Shadow Boxes</figcaption>
</figure>
</div>
</div>
</div>
</div>
</div>
<div data-content-type="html" data-appearance="default" data-element="main">&lt;style&gt; .page-title-wrapper { display: none; } .columns{ display: none !important } .category-description { max-width: 960px; padding-left: 15px; padding-right: 15px; margin: 50px auto; font-size: 16px; line-height: 1.8; } &lt;/style&gt;</div>'
            ]

        ];
    }
}
