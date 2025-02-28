define([
    'jquery',
    'underscore',
    'ko',
    'Ziffity_ProductCustomizer/js/view/filter-utils',
    'Magento_Catalog/js/price-utils',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'text!Ziffity_ProductCustomizer/template/optioninfo/glass-info.html',
], function ($, _, ko, filterUtils,priceUtils,  AbstractOption, stepNavigator, performAjax, customizerDataResolver, info) {
    'use strict';

    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(false),
            isVisible:ko.observable(false),
            defaultSelection: window.customizerConfig.options.glass.active_item,
            optionId: window.customizerConfig.options.glass.active_item.option_id,
            selection : ko.observable(),
            productSelection:ko.observable(),
            placeholder:ko.observable('Search for product name...'),
            searchFocused:ko.observable(false),
            selectedFrameDetails:ko.observable(),//TODO: Anyone can use this observable as this contains the product_id and if it is selected or not from the frame product options.
            productSku:window.customizerConfig.productSku,
            productList:ko.observableArray(),
            productListCount:ko.observable(0),
            pageNumber:ko.observable(0),
            searchQuery:ko.observable(null),
            showSelectionsFilter:ko.observable(false),
            keywordUpdated:false,
            searchValue:null,
            productsPerPage:2,//TODO: Have to set how many products should we set in admin store configurations and set that value here dynamically.
            offsetValue:0,
            position:35,
            pricing: ko.observable(),
            pricingSummary: ko.observableArray(),
            subtotal: ko.observable(),
            template: 'Ziffity_ProductCustomizer/options/glass',
            filterUtils:filterUtils,
            exports: {
                productSelection: '${ $.provider }:options.glass.active_item',
                optionId: '${ $.provider }:options.glass.option_id',
                pricing: '${ $.provider }:price.glass',
                subtotal: '${ $.provider }:price.subtotal'
            },
            listens:{
                '${ $.provider }:editmode': 'updateSelection'
            },
            imports: {
                options: '${ $.provider }:options',
            }
        },
        initSelection: function() {
            var self = this;
            self.productSelection(customizerDataResolver.resolveGlassSelection());
        },
        updateSelection: function(value) {
            if(!value) {
                this.initSelection();
            }
        },
        initialize: function() {
            this._super();
            var self = this;
            stepNavigator.registerStep(
                'Glass/Glazing',
                'glass',
                this.isActive,
                this.isVisible,
                self.position,
                self.sortOrder,
                false,
                false,
                info
            );
            self.initSelection();
            ko.computed(function() {
                self.pricing(self.productSelection().price);
            });
            this.isActive.subscribe(function(value){
                if (value){
                    self.loadProductListIntoObservable(self);
                    //self.recalculatePrice(self);
                }
            });

            this.totalPages = ko.computed(function(){
                let div = Math.floor(this.productListCount() / this.productsPerPage);
                div += this.productListCount() % this.productsPerPage > 0 ? 1 : 0;
                return div - 1;
            },this);
            this.hasPrevious = ko.computed(function() {
                return this.pageNumber() !== 0;
            },this);
            this.hasNext = ko.computed(function() {
                return this.pageNumber() !== this.totalPages();
            },this);
            this.productSelection.subscribe(function (value){
                console.log(value);
            });
        },

        next:function() {
            if(this.pageNumber() < this.totalPages()) {
                let result = this.pageNumber() + 1;
                this.offsetValue = result;
                this.pageNumber(result);
                this.loadProductListIntoObservable(this);
            }
        },
        filterButton:function(){
            if (!this.showSelectionsFilter()) {
                this.showSelectionsFilter(true);
                $("#toggle").fadeToggle("slow");
                return;
            }
            if (this.showSelectionsFilter()){
                $("#toggle").fadeToggle("slow");
                this.showSelectionsFilter(false);
            }
        },
        previous:function() {
            if(this.pageNumber() != 0) {
                let result = this.pageNumber() - 1;
                this.offsetValue = result;
                this.pageNumber(result);
                this.loadProductListIntoObservable(this);
            }
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

        loadProductListIntoObservable:function(self) {
            var self = this;
            let data = {};
            let result;
            data.pagination = {};
            data.sku = self.productSku;
            data.options = self.options;
            data.option = 'glass';
            data.search = self.searchValue;
            data.filters = [];
            data.pageSize = this.productsPerPage;
            data.pagination.limit = self.productsPerPage;
            data.pagination.offset = self.offsetValue + 1;
            data.selections = {width:null,height:null};
            result = performAjax.performAjaxOperation('customizer/option/getValues/','POST',data);
            result.done(function(response){
                if (response!==undefined && response.products !== undefined) {
                    response.products.forEach(product =>{
                        if(product.id == self.productSelection().id) {
                            self.productSelection(product);
                        }
                    });
                    self.productList(response.products);
                    if (response.product_total_count.glass!==undefined) {
                        self.productListCount(response.product_total_count.glass);
                    }
                } else {
                    self.productList([]);
                    self.productListCount(0);
                }
                $('body').trigger('processStop');
            });
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
                    self.subtotal(response.subtotal);
                }
            }, self);
        },
    });
});
