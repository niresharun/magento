define([
    'jquery',
    'ko',
], function ($, ko) {
    'use strict';
    let headers = ko.observableArray();
    return {
        headers:headers,
        registerHeader : function(code, isActive, sortOrder, selection, defaultSelection) {
            const header = {
                code: code,
                isActive: isActive,
                sortOrder: sortOrder,
                selection: selection,
                defaultSelection: defaultSelection
            }
            headers.push(header);
            headers().sort(this.sortItems);
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
