define([
    'jquery',
    'underscore',
    'ko',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Magento_Catalog/js/price-utils',
    'Ziffity_ProductCustomizer/js/model/header-types',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'text!Ziffity_ProductCustomizer/template/optioninfo/header-info.html',
    ], function ($, _, ko,performAjax, AbstractOption, stepNavigator, priceUtils, headerTypes, customizerDataResolver, info) {
    'use strict';
    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(false),
            isVisible:ko.observable(false),
            position: 80,
            index:2,
            headers: headerTypes.headers,
            template: 'Ziffity_ProductCustomizer/options/header',
            headerPosition:ko.observable('top'),
            headerDimensions:ko.observable({width:0,height:15}),
            headerDataArray:ko.observableArray([]),
            headerImageDataArray:ko.observableArray([]),
            headerTextDataArray:ko.observableArray([]),
            exports: {
                isActive: '${ $.provider }:options.header.isActive',
                headerPosition:'${ $.provider }:options.header.headerPosition',
                headerDimensions:'${ $.provider }:options.header.headerDimensions',
                headerDataArray:'${ $.provider }:options.header.headerDataArray',
                headerImageDataArray:'${ $.provider }:options.header.headerImageDataArray',
                headerTextDataArray:'${ $.provider }:options.header.headerTextDataArray'
            },
            imports: {
                options: '${ $.provider }:options',
                headerImageDataArray:'${ $.provider }:options.header.image_header.imageDataArray',
                headerTextDataArray:'${ $.provider }:options.header.text_header.textHeaderArray',
            },
            // listens: {
            //     '${ $.provider }:editmode': 'updateSelection',
            //     '${ $.provider }:reset': 'resetSelection'
            // }
        },
        initialize: function () {
            this._super();
            let self = this;
            stepNavigator.registerStep(
                'Header',
                'header',
                this.isActive,
                this.isVisible,
                self.position,
                self.sortOrder,
                false,
                false,
                info
            );
            self.loadData(self);
        },
        updateCurrentHeaderType: function (headerType) {
            var self = this;
            ko.utils.arrayForEach(self.headers(), function (element) {
                element.isActive(false);
                if (element.code == headerType.code) {
                    element.isActive(true);
                }
            });
        },
        loadData:function(self){
            let response =  performAjax.performNonAsyncAjaxOperation('customizer/option/getHeaderData',
                'POST',window.customizerConfig.productSku);
            response.done(function(response){
                if (response.success){
                    self.headerPosition(response.header_data.position);
                    self.headerDimensions(response.header_data.size);
                    self.headerDataArray(response.header_data);
                }
            });
            $('body').trigger('processStop');
        }
    });
});
