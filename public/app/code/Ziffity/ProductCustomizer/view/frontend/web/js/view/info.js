define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent'
], function ($, _, ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Ziffity_ProductCustomizer/info',
            visible: true,
            productName: window.customizerConfig.productName,
            productSku: window.customizerConfig.productSku,
        },
        initialize: function() {
            this._super();
        },

        getproductName: function() {
            return productName;
        },
        getProductSku: function() {
            return productSku;
        }
    } )
});
