define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'uiRegistry',
    'mage/template',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'text!Ziffity_ProductCustomizer/template/popup/your-selections.html'
], function ($, _, ko, Component, registry,template, stepNavigator, performAjax, your_selections) {
    'use strict';

    var position = 'right';

    return Component.extend({
        editmode: ko.observable(true),
        selections:ko.observableArray(),
        yourSelections: ko.observableArray(),
        productSku:window.customizerConfig.productSku,
        srcType: ko.observable(),
        finishCustomization: ko.observable(),
        defaults: {
            imports: {
                editmode: '${ $.provider }:editmode',
                options: '${ $.provider }:options',
                yourSelections: '${ $.provider }:your_selections',
                srcType: '${ $.provider }:options.additional_data.src_type',
                finishCustomization: '${ $.provider }:finish_customization'
            },
            exports: {
                yourSelections: '${ $.provider }:your_selections',
                selections: '${ $.provider }:selections',
            }
        },
        initialize: function(){
            this._super();
            var self = this;
            self.initSelections();

            var completedSteps = stepNavigator.getAllStepCodes();
            self.finishCustomization.subscribe(function(value){
                self.calculateDefaultSelections(self, completedSteps);
            })

            setInterval(function () {
                if(!self.selections().length > 0) {
                    self.calculateDefaultSelections(self, completedSteps);
                }
            }, 2000);
            console.log(self.options);

        },

        loadSelectionPopup: function() {
            var self = this;
            var completedSteps = self.srcType() == 'default' ? stepNavigator.processedSteps(): stepNavigator.getAllStepCodes();
            self.calculateYourSelections(self, completedSteps);
            var tpl = '';
            var response = self.yourSelections();
            tpl = template(your_selections, {response});
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('popup',{
                    header:'<h5>Your Selections</h5>',
                    content:tpl,
                    show:true,
                    position:position

                });
                $('body').addClass('slidein-active');
                var popup = document.querySelector('.customizer-slidein');
                var main_popup = document.querySelector('.customizer-main-popup')
                popup.style.display = 'flex';
                main_popup.style.cssText = 'animation:slide-in-'+position+' .5s ease; animation-fill-mode: forwards';
            });
        },
        initSelections: function() {
            var self = this;
            let result;
            var customizerData = self.options;
            result = performAjax.performAjaxOperation('customizer/option/getSelections/','POST',customizerData);
            if(result) {
                $('body').trigger('processStop');
            }
        },
        calculateYourSelections: function(self, completedSteps){
            self.yourSelections();
            let data = {};
            var result;
            data.options = self.options;
            data.sku = self.productSku;
            data.completedSteps = completedSteps;
            //result = performAjax.performAjaxOperation('customizer/option/getYourSelections/','POST',data);
            //TODO should update the data in sequence
            $.ajax({
                url:window.BASE_URL+'customizer/option/getYourSelections/',
                // showLoader:true,
                data:{data},
                type:'POST',
                cache:true,
                async:false,
                beforeSend:function(){
                    $('body').trigger('processStart'); // start loader
                }
            }).done(function(response){
                $('body').trigger('processStop');
                if(response!== undefined){
                    self.yourSelections(response);
                }
            }, self);
        },
        calculateDefaultSelections: function(self, completedSteps){
            let data = {};
            var result;
            data.options = self.options;
            data.sku = self.productSku;
            data.completedSteps = completedSteps;
            result = performAjax.performAjaxOperation('customizer/option/getYourSelections/','POST',data);
            $('body').trigger('processStop');
            result.done(function(response){
                if(response) {
                    self.selections.removeAll();
                    Object.keys(response).forEach(key => {
                        self.selections.push({label: key, value: response[key]});
                    });
                }
            }, self);
        }
    } );
});
