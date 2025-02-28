define([
    'jquery',
    'underscore',
    'ko',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Magento_Catalog/js/price-utils',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Ziffity_ProductCustomizer/js/view/filter-utils',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'text!Ziffity_ProductCustomizer/template/optioninfo/frame-info.html',
], function ($, _, ko, AbstractOption, stepNavigator,priceUtils,performAjax, filterUtils, customizerDataResolver, info) {
    'use strict';

    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(false),
            isVisible:ko.observable(false),
            productSelection:ko.observable(),
            priceInvalid: ko.observable(false),
            // position: parseInt(window.customizerConfig.options.frame.position),
            position: 5,
            defaultSelection: window.customizerConfig.options.frame.active_item,
            optionId: window.customizerConfig.options.frame.active_item.option_id,
            index: 2,
            placeholder:ko.observable('Search for product name...'),
            searchFocused:ko.observable(false),
            selectedFrameDetails:ko.observable(),//TODO: Anyone can use this observable as this contains the product_id and if it is selected or not from the frame product options.
            productSku:window.customizerConfig.productSku,
            selection : ko.observable(),
            productList:ko.observableArray([]),
            productListCount:ko.observable(0),
            currentSelection:ko.observable(),
            yourSelections: ko.observableArray(),
            pageNumber:ko.observable(1),
            searchQuery:ko.observable(null),
            keywordUpdated:false,
            searchValue:null,
            order: 5,
            productsPerPage:12,//TODO: Have to set how many products should we set in admin store configurations and set that value here dynamically.
            offsetValue:0,
            template: 'Ziffity_ProductCustomizer/options/frame',
            pricing: ko.observable(),
            pricingtext: '',
            pricingSummary: ko.observableArray(),
            //Filter changes below
            allFilterChecked:ko.observableArray([]),
            filterChecked:ko.observableArray([]).extend({ rateLimit: 50 }),
            selectionFilters:ko.observableArray([]),
            sizeRestricted: ko.observable(),
            productLoaded: ko.observable(false),
            filters:false,
            subtotal: ko.observable(),
            filterUtils:filterUtils,
            //custom pagination
            //TODO: Do the pagination optimisation for all the files which has this feature.
            currentPage:ko.observable(1),
            exports: {
                productSelection: '${ $.provider }:options.frame.active_item',
                optionId: '${ $.provider }:options.frame.option_id',
                yourSelections: '${ $.provider }:your_selections',
                pricing: '${ $.provider }:price.frame',
                priceInvalid: '${ $.provider }:price_invalid',
                pricingSummary: '${ $.provider }:price.summary',
                subtotal: '${ $.provider }:price.subtotal',
                sizeRestricted: '${ $.provider }:size_restricted'
            },
            imports: {
                options: '${ $.provider }:options',
                srcType: '${ $.provider }:srctype',
                yourSelections: '${ $.provider }:your_selections',
                sizeRestricted: '${ $.provider }:size_restricted'
            },
            listens:{
                '${ $.provider }:editmode': 'updateSelection',
                '${ $.provider }:options.size.width.integer': 'resetFilters',
                '${ $.provider }:options.size.width.tenth': 'resetFilters',
                '${ $.provider }:options.size.height.integer': 'resetFilters',
                '${ $.provider }:options.size.height.tenth': 'resetFilters'
            },
        },
        initSelection: function() {
            var self = this;
            self.productSelection(customizerDataResolver.resolveFrameSelection());
        },
        updateSelection: function(value) {
            if(!value) {
                this.initSelection();
            }
        },
        resetFilters:function(){
           this.filters = false;
           this.searchValue = null;
        },
        initialize: function() {
            let self = this;
            this._super();
            self.initSelection();
            self.productSelection.subscribe(function (value){
                self.priceInvalid(true);
                //self.recalculatePrice(self);
            })
            ko.computed(function() {
               // self.pricing(self.productSelection().price);
            });
            stepNavigator.registerStep(
                'Frame',
                'frame',
                this.isActive,
                this.isVisible,
                self.position,
                self.order,
                false,
                false,
                info
            );
            this.isActive.subscribe(function(value){
                if (value){
                    self.sizeRestricted(false);
                    self.loadProductListIntoObservable(self);
                    self.priceInvalid(false);
                   // self.recalculatePrice(self);
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
                    if (value) {
                        //self.calculateYourSelections(self);
                        self.loadProductListIntoObservable(self);
                    }
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
                self.currentSelection(value);
            });
            //custom pagination
            // Generate an array of page numbers
            self.pageNumbers = ko.computed(function () {
                let totalPages = Math.ceil(self.productListCount()/self.productsPerPage);
                let pagesArray = [];
                for (let i = 1; i <= totalPages; i++) {
                    pagesArray.push(i);
                }
                if (self.pageNumber() > 5){
                    return _.last(pagesArray,5);
                }
                return _.first(pagesArray,5);
            });
        },
        showStopLoader:function(self,display){
           performAjax.showStopLoader(display);
        },
        removeCheckedFilter:function(value){
            let self = this;
            this.showStopLoader(self,'show');
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
            this.showStopLoader(self,'hide');
            return true;
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
        loadProductListIntoObservable:function(self){
            let data = {};
            let result;
            self.sizeRestricted(false);
            self.productLoaded(false);
            data.pagination = {};
            data.sku = self.productSku;
            data.option = 'frame';
            data.options = self.options;
            data.active_item = self.options.frame.active_item.id;
            data.search = self.searchValue;
            data.filters = self.filters;
            data.pagination.limit = self.productsPerPage;
            data.pagination.offset = self.offsetValue;
            data.selections = {width:null,height:null};
            performAjax.showStopLoader('show');
            result = performAjax.performAjaxOperation('customizer/option/getValues/','POST',data);
            result.done(function(response){
                if (response!==undefined && response.products !== undefined && response.active_item) {
                    let tempProduct = {};
                    response.products.forEach(product =>{
                        if(!_.isEmpty(self.productSelection()) && product.id === response.active_item) {
                            tempProduct = product;
                        }
                    });
                    !_.isEmpty(tempProduct) ? self.productSelection.silentUpdate(tempProduct): '';
                    self.productList(response.products);
                    if (response.product_total_count!==undefined) {
                        self.productListCount(response.product_total_count);
                        if (response.filters!==undefined) {
                            if (!_.isEmpty(self.filterChecked())){
                                self.filterChecked.silentUpdate([]);
                            }
                            self.selectAllFilters(self,response.filters);
                            self.selectionFilters(response.filters);
                        }
                    }
                }else{
                    self.productList([]);
                    self.productListCount(null);
                    self.sizeRestricted(true);
                }
                self.productLoaded(true);
                performAjax.showStopLoader('hide');
                $('body').trigger('processStop');
            });
        },
        findFrontendLabel:function(data){
            return filterUtils.findFrontendLabel(data,this.selectionFilters());
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
        frameSelectedDetails:function(element,checked,self){
            if (checked) {
                self.selectedFrameDetails({element: element, checked: checked});
                console.log(self.selectedFrameDetails());
            }
        },
    });
});
