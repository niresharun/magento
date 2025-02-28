define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'Ziffity_ProductCustomizer/js/model/tab-popup',
], function ($, _, ko, Component, tabPopup) {
    return Component.extend({
        defaults:{
            listens: {
                '${ $.provider }:reset': 'initSelection'
            }
        },
        initialize:function (){
            this._super();
            let self = this;
            ko.subscribable.fn.subscribeChanged = function(callback) {
                var previousValue;
                this.subscribe(function(_previousValue) {
                    previousValue = _previousValue;
                }, undefined, 'beforeChange');
                this.subscribe(function(latestValue) {
                    callback(latestValue, previousValue );
                });
            };
            ko.observable.fn.silentUpdate = function(value) {
                this(value);
                this.notifySubscribers = function() {
                    ko.subscribable.fn.notifySubscribers.apply(this, arguments);
                };
            };
        },
        showDetails: function (value, event){
            let radio = $(event.currentTarget).parent().find('input[type=radio]');
            let type = jQuery(radio).attr('name');
            tabPopup.showPopup(type, value);
        },
    });
});
