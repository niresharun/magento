define([
    'Magento_Ui/js/form/components/button',
    'underscore'
], function (Button, _) {
    'use strict';

    return Button.extend({
        defaults: {
            customerId: null
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.visible(!_.isEmpty(this.customerId));
            return this;
        }
    });
});
