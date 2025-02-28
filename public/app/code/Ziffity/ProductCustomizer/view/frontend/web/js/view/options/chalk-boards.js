define([
    'jquery',
    'underscore',
    'ko',
    'Ziffity_ProductCustomizer/js/view/filter-utils',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'text!Ziffity_ProductCustomizer/template/optioninfo/chalk-board-info.html',
], function ($, _, ko, filterUtils, AbstractOption, stepNavigator, performAjax, customizerDataResolver, info) {
    'use strict';

    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(false),
            isVisible:ko.observable(false),
            defaultSelection: window.customizerConfig.options.chalk_board.active_item,
            optionId: window.customizerConfig.options.chalk_board.active_item.option_id,
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
            keywordUpdated:false,
            searchValue:null,
            yourSelections: ko.observableArray(),
            productsPerPage:12,//TODO: Have to set how many products should we set in admin store configurations and set that value here dynamically.
            offsetValue:0,
            position:10,
            template: 'Ziffity_ProductCustomizer/options/chalk-boards',
            //Filter changes below
            allFilterChecked:ko.observableArray([]),
            filterChecked:ko.observableArray([]).extend({ rateLimit: 50 }),
            selectionFilters:ko.observableArray([]),
            filters:false,
            pricing: ko.observable(),
            subtotal: ko.observable(),
            filterUtils:filterUtils,
            //custom pagination
            currentPage:ko.observable(1),
            exports: {
                productSelection: '${ $.provider }:options.chalk_board.active_item',
                optionId: '${ $.provider }:options.chalk_board.option_id',
                yourSelections: '${ $.provider }:your_selections',
                pricing: '${ $.provider }:price.chalk_board',
                subtotal: '${ $.provider }:price.subtotal'
            },
            listens:{
                '${ $.provider }:editmode': 'updateSelection',
                '${ $.provider }:options.size.width.integer': 'resetFilters',
                '${ $.provider }:options.size.width.tenth': 'resetFilters',
                '${ $.provider }:options.size.height.integer': 'resetFilters',
                '${ $.provider }:options.size.height.tenth': 'resetFilters'
            },
            imports: {
                options: '${ $.provider }:options',
                srcType: '${ $.provider }:srctype',
                yourSelections: '${ $.provider }:your_selections'
            }
        },
        resetFilters:function(){
            this.filters = false;
            this.searchValue = null;
        },
        initSelection: function() {
            var self = this;
            self.productSelection(customizerDataResolver.resolveChalkboardSelection());
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
                'Chalk Board',
                'chalk_board',
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
                if (!_.isEmpty(this.filterChecked())) {
                    this.filters = JSON.stringify([JSON.parse(this.filterChecked())]);
                }
                this.loadProductListIntoObservable(this);
            }
        },
        goToLastPage:function(){
            let result = this.totalPages();
            this.offsetValue = result;
            this.pageNumber(result);
            this.currentPage(result);
            this.filters = [];
            if (!_.isEmpty(this.filterChecked())) {
                this.filters = JSON.stringify([JSON.parse(this.filterChecked())]);
            }
            this.loadProductListIntoObservable(this);
        },
        goToFirstPage:function(){
            let result = 1;
            this.offsetValue = result;
            this.pageNumber(result);
            this.currentPage(result);
            this.filters = [];
            if (!_.isEmpty(this.filterChecked())) {
                this.filters = JSON.stringify([JSON.parse(this.filterChecked())]);
            }
            this.loadProductListIntoObservable(this);
        },
        next:function() {
            if(this.pageNumber() < this.totalPages()) {
                let result = this.pageNumber() + 1;
                this.offsetValue = result;
                this.pageNumber(result);
                this.currentPage(result);
                this.filters = [];
                if (!_.isEmpty(this.filterChecked())) {
                    this.filters = JSON.stringify([JSON.parse(this.filterChecked())]);
                }
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
        findFrontendLabel:function(data){
            return filterUtils.findFrontendLabel(data,this.selectionFilters());
        },
        previous:function() {
            if(this.pageNumber() !== 0) {
                let result = this.pageNumber() - 1;
                this.offsetValue = result;
                this.pageNumber(result);
                this.currentPage(result);
                this.filters = [];
                if (!_.isEmpty(this.filterChecked())) {
                    this.filters = JSON.stringify([JSON.parse(this.filterChecked())]);
                }
                this.loadProductListIntoObservable(this);
            }
        },
        convertPrice:function(price){
            //TODO:// Have to change the currency format.
            return priceUtils.formatPrice(price);
        },
        clearSearch:function(){
            let self =this;
            self.searchQuery(null);
            self.searchValue = null;
            self.loadProductListIntoObservable(self);
        },

        loadProductListIntoObservable:function() {
            let self = this;
            let data = {};
            let result;
            data.pagination = {};
            self.productLoaded(false);
            data.sku = self.productSku;
            data.options = self.options;
            data.option = 'chalkboards';
            data.search = self.searchValue;
            data.filters = self.filters;
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
                    if (response.product_total_count.chalkboards!==undefined) {
                        self.productListCount(response.product_total_count.chalkboards);
                        if (response.filters!==undefined) {
                            if (!_.isEmpty(self.filterChecked())){
                                self.filterChecked.silentUpdate([]);
                            }
                            self.selectAllFilters(self,response.filters);
                            self.selectionFilters(response.filters);
                        }
                    }
                } else {
                    self.productList([]);
                    self.productListCount(0);
                }
                self.productLoaded(true);
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
