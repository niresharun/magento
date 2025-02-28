define([
    'jquery',
    'ko',
], function ($, ko) {
    'use strict';

    let mats = ko.observableArray();

    return {
        mats:mats,

        registerMat : function(title, code, isActive, sortOrder, selection, defaultSelection) {
            const mat = {
                title:title,
                code: code,
                isActive: isActive,
                sortOrder: sortOrder,
                selection: selection,
                defaultSelection: defaultSelection
            }
            mats.push(mat);
            mats().sort(this.sortItems);
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
