define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'Ziffity_ProductCustomizer/js/view/option'
], function ($, _, ko, Component, option) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Ziffity_ProductCustomizer/options',
            visible: true
        },
        initialize: function() {
            this._super();
           
            
        },

        // print: function() {
        //     var self = this;
        //     console.log(progressBar().currentStep());

        // }

    } );
});
