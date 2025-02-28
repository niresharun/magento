define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
], function ($, _, ko, Component, stepNavigator) {
    'use strict';


    return Component.extend( {
        editmode: ko.observable(true),
        defaults: {
            template: 'Ziffity_ProductCustomizer/progress-bar',
            visible: true,
            steps:stepNavigator.steps,
            currentStep: stepNavigator.currentStep,
            imports: {
                editmode: '${ $.provider }:editmode',
            }
        },

        initialize: function() {
            this._super();
            //this.steps = ko.observableArray();

            // stepNavigator.createSteps(customizerData.getOptions());
            // stepNavigator.currentStep.subscribe(function(newVal){
            //
            //    // console.log(stepNavigator.getActiveItemIndex());
            //
            //     var ajaxData = {
            //     sku : newVal.sku,
            //     optionId : newVal.optionId,
            //     itemsPerPage : 20
            //     }
            //     //ajaxData['pageNumber'] = ?;
            //
            //
            //     $.ajax({
            //         url: customizerData.optionItemsAjaxUrl(),
            //         type: 'POST',
            //         data:ajaxData,
            //         dataType: 'json',
            //         success: function(res) {
            //             console.log(res);
            //         }
            //     })
            //
            //     console.log(newVal);
            // });
            //this.currentStep = ko.observable(stepNavigator.currentStep().sortOrder+1);
            //console.log(currentProgressValue);
           // this.progressBarData = window.customframeConfig.progressbardata;

            //this.initiateSteps(progressBarData);
            this.editmode.subscribe(function(value){
                if (value) {
                    setTimeout(function () {
                        var $element = $("#fstep-progress");
                        var offsetTop = $element.offset().top;
                        $("html, body").scrollTop(offsetTop);
                    });
                }
            })
        },

        //  currentProgressValue: function() {
        //     return stepNavigator.currentStep.code;
        //  },

        initiateSteps: function(progressdata) {
            var self = this;
            progressBarData.forEach(function (element, index) {
                console.log(element);
                self.steps.push(element);

            })
        },


    });
});
