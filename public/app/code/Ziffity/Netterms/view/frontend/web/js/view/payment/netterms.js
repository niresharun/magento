define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'netterms',
            component: 'Ziffity_Netterms/js/view/payment/method-renderer/netterms-method'
        }
    );

    /** Add view logic here if needed */
    return Component.extend({});
});
