<?php
declare(strict_types=1);

namespace Ziffity\Netterms\Test\Unit\Helper;

use Magento\Framework\UrlInterface;
use Magento\TestFramework\Mail\TransportInterfaceMock;
use Ziffity\Netterms\Helper\Data;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\View\Element\Template;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var TransportBuilder|MockObject
     */
    private $transportBuilderMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StateInterface|MockObject
     */
    private $inlineTranslationMock;

    /**
     * @var Template|MockObject
     */
    private $blockTemplateMock;

    /**
     * @var MockObject
     */
    private $scopeConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $className = Data::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var Context $context */
        $context = $arguments['context'];

        $this->scopeConfig = $context->getScopeConfig();

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        'payment/netterms/email_template',
                        ScopeInterface::SCOPE_STORE,
                        8,
                        'payment_us_netterms_email_template',
                    ],
                    [
                        'trans_email/ident_sales/name',
                        ScopeInterface::SCOPE_STORE,
                        8,
                        'Sales',
                    ],
                    [
                        'trans_email/ident_sales/email',
                        ScopeInterface::SCOPE_STORE,
                        8,
                        'sales@example.com',
                    ],
                    [
                        'payment/netterms/pdf',
                        ScopeInterface::SCOPE_STORE,
                        8,
                        'default/Credit_Application_and_W-9_Form.pdf',
                    ]
                ]
            );

        $this->transportBuilderMock = $this->getTransportBuilder();
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeMock = $this->createMock(Store::class);

        $this->inlineTranslationMock = $this->getMockForAbstractClass(StateInterface::class);
        $this->blockTemplateMock = $this->createMock(Template::class);

        $this->helper = new Data(
            $context,
            $this->transportBuilderMock,
            $this->storeManagerMock,
            $this->inlineTranslationMock,
            $this->blockTemplateMock
        );
    }

    /**
     * @covers \Ziffity\Netterms\Observer\UpdateNettermsCredit::sentNonApprovedEmail
     * @dataProvider getOrderData
     * @return void
     */
    public function testSentNonApprovedEmail($customerEmail, $orderData)
    {
        $baseUrl = 'http://magento.local/media/';
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->with(
                UrlInterface::URL_TYPE_MEDIA
            )
            ->willReturn($baseUrl);

        $transport = $this->getMockBuilder(TransportInterfaceMock::class)
            ->setMethods(['sendMessage'])
            ->getMock();
        $this->transportBuilderMock->expects($this->any())
            ->method('getTransport')
            ->willReturn($transport);
        $transport->expects($this->exactly(1))
            ->method('sendMessage');
        $this->helper->sentNonApprovedEmail($customerEmail, $orderData);
    }

    /**
     * @return array
     */
    public function getOrderData(): array
    {
        return [
            [
                'customer@example.com',
                [
                    'increment_id' => '000000001',
                    'customer_name' => 'Some Customer Name',
                    'store_id' => '1',
                    'application_url' => 'Some url for pdf'
                ],
            ],
            [
                'customer2@example.com',
                [
                    'increment_id' => '000000002',
                    'customer_name' => 'Some Customer2 Name',
                    'store_id' => '1',
                    'application_url' => 'Some url for pdf'
                ],
            ]
        ];
    }

    /**
     * @return TransportBuilder
     */
    private function getTransportBuilder(): TransportBuilder
    {
        $transportBuilder = $this->createMock(TransportBuilder::class);

        $methods = [
            'setTemplateIdentifier',
            'setTemplateOptions',
            'setTemplateVars',
            'setFrom',
            'addTo'
        ];

        foreach ($methods as $method) {
            $transportBuilder->expects($this->any())
                ->method($method)
                ->willReturn($transportBuilder);
        }

        return $transportBuilder;
    }
}
