define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'uiRegistry',
    'Ziffity_ProductCustomizer/js/view/progress-bar',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Magento_Customer/js/customer-data',
    'mage/cookies'
], function ($, _, ko, Component, registry, progressBar, stepNavigator, customizerResolver, performAjax, customerData) {
    'use strict';

    return Component.extend({
        editmode: ko.observable(true),
        defaults: {
            template: 'Ziffity_ProductCustomizer/buttons',
            visible: true,
            processedSteps: ko.observableArray(),
            qty: ko.observable(),
            steps: stepNavigator.steps,
            srcType: ko.observable(),
            quoteScope: ko.observable(),
            sizeRestricted: ko.observable(false),
            currentStep: stepNavigator.currentStep,
            invalidQty: ko.observable(false),
            canvasData: ko.observable(),
            isAddToCartDisabled: false,
            quoteAllowed: ko.observable(false),
            reset: ko.observable(false),
            canScrollLeft: ko.observable(false),
            canScrollRight: ko.observable(false),
            imports: {
                editmode: '${ $.provider }:editmode',
                yourSelections: '${ $.provider }:your_selections',
                selections: '${ $.provider }:selections',
                options: '${ $.provider }:options',
                reset: '${ $.provider }:reset',
                sizeRestricted: '${ $.provider }:size_restricted',
                canvasData: '${ $.provider }:options.additional_data.canvasData',
                canScrollLeft: '${ $.provider }:canScrollLeft',
                canScrollRight: '${ $.provider }:canScrollRight'
            },
            exports: {
                srcType: '${ $.provider }:options.additional_data.src_type',
                canScrollLeft: '${ $.provider }:canScrollLeft',
                canScrollRight: '${ $.provider }:canScrollRight',
            },
            listens: {
                '${ $.provider }:navigate': 'manageScroll'
            }
        },
        initialize: function () {
            this._super();
            var self = this;
            self.srcType(customizerResolver.resolveSrcType());
            self.quoteAllowed(customizerResolver.resolveQuoteAllowed());
            self.quoteScope(customizerResolver.resolveQuoteScope());
            window.customizerConfig.quote !== undefined ? self.qty(window.customizerConfig.quote.qty): self.qty(1);
            self.srcType.subscribe(function (value){
                console.log(value);
            });
            self.qty.subscribe(function(value){
                self.invalidQty(false);
                if(value <= 0 ){
                    self.invalidQty(true);
                }
                console.log(value);
            }, self);
            self.isAddToCartDisabled = ko.computed(() => {
                return self.invalidQty() || self.canvasData() === undefined;
            });
        },
        validateQty:function(element, event){
            console.log(element, event);
        },

        prevStep: function () {
            var self = this;
            stepNavigator.prev();
            this.manageScroll();
        },

        nextStep: function () {
            stepNavigator.next();
            this.manageScroll()
        },
        manageScroll:function() {
            if ($('.tablinks.active').length) {
                $('.tablinks.active').toArray()[0].scrollIntoView();
                var optionSelector = $('.option-group-tab .options')[0];
                var clientWidth = optionSelector.clientWidth;
                var scrollLeft = optionSelector.scrollLeft;
                var scrollWidth = optionSelector.scrollWidth;
                if (scrollWidth > clientWidth) {
                    this.canScrollRight(true);
                    if (clientWidth + scrollLeft + 3 >= scrollWidth) {
                        console.log('right reached');
                        this.canScrollRight(false);
                    }
                    if (scrollLeft <= 0) {
                        this.canScrollLeft(false);
                    }
                    if (scrollLeft > 0) {
                        this.canScrollLeft(true);
                    }
                }
            }
        },
        finishCustomization: function () {
            var self = this;
            //self.srcType('your_selections');
            customizerResolver.resetCustomizerData();
            customizerResolver.saveInStorage(self.options);
            $('body').removeClass('customizer-active');
            stepNavigator.resetFirstStep();
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('editmode', false);
            });
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('finish_customization', true);
            });

        },

        resetToDefault: function () {
            let self = this;
            let resetVar = (self.reset() !== true);
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('reset', resetVar);
            });
            stepNavigator.resetFirstStep();
            this.updateProccessedTabs();
        },

        getDataValidator: function () {
            return JSON.stringify(this.dataValidate);
        },

        decreaseQty: function () {
            var self = this;
            if (this.qty() > 1) {
                self.qty(parseInt(this.qty()) - 1);
            } else {
                self.qty(1);
            }
        },

        increaseQty: function () {
            this.qty(parseInt(this.qty()) + 1);
        },
        addToCartAction: function () {
            var self = this;
            let data = {};
            let params = {};
            let result;
            params = {
                form_key: $.mage.cookies.get('form_key'),
                product: window.customizerConfig.productId,
                selected_configurable_option: '',
                options: self.options,
                related_product: '',
                updateItem: false,
                item: 1,
                qty: self.qty()
            };
            $.ajax({
                url: window.customizerConfig.addToCartUrl,
                // showLoader:true,
                type: 'POST',
                data: params,
                cache: true,
                beforeSend: function () {
                    $('body').trigger('processStart'); // start loader
                },

            }).done(function (response) {

                if(response.length > 0){
                    window.location.reload();
                }
                $('body').trigger('processStop');
            });
        },
        updateCartAction: function () {
            var self = this;
            let data = {};
            let params = {};
            let result;
            let baseUrl = window.location.protocol + "//" + window.location.host;
            let cartUpdateUrl = baseUrl+''+'/checkout/cart/updateItemOptions/id/'+window.customizerConfig.quote.item_id;
            params = {
                form_key: $.mage.cookies.get('form_key'),
                product: window.customizerConfig.productId,
                selected_configurable_option: '',
                options: self.options,
                updateItem: true,
                related_product: '',
                item: 1,
                qty: self.qty()
            };
            $.ajax({
                url: cartUpdateUrl,
                // showLoader:true,
                type: 'POST',
                data: params,
                cache: true,
                beforeSend: function () {
                    $('body').trigger('processStart'); // start loader
                },

            }).done(function (response) {
                if(response){
                    window.location.href ='/checkout/cart';
                }
                customerData.reload(['quotecart']);
                $('body').trigger('processStop');
            });
        },
        updateQuoteActionAdmin: function () {
            var self = this;
            let data = {};
            let params = {};
            let result;
            let baseUrl = window.location.protocol + "//" + window.location.host;
            let cartUpdateUrl = baseUrl+''+'/requestquote/quote/updateitem/id/'+window.customizerConfig.quote.item_id;
            params = {
                form_key: $.mage.cookies.get('form_key'),
                product: window.customizerConfig.productId,
                selected_configurable_option: '',
                options: self.options,
                updateItem: true,
                related_product: '',
                item: 1,
                qty: self.qty()
            };
            $.ajax({
                url: cartUpdateUrl,
                // showLoader:true,
                type: 'POST',
                data: params,
                cache: true,
                beforeSend: function () {
                    $('body').trigger('processStart'); // start loader
                },
            }).done(function (response) {
                customerData.invalidate(['quotecart']);
                customerData.reload(['quotecart']);
                $('body').trigger('processStop');
            });
        },
        updateQuoteAction: function () {
            var self = this;
            let data = {};
            let params = {};
            let result;
            let baseUrl = window.location.protocol + "//" + window.location.host;
            let cartUpdateUrl = baseUrl+''+'/requestquote/quote/updateitem/id/'+window.customizerConfig.quote.item_id;
            params = {
                form_key: $.mage.cookies.get('form_key'),
                product: window.customizerConfig.productId,
                selected_configurable_option: '',
                options: self.options,
                updateItem: true,
                related_product: '',
                item: 1,
                qty: self.qty()
            };
            $.ajax({
                url: cartUpdateUrl,
                // showLoader:true,
                type: 'POST',
                data: params,
                cache: true,
                beforeSend: function () {
                    $('body').trigger('processStart'); // start loader
                },

            }).done(function (response) {
                $('body').trigger('processStop');
                if(response){
                    window.location.href ='/request_quote/cart';
                }
            });
        },
        addToQuoteAction: function () {
            var self = this;
            let data = {};
            let params = {};
            let result;
            params = {
                form_key: $.mage.cookies.get('form_key'),
                product: window.customizerConfig.productId,
                selected_configurable_option: '',
                options: self.options,
                related_product: '',
                updateItem: false,
                item: 1,
                qty: self.qty()
            };
            $.ajax({
                url: window.customizerConfig.addToQuoteUrl,
                // showLoader:true,
                type: 'POST',
                data: params,
                cache: true,
                beforeSend: function () {
                    $('body').trigger('processStart'); // start loader
                },

            }).done(function (response) {
                $('body').trigger('processStop');
            });
        },
        updateProccessedTabs: function()
        {
            var self = this;
            stepNavigator.resetFirstStep();
            stepNavigator.resetAllProcessed();

        },
    })
});
