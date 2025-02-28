define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'uiRegistry',
    'mage/template',
    'Magento_Catalog/js/price-utils',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'text!Ziffity_ProductCustomizer/template/popup/price-summary.html',
], function ($, _, ko, Component, registry, template, priceUtils, performAjax, price_summary) {
    'use strict';

    var position = "right";
    return Component.extend({
        pricingSummary: ko.observableArray(),
        totals: ko.observable(0),
        priceInvalid:ko.observable(false),
        productSku: window.customizerConfig.productSku,
        formatSubTotal: ko.observableArray(),
        headerImageDataArray:ko.observableArray([]),
        labelImageDataArray:ko.observableArray([]),
        labelTextDataArray:ko.observableArray([]),
        timer: '',
        defaults: {
            imports: {
                editmode: '${ $.provider }:editmode',
                options: '${ $.provider }:options',
                yourSelections: '${ $.provider }:your_selections',
                srcType: '${ $.provider }:options.additional_data.src_type',
                finishCustomization: '${ $.provider }:finish_customization',
                pricing: '${ $.provider }:price',
                priceInvalid: '${ $.provider }:price_invalid'
            },
            exports: {
                yourSelections: '${ $.provider }:your_selections',
                selections: '${ $.provider }:selections',
                pricingSummary: '${ $.provider }:price',
                totals: '${ $.provider }:options.additional_data.subtotal'
            },
            listens: {
                '${ $.provider }:options.size': 'recalculatePrice',
                '${ $.provider }:options.frame.active_item': 'recalculatePrice',
                '${ $.provider }:options.chalk_board.active_item': 'recalculatePrice',
                '${ $.provider }:options.cork_board.active_item': 'recalculatePrice',
                '${ $.provider }:options.dryerase_board.active_item': 'recalculatePrice',
                '${ $.provider }:options.fabric.active_item': 'recalculatePrice',
                '${ $.provider }:options.mat.active_items.top_mat': 'recalculatePrice',
                '${ $.provider }:options.mat.active_items.middle_mat':'recalculatePrice',
                '${ $.provider }:options.mat.active_items.bottom_mat':'recalculatePrice',
                '${ $.provider }:options.post_finish.active_item':'recalculatePrice',
                '${ $.provider }:options.glass.active_item': 'recalculatePrice',
                '${ $.provider }:options.laminate_finish': 'recalculatePrice',
                '${ $.provider }:options.mat.sizes':'recalculatePrice',
                '${ $.provider }:options.header':'recalculatePrice',
                '${ $.provider }:options.label':'recalculatePrice',
                // '${ $.provider }:options.mat.sizes.top.tenth':'recalculatePrice',
                // '${ $.provider }:options.mat.sizes.reveal': 'recalculatePrice',
                '${ $.provider }:options.lighting.form_data': 'recalculatePrice',
                '${ $.provider }:options.addons.form_data': 'recalculatePrice',
                // '${ $.provider }:options.lighting.form_data.power_connection': 'recalculatePrice',
                // '${ $.provider }:options.lighting.form_data.cord_color': 'recalculatePrice',
                '${ $.provider }:options.letter_board.active_item': 'recalculatePrice',
                '${ $.provider }:options.backing_board.active_item': 'recalculatePrice',
                '${ $.provider }:options.accessories': 'recalculatePrice',
                //'${ $.provider }:options.addons.form_data.plunge_lock': 'recalculatePrice',
                '${ $.provider }:options.shelves': 'recalculatePrice',
                // '${ $.provider }:options.shelves.shelves_thickness': 'recalculatePrice',
                '${ $.provider }:options.size.interior_depth': 'recalculatePrice',
                '${ $.provider }:options.header.text_header.selectedBackgroundColor': 'recalculatePrice',
                //'${ $.provider }:options.header.text-header.textHeaderArray': 'addSubscriptions',
                //'${ $.provider }:options.header.image-header.imageDataArray': 'updatedHeaderImages',
                //label
                //'${ $.provider }:options.label.text-label.textLabelArray': 'addSubscriptions',
                //'${ $.provider }:options.label.image-label.imageDataArray': 'updatedLabelImages',

            }
        },
        initialize: function() {
            this._super();
            this.recalculateTotals(this.pricing);
            this.recalculatePrice(this);
        },
        updatePrice: function(value){
           // var getUpdatedPrice = this.recalculatePrice(self);

            console.log(value);
        },

        addSubscriptions: function(value){
          console.log(value);
            let self = this;
            if (self.headerLabelType === 'header'){
                self.headerTextDataArray(value);
            }
            if (self.headerLabelType === 'label'){
                self.labelTextDataArray(value);
            }
            _.each(value,function(item){
                    if (!item.inputText.getSubscriptionsCount()) {
                        item.inputText.subscribe(function () {
                            // variable persisted here
                                window.clearTimeout(self.timer);
                                //var millisecBeforeRedirect = 10000;
                                self.timer = window.setTimeout(function(){
                                    self.recalculatePrice();
                                    },1500);

                        });
                    }
            }, self);

        },
        updatedLabelImages:function(value){
            let self = this;
            this.labelImageDataArray(value);
            _.each(value,function(item){
                if (!item.fileData.getSubscriptionsCount()) {
                    item.fileData.subscribe(function () {
                        window.clearTimeout(self.timer);
                        //var millisecBeforeRedirect = 10000;
                        self.timer = window.setTimeout(function(){
                            self.recalculatePrice();
                        },1500);

                    });
                }
            });
        },
        updatedHeaderImage:function(value){
            let self = this;
            this.headerImageDataArray(value);
            _.each(value,function(item){
                if (!item.fileData.getSubscriptionsCount()) {
                    item.fileData.subscribe(function () {
                        window.clearTimeout(self.timer);
                        //var millisecBeforeRedirect = 10000;
                        self.timer = window.setTimeout(function(){
                            self.recalculatePrice();
                        },1500);

                    });
                }
            });
        },
        formatPrice: function(value){
            return value.fixedTo
        },

        recalculatePrice:function (){
           // self = self !== undefined ? self : this;
            let self = this;
            let data = {};
            let result;
            data.options = self.options; //self.subPriceSumUp(self.pricing, self);
            data.sku = self.productSku;
            window.clearTimeout(self.timer);
            //var millisecBeforeRedirect = 10000;
            self.timer = window.setTimeout(function(){
                result = performAjax.performAjaxOperation('customizer/option/getSubtotal/','POST',data);
                $('body').trigger('processStop');
                result.done(function(response){
                    if(response!== undefined){
                        self.totals(response.subtotal);
                    }
                }, self);
            },1500);

        },
        convertPrice:function(price){
            //TODO:// Have to change the currency format.
            if(price) {
                var priceFormat = {
                    decimalSymbol: '.',
                    groupLength: 3,
                    groupSymbol: ",",
                    integerRequired: false,
                    pattern: "$%s",
                    precision: 2,
                    requiredPrecision: 2
                };
                this.totals(priceUtils.formatPrice(price, priceFormat));
            }
        },
        recalculateTotals: function(value){
            var self = this;
            if(value) {
                self.totals(self.calculateSubtotal(value, self));
            }
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
            self.calculateSummary(self);
            var subtotal = self.totals();
            var response = self.formatSummary(self.pricingSummary());
           // var response = self.pricingSummary();
            tpl = template(price_summary, {response, subtotal});
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('popup',{
                    header:'<h5>Your Pricing Summary: $<span>'+subtotal+'</span></h5>',
                    content:tpl,
                    show:true,
                    position:position

                }, self);
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

        formatSummary: function (data){
            var self = this;
            let summary = [];
            self.formatSubTotal.removeAll();
            Object.keys(data).forEach(key => {
                let label = '';
                switch(key){
                    case 'accessories':
                        label = 'Accessories';
                        break;
                    case 'frame':
                        label = 'Frame';
                        break;
                    case 'addons':
                        label = 'Add-on';
                        break;
                    case 'mat':
                        label = 'Mat';
                        break;
                    case 'cork_board':
                        label = 'Cork Board';
                        break;
                    case 'letter_board':
                        label = 'Letter Board';
                        break;
                    case 'dryerase_board':
                        label = 'Dry Erase Board';
                        break;
                    case 'chalk_board':
                        label = 'Chalk Board';
                        break;
                    case 'glass':
                        label = 'Glass/Glazing';
                        break;
                    case 'post_finish':
                        label = 'Post Finish';
                        break;
                    case 'fabric':
                        label = 'Fabric';
                        break;
                    case 'lighting':
                        label = 'Lighting';
                        break;
                    case 'laminate_finish':
                        label = 'Laminate Finish';
                        break;
                    case 'backing_board':
                        label = 'Backing Board';
                        break;
                    case 'other_components':
                        label = 'Frame Components & Parts';
                        break;
                    case 'shelves':
                        label = 'Shelves';
                        break;
                    case 'label':
                        label = 'Text & Image Labels';
                        break;
                    case 'header':
                        label = 'Header';
                        break;
                    default:
                        label = 'No Label';
                        break;
                }
                self.formatSubTotal.push({label:label,value:data[key]});
            }, self);
            return self.formatSubTotal();
        },

        calculateSummary:function (){
            var self = this;
            let data = {};
            let result;
            data.options = self.options; //self.subPriceSumUp(self.pricing, self);
            data.sku = self.productSku;
            // result = performAjax.performAjaxOperation('customizer/option/getSubtotal/','POST',data);
            //TODO should update the data in sequence
            $.ajax({
                url:window.BASE_URL+'customizer/option/getSubtotal/',
                // showLoader:true,
                data:{data},
                type:'POST',
                cache:true,
                async:false,
                beforeSend:function(){
                    $('body').trigger('processStart'); // start loader
                }
            }).done(function(response){
                $('body').trigger('processStop');
                if(response!== undefined){
                    self.totals(response.subtotal);
                    self.pricingSummary(response.price_summary);
                }
            }, self);
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
        calculateSubtotal: function (data) {
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
