define([
    'jquery',
    'underscore',
    'ko',
    'Fraction',
    'Ziffity_ProductCustomizer/js/model/customizer-helper',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Magento_Catalog/js/price-utils',
    'Ziffity_ProductCustomizer/js/model/mat-types',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'text!Ziffity_ProductCustomizer/template/optioninfo/mat-info.html',
], function ($, _, ko, Fraction, customizerHelper, AbstractOption, stepNavigator, priceUtils, matTypes, customizerDataResolver, performAjax, info) {
    'use strict';

    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(false),
            isVisible:ko.observable(false),
            productSku:window.customizerConfig.productSku,
            // position: parseInt(window.customizerConfig.options.mat.position),
            index:2,
            priceInvalid: ko.observable(false),
            reveals: ko.observableArray([]),
            revealSelected: ko.observable(),
            availableMats:ko.observableArray(),
            mats: matTypes.mats,
            matWide: ko.observableArray([]),
            matWideSelected: ko.observable(),
            matHigh: ko.observableArray([]),
            showReveal: false,
            matHighSelected: ko.observable(),
            selectedWidthInteger:ko.observable(),
            selectedHeightInteger:ko.observable(),
            selectedWidthFractional:ko.observable(),
            selectedHeightFractional:ko.observable(),
            isMatEnabled: ko.observable(false),
            yourSelections: ko.observableArray(),
            graphicWidth: ko.observable(0),
            graphicHeight: ko.observable(0),
            viewableWidth: ko.observable(0),
            viewableHeight: ko.observable(0),
            openingDataArray:ko.observableArray([]),
            matOverLap: ko.observable(),
            productsPerPage:2,
            offsetValue:0,
            position:30,
            topMatPrice: ko.observable(),
            middleMatPrice: ko.observable(),
            bottomMatPrice: ko.observable(),
            matSizeLock: ko.observable(),
            pricing:ko.observable(),
            matCount: ko.observable(),
            template: 'Ziffity_ProductCustomizer/options/mat',
            exports: {
                isActive: '${ $.provider }:options.mat.isActive',
                matSizeLock: '${ $.provider }:options.mat.sizes.sizes_lock',
                matWideSelected: '${ $.provider }:options.mat.sizes.top.integer',
                matHighSelected: '${ $.provider }:options.mat.sizes.top.tenth',
                revealSelected: '${ $.provider }:options.mat.sizes.reveal',
                isMatEnabled: '${ $.provider }:isMatEnabled',
                matCount: '${ $.provider }:options.mat.mat_count',
                matOverLap: '${ $.provider }:options.mat.overlap',
                yourSelections: '${ $.provider }:your_selections',
                pricing: '${ $.provider }:price.mat',
                viewableWidth:'${ $.provider }:options.mat.viewableWidth',
                viewableHeight:'${ $.provider }:options.mat.viewableHeight',
                graphicWidth:'${ $.provider }:options.mat.graphicWidth',
                graphicHeight:'${ $.provider }:options.mat.graphicHeight',
            },
            imports: {
                options: '${ $.provider }:options',
                selectedWidthInteger: '${ $.provider }:options.size.width.integer',
                selectedWidthFractional: '${ $.provider }:options.size.width.tenth',
                selectedHeightInteger: '${ $.provider }:options.size.height.integer',
                selectedHeightFractional: '${ $.provider }:options.size.height.tenth',
                topMatPrice : '${ $.provider }:mat.top_mat.price',
                middleMatPrice : '${ $.provider }:mat.middle_mat.price',
                bottomMatPrice : '${ $.provider }:mat.bottom_mat.price',
                priceInvalid: '${ $.provider }:price_invalid',
                openingDataArray: '${ $.provider }:options.openings.openingDataArray',
            },
            listens:{
                '${ $.provider }:editmode': 'updateSelection'
            },
        },
        initSelection: function() {
            var self = this;
            var matSelection = customizerDataResolver.resolveMatSelection();
            if(matSelection){
                self.matWideSelected(matSelection.sizes.top.integer);
                self.matHighSelected(matSelection.sizes.top.tenth);
                self.revealSelected(matSelection.sizes.reveal);
                self.matCount(matSelection.mat_count);
                self.matOverLap(matSelection.overlap);
            }
        },
        updateSelection: function(value) {
            if(!value) {
                this.initSelection();
            }
        },
        initialize: function () {
            this._super();
            let self = this;
            self.isMatEnabled(true);
            self.initMatInteger();
            self.initMatFraction();
            self.calculateSizes();
            self.initSelection();
            ko.computed(function() {
                var price = 0;
                if(!isNaN(self.topMatPrice())){
                    price += self.topMatPrice();
                }
                if(!isNaN(self.middleMatPrice())){
                    price += self.middleMatPrice();
                }
                if(!isNaN(self.bottomMatPrice())){
                    price += self.bottomMatPrice();
                }
                self.pricing(price);
            });
            var matSelection = customizerDataResolver.resolveMatSelection();
            self.matSizeLock = ko.computed(function(){
                return matSelection.sizes.sizes_lock || (self.openingDataArray().length > 1);
            })
            stepNavigator.registerStep(
                'Mat',
                'mat',
                this.isActive,
                this.isVisible,
                self.position,
                self.sortOrder,
                false,
                false,
                info
            );
            var sizes = window.customizerConfig.options.mat.sizes;
            this.isActive.subscribe(function (value) {
                if (value) {
                    self.calculateSizes();
                    self.priceInvalid(false);

                    //self.calculateYourSelections(self);
                }
            });
            if (sizes.reveal !== undefined) {
                self.showReveal = true;
                self.initMatReveal();
            }
            //var matOptions = customizerDataResolver.resolveMat();
        },
        initMatInteger: function () {
            var self = this;
            var matIntegerOption = window.customizerConfig.matSizeConfig.matInteger;
            matIntegerOption.forEach(function (element, index) {
                self.matWide.push({label:element.label, value:element.value});
            });
            self.matWideSelected(window.customizerConfig.options.mat.sizes.top.integer)
        },
        initMatFraction: function () {
            var self = this;
            var matFractionOption = window.customizerConfig.matSizeConfig.matFraction;
            matFractionOption.forEach(function (element) {
                self.matHigh.push({label:element.label, value:element.value});
            });
            self.matHighSelected(window.customizerConfig.options.mat.sizes.top.tenth)
        },
        updateCurrentMatType: function (matType) {
            var self = this;
            ko.utils.arrayForEach(self.mats(), function (element) {
                element.isActive(false);
                if (element.code == matType.code) {
                    element.isActive(true);
                }
            });
        },
        initMatReveal: function () {
            var self = this;
            var revealsOptions = window.customizerConfig.matSizeConfig.reveals;
            Object.keys(revealsOptions).forEach(key => {
                self.reveals.push({label:key, value:revealsOptions[key]});
            });
        },
        calculateSizes: function () {
            var self = this;
            var overlap = window.customizerConfig.options.mat.overlap == undefined ?
                0.625 :
                new Fraction(window.customizerConfig.options.mat.overlap).valueOf();
            var width_g = customizerHelper.getFullNumber({
                'integer': self.selectedWidthInteger(),
                'tenth' : self.selectedWidthFractional()
            });
            var height_g = customizerHelper.getFullNumber({
                'integer': self.selectedHeightInteger(),
                'tenth' : self.selectedHeightFractional()
            });

            self.graphicWidth(new Fraction(width_g).toFraction(true)+'"');
            self.graphicHeight(new Fraction(height_g).toFraction(true)+'"');
            self.viewableWidth(new Fraction(width_g - (overlap * 2)).toFraction(true)+'"');
            self.viewableHeight(new Fraction(height_g - (overlap * 2)).toFraction(true)+'"');
            // console.log(selectedHeightFractional, selectedHeightInteger, selectedWidthFractional, selectedWidthInteger);
        },
    });
});
