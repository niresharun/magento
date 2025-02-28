define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'uiRegistry',
    'mage/template',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'text!Ziffity_ProductCustomizer/template/popup/price-summary.html',
], function ($, _, ko, Component, registry, template, performAjax, price_summary) {
    'use strict';

    var position = "right";
    return Component.extend({
        pricingSummary: ko.observableArray(),
        totals: 0,
        defaults: {
            imports: {
                editmode: '${ $.provider }:editmode',
                options: '${ $.provider }:options',
                yourSelections: '${ $.provider }:your_selections',
                srcType: '${ $.provider }:options.additional_data.src_type',
                finishCustomization: '${ $.provider }:finish_customization',
                pricingSummary: '${ $.provider }:price',
            },
            exports: {
                yourSelections: '${ $.provider }:your_selections',
                selections: '${ $.provider }:selections',
                totals: '${ $.provider }:subtotal'
            },
            listens:{
                '${ $.provider }:price': 'recalculateTotals',
            },
        },
        initialize: function() {
            this._super();
            this.recalculatePrice(this);
            console.log(this.pricingSummary());
        },
        recalculatePrice:function (self){
            var self = this;
            let data = {};
            let result;
            data.options = self.options; //self.subPriceSumUp(self.pricing, self);
            data.sku = self.productSku;
            result = performAjax.performAjaxOperation('customizer/option/getSubtotal/','POST',data);
            $('body').trigger('processStop');
            result.done(function(response){
                if(response!== undefined){
                    self.pricingSummary(response.price_summary);
                }
            }, self);
        },
        recalculateTotals: function(value){
            var self = this;
            self.totals = 0;
            if(value) {
                self.totals = self.calculateSubtotal(value, self);
            }
            return self.totals;
        },
        calculatePrice: function(self) {
            var self = this;
            let result;
            var pricingData = self.pricing; //self.subPriceSumUp(self.pricing, self);
            result = performAjax.performAjaxOperation('customizer/option/getPriceSummary/','POST',pricingData);
            $('body').trigger('processStop');
            result.done(function(response){
                if(response!== undefined){
                    self.pricingSummary(response);
                }
            }, self);
        },
        // subPriceSumUp: function(data, self){
        //     var price = [];
        //     Object.keys(data).forEach(key => {
        //
        //         if(typeof (data[key]) !== 'number'){
        //             console.log();
        //         }
        //     }, self);
        //
        // },
        loadPriceSummary: function() {

            var self = this;
            var tpl = '';
            self.calculatePrice(self);
            var subtotal = self.calculateSubtotal(self.pricing);
            var response = self.pricingSummary();
            tpl = template(price_summary, {response, subtotal});
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('popup',{
                    content:tpl,
                    show:true,
                    position:position

                });
                $('body').addClass('slidein-active');
                var popup = document.querySelector('.customizer-slidein');
                var main_popup = document.querySelector('.customizer-main-popup')
                popup.style.display = 'flex';
                main_popup.style.cssText = 'animation:slide-in-'+position+' .5s ease; animation-fill-mode: forwards';
            });



            // registry.async('customizerProvider')(function (customizerProvider) {
            //     customizerProvider.set('popup',{
            //         content:'<h2>Price summary<h2>',
            //         show:true,
            //         position:position
            //
            //     });
            //     $('body').addClass('slidein-active');
            //     var popup = document.querySelector('.customizer-slidein');
            //     var main_popup = document.querySelector('.customizer-main-popup')
            //     popup.style.display = 'flex';
            //     main_popup.style.cssText = 'animation:slide-in-'+position+' .5s ease; animation-fill-mode: forwards';
            // });
        },
        loadSelectionPopup: function() {
            var self = this;
            var tpl = '';
            var test = 'test';
            self.calculatePrice(self);
            var response;
            response['options'] = self.pricingSummary();
            response['sutotal'] = self.calculateSubtotal(self.pricingSummary());
            tpl = template(your_selections, {response, test});
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('popup',{
                    content:tpl,
                    show:true,
                    position:position

                });
                $('body').addClass('slidein-active');
                var popup = document.querySelector('.customizer-slidein');
                var main_popup = document.querySelector('.customizer-main-popup')
                popup.style.display = 'flex';
                main_popup.style.cssText = 'animation:slide-in-'+position+' .5s ease; animation-fill-mode: forwards';
            });
        },
        calculateSubtotal: function (data, self) {
            var subtotal = 0;
            Object.keys(data).forEach(key => {
                if(typeof (data[key]) == 'number'){
                    subtotal += data[key];
                }
            }, self);
            return subtotal;
        }
    } );

});
