define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'Ziffity_ProductCustomizer/js/view/progress-bar'
], function ($, _, ko, Component, progressBar) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Ziffity_ProductCustomizer/option',
            visible: true
        },
        initialize: function() {
            this._super();
           
            this.print();
        },

        print: function() {
            var self = this;
            console.log(progressBar().currentStep());

        }

    } );
});
