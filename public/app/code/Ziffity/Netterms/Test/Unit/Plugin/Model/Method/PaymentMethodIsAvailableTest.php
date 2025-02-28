<?php
declare(strict_types=1);

namespace Ziffity\Netterms\Test\Unit\Plugin\Model\Method;

use Ziffity\Netterms\Plugin\Model\Method\PaymentMethodIsAvailable;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Company\Model\CompanyUserPermission;
use Magento\Customer\Model\Session as CustomerSession;
use Ziffity\Netterms\Model\Netterms;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentMethodIsAvailableTest extends TestCase
{
    /**
     * Result of 'proceed' closure call
     */
    const PROCEED_RESULT = 'proceed';

    /**
     * @var PaymentMethodIsAvailable
     */
    private $plugin;

    /**
     * @var CustomerSession|MockObject
     */
    private $customerSessionMock;

    /**
     * @var Netterms|MockObject
     */
    private $subject;

    /**
     * @var \Closure
     */
    private $proceed;

    /**
     * @var CartInterface|MockObject
     */
    private $quote;

    protected function setUp(): void
    {
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->subject = $this->createMock(Netterms::class);
        $this->proceed = function () {
            return self::PROCEED_RESULT;
        };

        $this->quote = $this->getMockForAbstractClass(CartInterface::class);

        $this->plugin = new PaymentMethodIsAvailable( $this->customerSessionMock );
    }

    /**
     * @covers \Ziffity\Netterms\Plugin\Model\Method\PaymentMethodIsAvailable::aroundIsAvailable
     * @return void
     */
    public function testAroundIsAvailableForStoreFront()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerData')
            ->willReturnSelf();
        $this->assertSame(
            self::PROCEED_RESULT,
            $this->plugin->aroundIsAvailable(
                $this->subject,
                $this->proceed
            )
        );
    }

    /**
     * @covers \Ziffity\Netterms\Plugin\Model\Method\PaymentMethodIsAvailable::aroundIsAvailable
     * @return void
     */
    public function testAroundIsAvailableForAdminhtml()
    {
        $this->quote->expects($this->once())
            ->method('getCustomer')
            ->willReturnSelf();
        $this->assertSame(
            self::PROCEED_RESULT,
            $this->plugin->aroundIsAvailable(
                $this->subject,
                $this->proceed,
                $this->quote
            )
        );
    }
}
