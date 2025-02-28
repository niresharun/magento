
 define([
    'underscore',
    'Magento_Bundle/js/components/bundle-dynamic-rows-grid'
], function (_, bundleDynamicRowsGrid) {
    'use strict';

    return bundleDynamicRowsGrid.extend({

        /**
         * Init header elements
         */
         initHeader: function () {
            var labels = [],
                data;
                var self = this;

            if (!this.labels().length) {
                _.each(this.childTemplate.children, function (cell) {
                    data = this.createHeaderTemplate(cell.config);
                    cell.config.labelVisible = false;
                    _.extend(data, {
                        defaultLabelVisible: data.visible(),
                        label: cell.config.label,
                        name: cell.name,
                        required: !!cell.config.validation,
                        columnsHeaderClasses: cell.config.columnsHeaderClasses,
                        extendedLabel: cell.config.extendedLabel,
                        sortOrder: cell.config.sortOrder
                    });
                    labels.push(data);
                }, this);
                this.labels(_.sortBy(labels, 'sortOrder'));
            }
        },

    });
});
