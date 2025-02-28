define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent'
], function ($, _, ko, Component) {
    'use strict';

    return Component.extend({
        editmode: ko.observable(true),
        defaults: {
            template: 'Ziffity_ProductCustomizer/option-renderer',
            imports: {
                editmode: '${ $.provider }:editmode',
            }
        },
        initialize: function() {
            this._super();
        },
        // getOptions: function() {
        //     var self = this;
        //     var components;
        //     options.forEach(function (element, index){
        //         console.log(element);
        //         console.log(index);
        //     });
        // }
    } );
});
