<?php
declare(strict_types=1);

namespace Ziffity\Netterms\Test\Unit\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Ziffity\Netterms\Model\Netterms;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Payment\Model\InfoInterface;

class NettermsTest extends TestCase
{

    /**
     * @var Netterms
     */
    private $netterms;

    /**
     * @var PaymentMethodIsAvailable
     */
    private $plugin;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;


    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $className = Netterms::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        $context = $arguments['context'];
        $registry = $arguments['registry'];
        $extensionFactory = $arguments['extensionFactory'];
        $customAttributeFactory = $arguments['customAttributeFactory'];
        $paymentData = $arguments['paymentData'];
        $scopeConfig = $arguments['scopeConfig'];
        $logger = $arguments['logger'];
        $resource = $arguments['resource'];
        $resourceCollection = $arguments['resourceCollection'];
        $this->customerRepositoryMock = $this->createMock(
            CustomerRepositoryInterface::class
        );

        $this->netterms = new Netterms(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $this->customerRepositoryMock,
            $resource,
            $resourceCollection
        );
    }

    /**
     * @covers \Ziffity\Netterms\Model\Netterms::validate
     * @return void
     */
    public function testAssignData()
    {
        $inputData = new DataObject();
        $paymentInfoInstance = $this->getMockForAbstractClass(
            InfoInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getQuote']
        );

        $this->netterms->setInfoInstance($paymentInfoInstance);
        $quote = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $paymentInfoInstance->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quote);
        $this->netterms->assignData($inputData);
    }

    /**
     * @covers \Ziffity\Netterms\Model\Netterms::validate
     * @return void
     */
    public function testValidate()
    {
        $countryId = 'USA';
        $customerId = 1;
        $paymentInfoInstance = $this->getMockForAbstractClass(
            InfoInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getQuote']
        );
        $this->netterms->setInfoInstance($paymentInfoInstance);
        $quote = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCustomerId'])
            ->onlyMethods(['getBillingAddress'])
            ->disableOriginalConstructor()
            ->getMock();

        $addressMock = $this->createMock(Address::class);
        $quote->expects($this->once())->method('getBillingAddress')->willReturn($addressMock);
        $addressMock->expects(self::once())
            ->method('getCountryId')
            ->willReturn($countryId);
        $quote->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $paymentInfoInstance->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quote);

        $customerMock = $this->createMock(CustomerInterface::class);
        $this->customerRepositoryMock->expects($this->any())->method('getById')->with( $customerId )->willReturn($customerMock);

        $this->netterms->validate();
    }
}
