define([
    'jquery',
    'underscore',
    'ko',
    'Ziffity_ProductCustomizer/js/view/filter-utils',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'mage/translate',
    'Magento_Catalog/js/price-utils',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'text!Ziffity_ProductCustomizer/template/optioninfo/accessories-info.html',
], function ($, _, ko, filterUtils,AbstractOption, stepNavigator, $t, priceUtils, performAjax, customizerDataResolver, info ) {
    'use strict';

    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(false),
            isVisible:ko.observable(false),
            selection : ko.observable(),
            accessories: ko.observableArray(),
            productSelections:ko.observableArray(),
            // position: parseInt(window.customizerConfig.options.accessories.position),
            index: 5,
            placeholder:ko.observable('Search for product name...'),
            searchFocused:ko.observable(false),
            selectedFrameDetails:ko.observable(),//TODO: Anyone can use this observable as this contains the product_id and if it is selected or not from the frame product options.
            productSku:window.customizerConfig.productSku,
            productList:ko.observableArray([]),
            productListCount:ko.observable(0),
            pageNumber:ko.observable(0),
            searchQuery:ko.observable(null),
            showSelectionsFilter:ko.observable(false),
            keywordUpdated:false,
            searchValue:null,
            productsPerPage:2,//TODO: Have to set how many products should we set in admin store configurations and set that value here dynamically.
            offsetValue:0,
            position:90,
            template: 'Ziffity_ProductCustomizer/options/accessories',
            filterUtils:filterUtils,
            exports: {
                productSelections: '${ $.provider }:options.accessories.active_items',
            },
            imports: {
                options: '${ $.provider }:options'
            }
        },
        initSelection:function(){
            var self = this;
            self.productSelections(customizerDataResolver.resolveAccessoriesSelection());
        },
        updateSelection: function(value) {
            if(!value) {
                this.initSelection();
            }
        },
        initialize: function() {
            var self = this;
            this._super();
            stepNavigator.registerStep(
                'Accessories',
                'accessories',
                this.isActive,
                this.isVisible,
                self.position,
                self.sortOrder,
                false,
                true,
                info
            );
            this.isActive.subscribe(function(value){
                if (value){
                    self.loadProductListIntoObservable(self);
                    console.log(value);
                }
            });

            self.initSelection();

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
            this.productSelections.subscribe(function (value){
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
            data.option = 'accessories';
            result = performAjax.performAjaxOperation('customizer/option/getValues/','POST',data);
            result.done(function(response){
                if (response!==undefined && response.products !== undefined) {
                    self.accessories.removeAll();
                    self.accessories(self.productSelections().slice());
                    self.productSelections.removeAll();
                    response.products.forEach(product =>{
                        if(self.accessories().length > 0) {
                            // self.productSelections.forEach(productSelection => {
                            //     if (product.id == productSelection.id) {
                            //         self.productSelection.push(product);
                            //     }
                            // });
                            Object.keys(self.accessories()).forEach(key => {
                                console.log(self.accessories()[key]);
                                if (product.id == self.accessories()[key].id) {
                                    self.productSelections.push(product);
                                }
                            }, self);
                        }
                    });
                    self.productList(response.products);
                    if (response.product_total_count.accessories!==undefined) {
                        self.productListCount(response.product_total_count.accessories);
                    }
                } else {
                    self.productList([]);
                    self.productListCount(0);
                }
                $('body').trigger('processStop');
            });
        },
        prepareSearchData:function(searchQuery,self){
            let data = {};
            let result;
            data.pagination = {};
            data.search = searchQuery;
            data.sku = self.productSku;
            data.option = 'mat';
            data.optionType = 'top-mat';
            data.filters = [];
            data.pagination.limit = self.productsPerPage;
            data.pagination.offset = self.offsetValue + 1;
            data.selections = {width:null,height:null};
            result = performAjax.performAjaxOperation('customizer/option/getValues/','GET',data);
            result.done(function(response){
                if (response!==undefined && response.products !== undefined) {
                    response.products.forEach(product =>{
                        self.productSelections.forEach(productSelection => {
                            if(product.id == productSelection.id) {
                                self.productSelection(product);
                            }
                        });

                    });
                    self.productList(response.products);
                    if (response.product_total_count!==undefined) {
                        self.productListCount(response.product_total_count);
                    }
                }
                $('body').trigger('processStop');
                self.searchFocused(false);
            });
        },
    });
});
