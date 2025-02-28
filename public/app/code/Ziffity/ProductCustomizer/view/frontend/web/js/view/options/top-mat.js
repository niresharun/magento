define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Ui/js/modal/modal',
    'Magento_Catalog/js/price-utils',
    'Ziffity_ProductCustomizer/js/view/filter-utils',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/model/mat-types',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'text!Ziffity_ProductCustomizer/template/popup/size-restriction.html',
], function ($, _, ko, modal, priceUtils, filterUtils, AbstractOption, stepNavigator, matTypes, performAjax, customizerDataResolver, sizeTpl) {
    'use strict';

    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(true),
            matActive:ko.observable(),

            topMatProducts: ko.observableArray([]),
            // defaultSelection: window.customizerConfig.options.mat.active_items.top_mat,
            // optionId: window.customizerConfig.options.mat.active_items.top_mat.option_id,
            topMats: ko.observableArray(),
            selection : ko.observable(),
            productSelection:ko.observable(),
            placeholder:ko.observable('Search for product name...'),
            searchFocused:ko.observable(false),
            selectedFrameDetails:ko.observable(),//TODO: Anyone can use this observable as this contains the product_id and if it is selected or not from the frame product options.
            productSku:window.customizerConfig.productSku,
            productList:ko.observableArray([]),
            productListCount:ko.observable(0),
            pageNumber:ko.observable(1),
            searchQuery:ko.observable(null),
            showSelectionsFilter:ko.observable(false),
            currentSelection:ko.observable(),
            pricingSummary: ko.observableArray(),
            keywordUpdated:false,
            searchValue:null,
            productsPerPage:12,//TODO: Have to set how many products should we set in admin store configurations and set that value here dynamically.
            offsetValue:0,
            sortOrder:1,
            template: 'Ziffity_ProductCustomizer/options/top-mat',
            //Filter changes below
            allFilterChecked:ko.observableArray([]),
            filterChecked:ko.observableArray([]).extend({ rateLimit: 50 }),
            selectionFilters:ko.observableArray([]),
            filters:false,
            pricing: ko.observable(),
            topMatPrice: ko.observable(),
            subtotal: ko.observable(),
            matCount: ko.observable(),
            filterUtils:filterUtils,
            sizeRestricted: ko.observable(),
            productLoaded: ko.observable(false),
            //custom pagination
            currentPage:ko.observable(1),
            exports: {
                productSelection: '${ $.provider }:options.mat.active_items.top_mat',
                // optionId: '${ $.provider }:options.mat.top_mat.option_id',
                topMatPrice : '${ $.provider }:mat.top_mat.price',
                pricingSummary: '${ $.provider }:price',
                subtotal: '${ $.provider }:price.subtotal',
                sizeRestricted: '${ $.provider }:size_restricted'
            },
            listens:{
                '${ $.provider }:editmode': 'updateSelection',
                '${ $.provider }:options.mat.sizes':'loadProductListIntoObservable',
                '${ $.provider }:options.size.width.integer': 'resetFilters',
                '${ $.provider }:options.size.width.tenth': 'resetFilters',
                '${ $.provider }:options.size.height.integer': 'resetFilters',
                '${ $.provider }:options.size.height.tenth': 'resetFilters'
            },
            imports: {
                options: '${ $.provider }:options',
                matActive: '${ $.provider }:options.mat.isActive',
                sizeRestricted: '${ $.provider }:size_restricted'
            }
        },
        resetFilters:function(){
            this.filters = false;
            this.searchValue = null;
        },
        initSelection: function() {
            var self = this;
            self.productSelection(customizerDataResolver.resolveTopMatSelection());
        },
        updateSelection: function(value) {
            if(!value) {
                this.initSelection();
            }
        },
        initialize: function() {
            this._super();
            var self = this;
            self.initTopMatTab();
            // self.matCount(1);
            //self.productSelection(self.defaultSelection);
            //self.currentSelection(self.defaultSelection);
            self.initSelection();
            ko.computed(function() {
                self.topMatPrice(self.productSelection().price);
            });
            self.productSelection.subscribe(function (value){
                //self.recalculatePrice(self);
                console.log(value);
            })
            //self.loadProductListIntoObservable();
            ko.observable.fn.silentUpdate = function(value) {
                this.notifySubscribers = function() {};
                this(value);
                this.notifySubscribers = function() {
                    ko.subscribable.fn.notifySubscribers.apply(this, arguments);
                };
            };
            this.isActive.subscribe(function(value){
                if (value){
                    //self.recalculatePrice(self);
                    if(self.topMatProducts().length) {
                        console.log(self.options);
                        self.loadProductListIntoObservable(self);
                    }
                }
            });
            this.filterChecked.subscribe(function(value){
                let filtersData = [];
                let attributeData = [];
                _.each(value, function (value) {
                    if (value) {
                        filtersData.push(JSON.parse(value));
                        attributeData.push(JSON.parse(value).attribute_code);
                    }
                });
                if (!_.isEmpty(filtersData)) {
                    self.removeAllFilter(self, attributeData);
                    self.filters = JSON.stringify(filtersData);
                    self.loadProductListIntoObservable(self);
                }
                if (_.isEmpty(filtersData)) {
                    self.filters = JSON.stringify(filtersData);
                    self.loadProductListIntoObservable(self);
                }
            });
            this.matActive.subscribe(function(value){
                if (value) {
                    //self.initSelection();
                    self.recalculatePrice(self);
                    console.log(self.options);
                    self.loadProductListIntoObservable(self);
                }
            })
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
        removeCheckedFilter:function(value){
            let self = this;
            let selectedValue = JSON.parse(value);
            self.filterChecked.remove(function(item){
                let selectedFilter = JSON.parse(item);
                if (selectedFilter.attribute_code === selectedValue.attribute_code){
                    return true;
                }
            });
            if (!_.isEmpty(self.filterChecked())) {
                let packFilters = [];
                _.each(self.filterChecked(),function(item){
                    packFilters.push(JSON.parse(item));
                });
                self.filters = JSON.stringify(packFilters);
            }
            return true;
        },
        findFrontendLabel:function(data){
            return filterUtils.findFrontendLabel(data,this.selectionFilters());
        },
        removeAllFilter:function(self,attributeData){
            self.allFilterChecked.remove(function(item){
                let value = JSON.parse(item);
                if (_.contains(attributeData,value.attribute_code)){
                    return true;
                }
            });
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
        next:function() {
            if(this.pageNumber() < this.totalPages()) {
                let result = this.pageNumber() + 1;
                this.offsetValue = result;
                this.pageNumber(result);
                this.currentPage(result);
                this.filters = [];
                this.packFilters();
                this.loadProductListIntoObservable(this);
            }
        },
        filterButton:function(){
            $("#toggle").fadeToggle("slow");
            if ($( "body" ).hasClass('pdp-filter-active')) {
                $( "body" ).removeClass( 'pdp-filter-active');
            } else {
                $( "body" ).addClass( 'pdp-filter-active');
            }
        },
        previous:function() {
            if(this.pageNumber() !== 0) {
                let result = this.pageNumber() - 1;
                this.offsetValue = result;
                this.pageNumber(result);
                this.currentPage(result);
                this.filters = [];
                this.packFilters();
                this.loadProductListIntoObservable(this);
            }
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

        initTopMatTab: function () {
            var self = this;
            var code = 'top-mat';
            var title = "Top Mat";
            matTypes.registerMat(title, code, self.isActive, self.sortOrder, self.selection, self.defaultSelection);
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
            self.sizeRestricted(false);
            self.productLoaded(false);
            data.pagination = {};
            data.sku = self.productSku;
            data.options = self.options;
            data.option = 'mat';
            data.optiontype = 'top-mat'
            data.active_item = self.options.mat.active_items.top_mat;
            data.search = self.searchValue;
            data.filters = self.filters;
            data.pageSize = this.productsPerPage;
            data.pagination.limit = self.productsPerPage;
            data.pagination.offset = self.offsetValue;
            data.selections = {width:null,height:null};
            result = performAjax.performAjaxOperation('customizer/option/getValues/','POST',data);
            result.done(function(response){
                if (response!==undefined && response.products !== undefined && response.active_item) {
                    let tempProduct = {};
                    response.products.top_mat.forEach(product =>{
                        if(!_.isEmpty(self.productSelection()) && product.id === response.active_item) {
                            tempProduct = product;
                        }
                    })
                    !_.isEmpty(tempProduct) ? self.productSelection.silentUpdate(tempProduct): '';
                    self.topMatProducts(response.products.top_mat);
                    if (response.product_total_count.top_mat!==undefined) {
                        self.productListCount(response.product_total_count.top_mat);
                        if (response.filters!==undefined) {
                            if (!_.isEmpty(self.filterChecked())){
                                self.filterChecked.silentUpdate([]);
                            }
                            self.selectAllFilters(self,response.filters);
                            self.selectionFilters(response.filters);
                        }
                    }
                } else {
                    self.topMatProducts([]);
                    self.productListCount(0);
                    self.sizeRestricted(true);
                }
                self.productLoaded(true);
                // if(response.error){
                //     console.log('error');
                //     var options = {
                //         type: 'popup',
                //         responsive: true,
                //         innerScroll: true,
                //         title: '',
                //         clickableOverlay: false,
                //         customTpl: sizeTpl,
                //         buttons: [{
                //             text: $.mage.__('OK'),
                //             class: 'size-restriction-modal',
                //             click: function () {
                //                 this.closeModal();
                //             }
                //         }]
                //     };
                //     // var popup = modal(options, $('#custom-popup-modal'));
                //     // $("#custom-popup-modal").html(sizeTpl);
                //     // $("#custom-popup-modal").modal("openModal");
                // }
                $('body').trigger('processStop');
            });
        },
        selectAllFilters:function(self,filters){
            _.each(filters,function(value,index){
                _.each(value,function(data,item){
                    if (data.all_filter){
                        self.allFilterChecked.push(data.filter_param);
                    }
                });
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
                        if(!_.isEmpty(self.productSelection()) && product.id === self.productSelection().id) {
                            self.productSelection(product);
                        }
                    })
                    self.productList(response.products);
                    if (response.product_total_count!==undefined) {
                        self.productListCount(response.product_total_count);
                    }
                }
                $('body').trigger('processStop');
                self.searchFocused(false);
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
