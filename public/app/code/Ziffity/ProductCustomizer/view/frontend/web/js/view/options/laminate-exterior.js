define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Catalog/js/price-utils',
    'Ziffity_ProductCustomizer/js/view/filter-utils',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/model/laminate-types',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
], function ($, _, ko,priceUtils,filterUtils, AbstractOption, stepNavigator, laminateTypes, performAjax, customizerDataResolver) {
    'use strict';

    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(true),
            laminateActive:ko.observable(),
            laminateExteriorProducts:ko.observableArray([]),
            productSelection:ko.observable(),
            selectedFrameDetails:ko.observable(),//TODO: Anyone can use this observable as this contains the product_id and if it is selected or not from the frame product options.
            productSku:window.customizerConfig.productSku,
            selection : ko.observable(),
            productList:ko.observableArray([]),
            sortOrder:1,
            template: 'Ziffity_ProductCustomizer/options/laminate-exterior',
            productListCount:ko.observable(0),
            pageNumber:ko.observable(1),
            placeholder:ko.observable('Search for product name...'),
            searchFocused:ko.observable(false),
            searchQuery:ko.observable(null),
            productLoaded: ko.observable(false),
            searchValue:null,
            keywordUpdated:false,
            productsPerPage:12,//TODO: Have to set how many products should we set in admin store configurations and set that value here dynamically.
            offsetValue:0,
            //Filter changes below
            allFilterChecked:ko.observableArray([]),
            filterChecked:ko.observableArray([]).extend({ rateLimit: 50 }),
            selectionFilters:ko.observableArray([]),
            filters:false,
            filterUtils:filterUtils,
            //custom pagination
            currentPage:ko.observable(1),
            exports: {
                productSelection: '${ $.provider }:options.laminate_finish.active_items.laminate_exterior',
                optionId: '${ $.provider }:options.laminate_finish.laminate_exterior.option_id',
            },
            imports: {
                options: '${ $.provider }:options',
                laminateActive: '${ $.provider }:options.laminate_finish.isActive',
            },
            listens: {
                '${ $.provider }:editmode': 'updateSelection',
                '${ $.provider }:options.size.width.integer': 'resetFilters',
                '${ $.provider }:options.size.width.tenth': 'resetFilters',
                '${ $.provider }:options.size.height.integer': 'resetFilters',
                '${ $.provider }:options.size.height.tenth': 'resetFilters'
            }
        },
        resetFilters:function(){
            this.filters = false;
            this.searchValue = null;
        },
        initSelection: function() {
            var self = this;
            self.productSelection(customizerDataResolver.resolveExteriorLaminateSelection());
        },
        updateSelection: function(value) {
            if(!value) {
                this.initSelection();
            }
        },
        initialize: function() {
            this._super();
            let self = this;
            self.initLaminateExterior();
            self.initSelection();
            //self.productSelection(self.defaultSelection);
            this.isActive.subscribe(function(value){
                if (value && !self.laminateExteriorProducts().length){
                    self.loadProductListIntoObservable(self);
                }
            });
            this.laminateActive.subscribe(function(value){
                if (value) {
                    self.loadProductListIntoObservable(self);
                }
            });
            this.productSelection.subscribe(function (value){
                //TODO: Will use this function in future to export the product selection to other options when needed.
                console.log(value);
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
        findFrontendLabel:function(data){
            return filterUtils.findFrontendLabel(data,this.selectionFilters());
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
            $("#toggle").fadeToggle("slow");
            if ($( "body" ).hasClass('pdp-filter-active')) {
                $( "body" ).removeClass( 'pdp-filter-active');
            } else {
                $( "body" ).addClass( 'pdp-filter-active');
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
        initLaminateExterior: function () {
            let self = this;
            let code = 'Exterior Laminate';
            laminateTypes.registerLaminate(code, self.isActive, self.sortOrder,
                self.selection, self.defaultSelection,
                require.toUrl('Ziffity_ProductCustomizer/images/laminate/laminate-exterior.png'));
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
            data.option = 'laminate';
            data.search = self.searchValue;
            data.filters = self.filters;
            data.pageSize = this.productsPerPage;
            data.pagination.limit = self.productsPerPage;
            data.pagination.offset = self.offsetValue;
            data.optiontype = 'laminate-exterior'
            data.selections = {width:null,height:null};
            result = performAjax.performAjaxOperation('customizer/option/getValues/','POST',data);
            result.done(function(response){
                if (response!==undefined && response.products !== undefined) {
                    response.products.laminate_exterior.forEach(product =>{
                        if(product.id == self.productSelection().id) {
                            self.productSelection(product);
                        }
                    });
                    self.laminateExteriorProducts(response.products.laminate_exterior);
                    // _.each(response.products.laminate_exterior,function(item){
                    //     self.productSelection(null);
                    //     if (item.is_default !== "0"){
                    //         self.productSelection(item.entity_id);
                    //     }
                    // });
                    //pagination and filter code
                    if (response.product_total_count!==undefined) {
                        self.productListCount(response.product_total_count.laminate_exterior);
                        if (response.filters!==undefined) {
                            if (!_.isEmpty(self.filterChecked())){
                                self.filterChecked.silentUpdate([]);
                            }
                            self.selectAllFilters(self,response.filters);
                            self.selectionFilters(response.filters);
                        }
                    }
                } else {
                    self.laminateExteriorProducts([]);
                    self.productListCount(null);
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
    });
});
