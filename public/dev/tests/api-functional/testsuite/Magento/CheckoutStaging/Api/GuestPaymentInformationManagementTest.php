<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutStaging\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\Staging\Model\VersionManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

class GuestPaymentInformationManagementTest extends WebapiAbstract
{
    private const SERVICE_VERSION = 'V1';
    private const SERVICE_NAME = 'checkoutGuestPaymentInformationManagementV1';
    private const RESOURCE_PATH = '/V1/guest-carts/%s/payment-information';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/CheckoutStaging/_files/quote_with_check_payment.php
     */
    public function testSavePaymentInformationAndPlaceOrderWithException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The order can\'t be submitted in preview mode.');

        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->markTestSkipped('Preview version should not works for SOAP Api');
        }

        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class)
            ->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $serviceInfo = [
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'savePaymentInformationAndPlaceOrder',
                'serviceVersion' => self::SERVICE_VERSION,
            ],
            'rest' => [
                'resourcePath' => sprintf(self::RESOURCE_PATH, $cartId),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        $payment = $quote->getPayment();
        $address = $quote->getBillingAddress();
        $addressData = [];
        $keys = [
            'city', 'company', 'countryId', 'firstname', 'lastname', 'postcode',
            'region', 'regionCode', 'regionId', 'saveInAddressBook', 'street', 'telephone', 'email'
        ];
        foreach ($keys as $key) {
            $method = 'get' . $key;
            $addressData[$key] = $address->$method();
        }
        $requestData = [
            VersionManager::PARAM_NAME => 214748,
            'cart_id' => $cartId,
            'billingAddress' => $addressData,
            'email' => $quote->getCustomerEmail(),
            'paymentMethod' => [
                'additional_data' => $payment->getAdditionalData(),
                'method' => $payment->getMethod(),
                'po_number' => $payment->getPoNumber()
            ]
        ];
        $this->_webApiCall($serviceInfo, $requestData);
    }
}
