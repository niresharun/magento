define([
    'jquery',
    'ko',
], function ($, ko) {
    'use strict';
    let labels = ko.observableArray();
    return {
        labels:labels,
        registerLabel : function(code, isActive, sortOrder, selection, defaultSelection) {
            const label = {
                code: code,
                isActive: isActive,
                sortOrder: sortOrder,
                selection: selection,
                defaultSelection: defaultSelection
            }
            labels.push(label);
            labels().sort(this.sortItems);
        },
        /**
         * @param {Object} itemOne
         * @param {Object} itemTwo
         * @return {Number}
         */
        sortItems: function (itemOne, itemTwo) {
            return itemOne.sortOrder > itemTwo.sortOrder ? 1 : -1;
        },
    };
});
