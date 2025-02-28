define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'uiRegistry',
], function ($, _, ko, Component, registry) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Ziffity_CustomFrame/minicart/item/default',

        },
        initialize: function () {
            this._super();
        },
        getAdditionalData: function(data)
        {
            this.items = [];
            Object.keys(data).forEach(function(key, index){
                this.items.push({label:key, value:data[key]});
            }, this);
        },
        isValueArray: function (value){
            return Array.isArray(value);
        },
        frameImage: function(item){
            return {
                alt: item.product_name,
                height: 78,
                src: item.saved_img,
                width: 78,
            };
        }
    })
});
