define([
    'Magento_Ui/js/modal/modal-component',
    'underscore',
    'uiRegistry',
    'ko',
    'jquery',
], function (modal, _, registry, ko, $) {
    'use strict';
    return modal.extend({
        /**
         * Initializes component.
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super();
            return this;
        },

        /**
         * Accept changes in modal by not preventing them.
         * Can be extended by exporting 'gatherValues' result somewhere
         */
        actionDone: function () {
            this.valid = true;
            this.elems().forEach(this.validate, this);
            if (this.valid) {
                let OPENINGS_ADMIN = window.OpeningObject;
                if (typeof OPENINGS_ADMIN !== 'undefined' && OPENINGS_ADMIN !== null) {
                    var error = OPENINGS_ADMIN.error;
                    if (error) {
                        alert('Please check Openings Tab.');
                        return false;
                    }

                    var openings = OPENINGS_ADMIN.exportList();
                    if (typeof openings !== 'undefined' && openings !== null) {
                        registry.get('index=opening_data').value(JSON.stringify(openings));

                        if (typeof window.productJson !== 'undefined' && window.productJson !== null) {
                            registry.get('index=opening_size').value(JSON.stringify(window.productJson));
                        }
                    }
                }
                this.closeModal();
            }
        },
    });
});
