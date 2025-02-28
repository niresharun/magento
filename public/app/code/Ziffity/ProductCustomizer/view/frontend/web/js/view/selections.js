define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'uiRegistry',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
], function ($, _, ko, Component, registry, stepNavigator, customizerDataResolver) {
    'use strict';

    return Component.extend({
        defaults: {
            selections: ko.observableArray(),
            editmode: ko.observable(true),
            srcType: ko.observable(),
            yourSelections: ko.observableArray(),
           // isDefaultSrc: ko.observable(true),
            imports: {
                editmode: '${ $.provider }:editmode',
                srcType: '${ $.provider }:options.additional_data.src_type',
                selections: '${ $.provider }:selections',
                yourSelections: '${ $.provider }:your_selections'
            },
        },
        initialize: function() {
            this._super();
            var self = this;
            setInterval((function() {
                this.addExpiry();
                this.removeExpired();
            }).bind(this), 60 * 1000);
            this.loadYourSelections();
        },

        enableCustomization : function() {
            var self = this;
            if(self.srcType() !== 'default' || customizerDataResolver.existInStorage()){
                stepNavigator.setAllProcessed();
            }
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('editmode', true);
            });
            $('body').addClass('customizer-active');
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('finish_customization', false);
            });
        },

        loadYourSelections: function() {
            var self = this;
        //     var options = window.customizerConfig.options;
        //     Object.keys(options).forEach(function(key, index){
        //         var element = options[key].default_selection;
        //        const selection = {
        //             name: element.option_name,
        //             selectionId: element.selection_id,
        //             selectionName: element.selection_name,
        //             price: element.price,
        //             swatchImage: element.swatch_image
        //         }
        //     self.selections.push(selection);
        //    });
        },
        isValueArray: function (value){
            return Array.isArray(value);
        },
        resetToDefault: function (){
            customizerDataResolver.resetCustomizerData();
            window.location.reload();
        },
        canReset: function() {
           return customizerDataResolver.existInStorage();
        },
        addExpiry: function(){
            let data = localStorage.getItem('customizer_data');
            let expiry = null;
            let expiryValue = 3600*1000;
            if (data) {
                data = JSON.parse(data);
                switch(this.srcType()){
                    case 'checkout':
                        if(window.customizerConfig.quote !== undefined)
                        {
                            expiry =  (data.hasOwnProperty('checkout') && data['checkout'].hasOwnProperty(window.customizerConfig.quote.item_id) &&
                                data['checkout'][window.customizerConfig.quote.item_id].hasOwnProperty('expiry')) ?
                                data['checkout'][window.customizerConfig.quote.item_id]['expiry']: null;
                            if(expiry){
                                data['checkout'][window.customizerConfig.quote.item_id]['expiry'] = Date.now()+expiryValue;
                            }
                        }
                        break;
                    case 'request_quote':
                        if(window.customizerConfig.quote !== undefined)
                        {
                            expiry = (data.hasOwnProperty('request_quote') && data['request_quote'].hasOwnProperty(window.customizerConfig.quote.item_id) &&
                                data['request_quote'][window.customizerConfig.quote.item_id].hasOwnProperty('expiry')) ?
                                data['request_quote'][window.customizerConfig.quote.item_id]['expiry']: null;
                            if(expiry){
                                data['product'][window.customizerConfig.quote.item_id]['expiry'] = Date.now()+expiryValue;
                            }
                        }
                        break;
                    case 'saved_designs':
                        if(window.customizerConfig.saved_designs !== undefined)
                        {
                            expiry = (data.hasOwnProperty('saved_designs') && data['saved_designs'].hasOwnProperty(window.customizerConfig.saved_designs.id) &&
                                data['saved_designs'][window.customizerConfig.saved_designs.id].hasOwnProperty('expiry')) ?
                                data['saved_designs'][window.customizerConfig.saved_designs.id]['expiry']: null;
                            if(expiry){
                                data['saved_designs'][window.customizerConfig.saved_designs.id]['expiry'] = Date.now()+expiryValue;
                            }
                        }
                        break;
                    case 'default':
                        expiry = (data.hasOwnProperty('product') && data['product'].hasOwnProperty(this.productSku) &&
                            data['product'][this.productSku].hasOwnProperty('expiry')) ? data['product'][this.productSku]['expiry'] : null;
                        if(expiry){
                            data['product'][this.productSku]['expiry'] = Date.now()+expiryValue;
                        }
                        break;
                }
            }
            expiry !== null ? localStorage.setItem('customizer_data', JSON.stringify(data)): '';
        },

        initScrollDown: function(){
            var sdBtn = $('.scroll-down');
            if (sdBtn.length > 0) {
                $(window).on('scroll', function () {
                    ($(this).scrollTop() > 100) ? sdBtn.fadeOut() : sdBtn.fadeIn();
                });
            }
        },

        removeExpired: function(){
            let data = localStorage.getItem('customizer_data');
            data = JSON.parse(data);
            if(data && ((data.last_updated + 60*1000) > Date.now())){
                return;
            }
            if (data) {
                Object.keys(data).forEach(key =>{
                    let item = data[key];
                    Object.keys(item).forEach(obj =>{
                        let expired = false;
                        if(item[obj].hasOwnProperty('expiry')){
                           if(item[obj]['expiry'] < Date.now()){
                               delete data[key][obj];
                           }
                        }
                    }, this);
                }, this);
                localStorage.setItem('customizer_data', JSON.stringify(data));
            }
        }
    } );
});
