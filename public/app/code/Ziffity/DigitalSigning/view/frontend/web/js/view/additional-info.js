define([
    'ko',
    'uiRegistry',
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator',
    'Ziffity_DigitalSigning/js/model/additional-information',
], function (ko,uiRegistry,Component,stepNavigator, purchaseOrder) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Ziffity_DigitalSigning/additional-information'
        },

        purchase: ko.observable(),
        order:ko.observable(),

        isFieldVisible: function () {
            return stepNavigator.getActiveItemIndex() == 1;
        },
        initialize: function() {
            var self = this;
            this._super();
            self.purchase.subscribe(function(value) {
                purchaseOrder.setPurchaseOrder(value);
            });
            self.order.subscribe(function(value) {
                purchaseOrder.setOrderNotes(value);
            });
        },
    });
});