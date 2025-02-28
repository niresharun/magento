define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator',
    'mage/url'
], function (ko, $, Component, stepNavigator, url) {
    'use strict';
    return Component.extend({
        isButtonVisible: function () {
            return stepNavigator.getActiveItemIndex() != 1;
        },
        getBaseUrl: function() { 
            return url.build('amasty_quote/move/inQuote');
        },

        getFormKey: function(){
            return $.cookie('form_key');
        },
        getQuoteCartUrl: function(){
            var self = this;
            return JSON.stringify({'action' :self.getBaseUrl(), 'data': {'form_key':self.getFormKey()}});
        },
    });
});