define([
    'jquery',
    'underscore',
    'ko',
    'Ziffity_ProductCustomizer/js/view/filter-utils',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Magento_Catalog/js/price-utils',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'text!Ziffity_ProductCustomizer/template/optioninfo/backing-board-info.html',
], function ($, _, ko,filterUtils, AbstractOption, stepNavigator, performAjax, priceUtils, customizerDataResolver, info) {
    'use strict';

    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(false),
            isVisible:ko.observable(false),
            defaultSelection: window.customizerConfig.options.backing_board.active_item,
            optionId: window.customizerConfig.options.backing_board.active_item.option_id,
            selection : ko.observable(),
            productSelection:ko.observable(),
            placeholder:ko.observable('Search for product name...'),
            searchFocused:ko.observable(false),
            selectedFrameDetails:ko.observable(),//TODO: Anyone can use this observable as this contains the product_id and if it is selected or not from the frame product options.
            productSku:window.customizerConfig.productSku,
            productList:ko.observableArray(),
            productListCount:ko.observable(0),
            pageNumber:ko.observable(1),
            searchQuery:ko.observable(null),
            productLoaded: ko.observable(false),
            showSelectionsFilter:ko.observable(false),
            keywordUpdated:false,
            searchValue:null,
            productsPerPage:2,//TODO: Have to set how many products should we set in admin store configurations and set that value here dynamically.
            offsetValue:0,
            position: 50,
            subtotal: ko.observable(),
            pricing: ko.observable(),
            template: 'Ziffity_ProductCustomizer/options/backing-board',
            filterUtils:filterUtils,
            //custom pagination
            currentPage:ko.observable(1),
            exports: {
                productSelection: '${ $.provider }:options.backing_board.active_item',
                optionId: '${ $.provider }:options.backing_board.option_id',
                pricing: '${ $.provider }:price.backing_board',
                subtotal: '${ $.provider }:price.subtotal'
            },
            listens:{
                '${ $.provider }:editmode': 'updateSelection',
            },
            imports: {
                options: '${ $.provider }:options',
            }
        },
        initSelection: function() {
            var self = this;
            self.productSelection(customizerDataResolver.resolveBackingBoardSelection());
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
                'Backing Board',
                'backing_board',
                this.isActive,
                this.isVisible,
                self.position,
                self.sortOrder,
                false,
                false,
                info
            );
            self.productSelection(self.defaultSelection);
            self.initSelection();
            ko.computed(function() {
                self.pricing(self.productSelection().price);
            });
            this.isActive.subscribe(function(value){
                if (value){
                    self.loadProductListIntoObservable(self);
                    self.recalculatePrice(self);
                }
            });

            this.totalPages = ko.computed(function(){
                let div = Math.floor(this.productListCount() / this.productsPerPage);
                div += this.productListCount() % this.productsPerPage > 0 ? 1 : 0;
                return div;
            },this);
            this.hasPrevious = ko.computed(function() {
                return this.pageNumber() !== 1;
            },this);
            this.hasNext = ko.computed(function() {
                return this.pageNumber() !== this.totalPages();
            },this);
            this.productSelection.subscribe(function (value){
                console.log(value);
            });
            //custom pagination
            // Generate an array of page numbers
            self.pageNumbers = ko.computed(function () {
                let totalPages = Math.ceil(self.productListCount()/self.productsPerPage);
                let pagesArray = [];
                for (let i = 1; i <= totalPages; i++) {
                    pagesArray.push(i);
                }
                let pagesElements = [];
                if (self.pageNumber() > 5){
                    return _.last(pagesArray,5);
                }
                // if (self.pageNumber() >= 5) {
                //     console.log(self.pageNumber() + 5);
                //     for (let i = self.pageNumber(); i < self.pageNumber() + 5; i++) {
                //         if (_.contains(pagesArray, i)) {
                //             pagesElements.push(i);
                //         }
                //     }
                //     pagesArray = pagesElements;
                // }
                return _.first(pagesArray,5);
            });
        },


        next:function() {
            if(this.pageNumber() < this.totalPages()) {
                let result = this.pageNumber() + 1;
                this.offsetValue = result;
                this.pageNumber(result);
                this.currentPage(result);
                this.loadProductListIntoObservable(this);
            }
        },
        goToPage:function (pageNumber) {
            if(pageNumber <= this.totalPages()) {
                let result = pageNumber;
                this.offsetValue = result;
                this.pageNumber(result);
                this.filters = [];
                this.packFilters();
                this.loadProductListIntoObservable(this);
            }
        },
        goToLastPage:function(){
            let result = this.totalPages();
            this.offsetValue = result;
            this.pageNumber(result);
            this.currentPage(result);
            this.filters = [];
            this.packFilters();
            this.loadProductListIntoObservable(this);
        },
        goToFirstPage:function(){
            let result = 1;
            this.offsetValue = result;
            this.pageNumber(result);
            this.currentPage(result);
            this.filters = [];
            this.packFilters();
            this.loadProductListIntoObservable(this);
        },
        packFilters:function() {
            if (!_.isEmpty(this.filterChecked())) {
                if (!_.isArray(this.filterChecked())) {
                    this.filters = JSON.stringify([JSON.parse(this.filterChecked())]);
                }
                if (_.isArray(this.filterChecked())){
                    let newArr = [];
                    _.each(this.filterChecked(),function(item){
                        newArr.push(JSON.parse(item));
                    });
                    this.filters = JSON.stringify(newArr);
                }
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
                this.currentPage(result);
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
        clearSearch:function(){
            let self =this;
            self.searchQuery(null);
            self.searchValue = null;
            self.loadProductListIntoObservable(self);
        },
        loadProductListIntoObservable:function(self) {
            var self = this;
            let data = {};
            let result;
            self.productLoaded(false);
            data.pagination = {};
            data.sku = self.productSku;
            data.options = self.options;
            data.option = 'backingboard';
            data.search = self.searchValue;
            data.filters = [];
            data.pageSize = this.productsPerPage;
            data.pagination.limit = self.productsPerPage;
            data.pagination.offset = self.offsetValue;
            data.selections = {width:null,height:null};
            result = performAjax.performAjaxOperation('customizer/option/getValues/','POST',data);
            result.done(function(response){
                if (response!==undefined && response.products !== undefined) {
                    response.products.forEach(product =>{
                        if(!_.isEmpty(self.productSelection()) && product.id == self.productSelection().id) {
                            self.productSelection(product);
                        }
                    });
                    self.productList(response.products);
                    if (response.product_total_count.backing_board!==undefined) {
                        self.productListCount(response.product_total_count.backing_board);
                    }
                } else {
                    self.productList([]);
                    self.productListCount(0);
                }
                self.productLoaded(true);
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
