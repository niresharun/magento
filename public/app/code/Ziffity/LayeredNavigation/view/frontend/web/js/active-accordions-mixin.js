define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function (filterAction) {
        filterAction.applyChanges = wrapper.wrapSuper(
            filterAction.applyChanges,
            function () {
                window.activeAccordions = $(".filter-options-item.active")
                    .map((index, filterItem) => $(filterItem).data("filter-code"))
                    .toArray();
                this._super();
            }
        );

        return filterAction;
    }
});
