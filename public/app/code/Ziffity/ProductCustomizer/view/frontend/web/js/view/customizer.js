define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'uiRegistry',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
], function ($, _, ko, Component, registry, stepNavigator, performAjax, customizerResolver) {
    'use strict';

    return Component.extend({
        //srcType: ko.observable(true),
        srcType: ko.observable(customizerResolver.srcType),
        editmode:ko.observable(false),
        selections:ko.observableArray(),
        wallcolor: ko.observable(),
        defaults: {
            exports: {
                srcType: '${ $.provider }:options.additional_data.src_type',
            },
            imports: {
                srcType:'${ $.provider }:src_type',
                options:'${ $.provider }:options',
                wallcolor: '${ $.provider }:options.additional_data.wallcolor.current_color'
            }
        },

        initialize: function() {
            this._super();
            var self = this;
            console.log(self.srcType());
            if (self.srcType() == 'default') {
               // self.calculateYourSelections(self);
            }
            var completedSteps = self.srcType() == 'default' ? stepNavigator.processedSteps(): stepNavigator.getAllStepCodes();
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('editmode', false);
            });
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('isMatEnabled', false);
            });
            console.log(self.srcType);
        },
        calculateYourSelections: function(self){
            self.selections();
            let data = {};
            var result;
            data.options = self.options;
            data.sku = customizerResolver.productSku;
            data.completedSteps = stepNavigator.getAllStepCodes();
            result = performAjax.performAjaxOperation('customizer/option/getYourSelections/','GET',data);
            result.done(function(response){
                $('body').trigger('processStop');
                if(response!== undefined){
                    self.selections(response);
                }
            }, self);
        }
    } );
});
