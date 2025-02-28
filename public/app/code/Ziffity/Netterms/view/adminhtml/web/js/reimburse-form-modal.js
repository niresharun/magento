define([
    'jquery',
    'Magento_Ui/js/modal/modal-component',
], function ($, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            modules: {
                reimburseNetterms: '${ $.reimburseNetterms }'
            }
        },

        /**
         * Init ui component
         *
         * @returns {Element}
         */
        initialize: function () {
            return this._super();
        },

        /**
         * Open modal
         *
         * @param {Object} data
         * @public
         */
        openModal: function (data) {
            this.reimburseNetterms().elems().forEach(function (el) {
                el.enable();
            }, this);
            this._super();
        },

        /**
         * Close modal
         *
         * @public
         */
        closeModal: function () {
            this.reimburseNetterms().elems().forEach(function (el) {
                el.disable();
            }, this);

            this._super();
        },

        /**
         * Validate everything validatable in modal
         *
         * @param {Object} elem
         * @public
         */
        validate: function (elem) {
            if (elem) {
                this._super();
            }
        },

        /**
         * Send Ajax
         *
         * @public
         */
        sendAjax: function () {
            if ($('#netterms-reimburse-message') != null && $('#netterms-reimburse-message').length > 0) {
                $('.netterms-reimburse-modal').find('#netterms-reimburse-message').remove()
            }
            this.valid = true;
            this.elems().forEach(this.validate, this);
            if (this.valid) {
                let $customerId = this.source.data.customer_id;
                let $amount = this.source.get(this.dataScope).reimburse_netterms.amount;
                $.ajax({
                    url: this.url,
                    data: {'id': $customerId, 'amount': $amount },
                    type: 'post',
                    dataType: 'json',
                    /**
                     * @callback
                        */
                    success: $.proxy(function (response) {
                        if (!response.error) {
                            location.reload();
                        } else {
                            $('.netterms-reimburse-modal .modal-content').prepend('<div class="message message-error error" id="netterms-reimburse-message"><div>' +response.error+ '</div></div>');
                        }
                    }, this)
                });
            }
        }
    });
});
