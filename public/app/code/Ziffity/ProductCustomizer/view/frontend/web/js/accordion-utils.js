define([
    'jquery',
    'underscore',
    'accordion',
    'jquery/ui'
], function ($, _) {
    'use strict';

    return {
        renderAccordion:function(element, lastElemIndex=0,removed = false){
            if (element.length) {
                if (element.accordion('instance') === undefined) {
                    element.accordion({active: [lastElemIndex], openedState: "active", multipleCollapsible: true, collapsible: true});
                } else {
                    element.accordion('destroy');
                    element.accordion({active: [lastElemIndex], openedState: "active", multipleCollapsible: true, collapsible: true});
                }
            }
            if (removed){
                element.accordion('destroy');
                element.accordion({active: [lastElemIndex], openedState: "active", multipleCollapsible: true, collapsible: true});
            }
        },

        destroyAccordion: function (element){
            if (element.length) {
                element.accordion('destroy');
            }
        },

        selectFontColor:function(fontColors){
            let result = _.first(fontColors);
            return result !== undefined ? result : null;
        }
    };
});
