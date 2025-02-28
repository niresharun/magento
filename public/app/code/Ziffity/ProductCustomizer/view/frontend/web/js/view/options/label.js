define([
        'jquery',
        'underscore',
        'ko',
        'Ziffity_ProductCustomizer/js/perform-ajax',
        'Ziffity_ProductCustomizer/js/view/options/abstract-option',
        'Ziffity_ProductCustomizer/js/model/step-navigator',
        'Magento_Catalog/js/price-utils',
        'Ziffity_ProductCustomizer/js/model/label-types',
        'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
        'text!Ziffity_ProductCustomizer/template/optioninfo/label-info.html',
    ],
    function ($, _, ko,performAjax, AbstractOption, stepNavigator, priceUtils, labelTypes, customizerDataResolver, info) {
        'use strict';
        return AbstractOption.extend({
            defaults: {
                isActive: ko.observable(false),
                isVisible:ko.observable(false),
                position: 85,
                index:2,
                labels: labelTypes.labels,
                template: 'Ziffity_ProductCustomizer/options/label',
                labelDataArray:ko.observableArray([]),
                labelFonts:ko.observableArray([]),
                fontColors:ko.observableArray([]),
                labelDimensions:ko.observable({width:0,height:15}),
                labelPosition:ko.observable("top"),
                exports: {
                    isActive: '${ $.provider }:options.label.isActive',
                    labelDataArray:'${ $.provider }:options.label.labelDataArray',
                    labelDimensions:'${ $.provider }:options.label.labelDimensions',
                    labelPosition:'${ $.provider }:options.label.labelPosition',
                    labelFonts:'${ $.provider }:options.label.labelFonts',
                    fontColors:'${ $.provider }:options.label.fontColors'
                },
                imports: {
                    options: '${ $.provider }:options'
                }
            },
            initialize: function () {
                this._super();
                let self = this;
                stepNavigator.registerStep(
                    'Text & Image Labels',
                    'image-labels',
                    this.isActive,
                    this.isVisible,
                    self.position,
                    self.sortOrder,
                    false,
                    false,
                    info
                );
                self.loadLabelData(self);
                return this;
            },
            updateCurrentLabelType: function (labelType) {
                let self = this;
                ko.utils.arrayForEach(self.labels(), function (element) {
                    element.isActive(false);
                    if (element.code == labelType.code) {
                        element.isActive(true);
                    }
                });
            },
            loadLabelData:function(self){
                let response =  performAjax.performNonAsyncAjaxOperation('customizer/option/getLabelData',
                    'POST',window.customizerConfig.productSku);
                response.done(function(response){
                    if (response.success){
                        self.labelFonts(self.convertFonts(response.label_data.fonts));
                        self.labelDimensions({height:response.label_data.size.height,
                        width:response.label_data.size.width});
                        self.labelPosition(response.label_data.position);
                        self.fontColors(response.label_data.text_colors);
                        self.labelDataArray(response.label_data);
                    }
                });
                $('body').trigger('processStop');
            },
            convertFonts:function(data){
                let result = [];
                let self = this;
                _.each(data,function(value){
                    let obj = {};
                    obj.name = value;
                    obj.family = "font-family: '"+value+"';";
                    self.loadFont(value);
                    result.push(obj);
                });
                return result;
            },
            loadFont:function (font_name) {
                require([
                    'https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js'
                ], function (webfont) {
                    webfont.load({
                        google: {
                            families: [font_name + ':400,400i,700,700i']
                        }
                    });
                });
            }
        });
    });
