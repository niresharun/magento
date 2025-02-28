define([
    'jquery',
    'ko',
], function ($, ko) {
    'use strict';
    let laminates = ko.observableArray();
    return {
        laminates:laminates,
        registerLaminate : function(code, isActive, sortOrder, selection, defaultSelection,image) {
            const laminate = {
                code: code,
                isActive: isActive,
                sortOrder: sortOrder,
                selection: selection,
                defaultSelection: defaultSelection,
                image:image
            }
            laminates.push(laminate);
            laminates().sort(this.sortItems);
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
