define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/place-order',
    'Ziffity_DigitalSigning/js/model/digital-signing',
    'Ziffity_DigitalSigning/js/model/additional-information'
], function (quote, urlBuilder, customer, placeOrderService, digitalSigning,additionalOrderInfo) {
    'use strict';

    return function (paymentData, messageContainer) {
        var serviceUrl, payload;

        payload = {
            cartId: quote.getQuoteId(),
            billingAddress: quote.billingAddress(),
            paymentMethod: paymentData,
            ImageData:digitalSigning.getImage(),
            purchaseData:additionalOrderInfo.getPurchaseOrder(),
            orderNotes:additionalOrderInfo.getOrderNotes()
        };

        if (customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/carts/mine/payment-information', {});
        } else {
            serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/payment-information', {
                quoteId: quote.getQuoteId()
            });
            payload.email = quote.guestEmail;
        }

        return placeOrderService(serviceUrl, payload, messageContainer);
    };
});
