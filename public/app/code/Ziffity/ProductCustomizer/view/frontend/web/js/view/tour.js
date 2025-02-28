define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent'
], function ($, _, ko, Component) {
    'use strict';

    return Component.extend( {
        initialize: function() {
            this._super();
        },
        tourEvent: function(){
            $(document).trigger('tour_event');
        }

    });
});
