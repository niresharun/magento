define([
    'jquery',
    'underscore',
    'ko',
    'mage/translate',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Magento_Catalog/js/price-utils',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'Ziffity_ProductCustomizer/js/view/your-selections',
    'text!Ziffity_ProductCustomizer/template/optioninfo/post-finish-info.html',
], function ($, _, ko,$t, AbstractOption, stepNavigator,priceUtils,
             performAjax, customizerDataResolver,yourSelection, info) {
    'use strict';

    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(false),
            isVisible:ko.observable(false),
            postFinishContent:ko.observable($t('When framing a thin print (such as a poster, sign, photo, or artwork) a ' +
                '“post finish” may be necessary to stiffen the printed graphic when displayed in the frame. ' +
                'Without a post finish, the thin graphic sheet may not stay up flat. ' +
                'It could fall/slip through the frame or appear wavy and wrinkled which will create an unpleasant appearance. ' +
                'Depending on the type of graphic being framed, you can select the backing board that best meets your graphic requirement.')),
            template: 'Ziffity_ProductCustomizer/options/frame',
            position:55,
            productSelection:ko.observable(),
            productSku:window.customizerConfig.productSku,
            productList:ko.observableArray([]),
            pricing: ko.observable(),
            exports: {
                productSelection: '${ $.provider }:options.post_finish.active_item',
                pricing: '${ $.provider }:price.post_finish'
            },
        },

        initSelection:function(){
            var self = this;
            self.productSelection(customizerDataResolver.resolvePostFinishSelection());
        },
        initialize: function() {
            this._super();
            let self = this;
            self.initSelection();
            ko.computed(function() {
                self.pricing(self.productSelection().price);
            });
            stepNavigator.registerStep(
                'Post Finish',
                'post_finish',
                this.isActive,
                this.isVisible,
                self.position,
                self.sortOrder,
                false,
                false,
                info
            );
            this.isActive.subscribe(function(value){
                if (value){
                    self.loadProductListIntoObservable(self);
                }
            })
        },
        callPopup:function(){
           yourSelection().loadSelectionPopup();
        },
        convertPrice:function(price){
            //TODO:// Have to change the currency format.
            var priceFormat = {
                decimalSymbol: '.',
                groupLength: 3,
                groupSymbol: ",",
                integerRequired: false,
                pattern: "$%s",
                precision: 2,
                requiredPrecision: 2
            };
            return priceUtils.formatPrice(price, priceFormat);
        },
        loadProductListIntoObservable:function(self){
            let data = {};
            let result;
            data.sku = self.productSku;
            data.option = 'post-finish';
            result = performAjax.performAjaxOperation('customizer/option/getValues/','POST',data);
            result.done(function(response){
                if (response!==undefined && response.products !== undefined) {
                    response.products.forEach(product =>{
                        if(product.id === self.productSelection().id) {
                            self.productSelection(product);
                        }
                    });
                    self.productList(response.products);
                    _.each(response.products,function(item){
                        if (item.is_default === "1"){
                            self.productSelection(item);
                        }
                    });
                }
                $('body').trigger('processStop');
            });
        }
    });
});
