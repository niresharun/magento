define([
    'jquery',
    'mage/translate',
    'mage/url',
    'jquery-ui-modules/widget'
], function ($, $t, urlBuilder) {
    'use strict';

    $.widget('mage.catalogSaveDesign', {
        options: {
            processStart: null,
            processStop: null,
            messagesSelector: '[data-placeholder="messages"]',
            saveDesignButtonSelector: '#product-save-design',
            saveDesignButtonDisabledClass: 'disabled',
            saveDesignButtonTextWhileSaving: $t('Saving...'),
            saveDesignButtonTextAdded: $t('Saved'),
            saveDesignButtonTextDefault: $t('Save Design'),
        },

        /** @inheritdoc */
        _create: function () {
            this.bindSubmit();
            $(this.options.saveDesignButtonSelector).prop('disabled', false);
        },

        /**
         * @private
         */
        bindSubmit: function () {
            var self = this;
            this.element.on('click', function (e) {
                e.preventDefault();
                self.submitForm($('#product_addtocart_form'));
            });
        },

        /**
         * @return {Boolean}
         */
        isLoaderEnabled: function () {
            return this.options.processStart && this.options.processStop;
        },

        /**
         * Handler for the form 'submit' event
         *
         * @param {jQuery} form
         */
        submitForm: function (form) {
            var self = this,
                formData;

            self.disableSaveDesignButton();
            formData = new FormData(form[0]);

            // TODO: Dynamic Image Data
            // var image = $("<input name='image' />");
            // var imageData = document.getElementById("main").toDataURL('image/jpeg').replace('image/jpeg', 'image/octet-stream');
            var imageData = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAApgAAAKYB3X3/OAAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAANCSURBVEiJtZZPbBtFFMZ/M7ubXdtdb1xSFyeilBapySVU8h8OoFaooFSqiihIVIpQBKci6KEg9Q6H9kovIHoCIVQJJCKE1ENFjnAgcaSGC6rEnxBwA04Tx43t2FnvDAfjkNibxgHxnWb2e/u992bee7tCa00YFsffekFY+nUzFtjW0LrvjRXrCDIAaPLlW0nHL0SsZtVoaF98mLrx3pdhOqLtYPHChahZcYYO7KvPFxvRl5XPp1sN3adWiD1ZAqD6XYK1b/dvE5IWryTt2udLFedwc1+9kLp+vbbpoDh+6TklxBeAi9TL0taeWpdmZzQDry0AcO+jQ12RyohqqoYoo8RDwJrU+qXkjWtfi8Xxt58BdQuwQs9qC/afLwCw8tnQbqYAPsgxE1S6F3EAIXux2oQFKm0ihMsOF71dHYx+f3NND68ghCu1YIoePPQN1pGRABkJ6Bus96CutRZMydTl+TvuiRW1m3n0eDl0vRPcEysqdXn+jsQPsrHMquGeXEaY4Yk4wxWcY5V/9scqOMOVUFthatyTy8QyqwZ+kDURKoMWxNKr2EeqVKcTNOajqKoBgOE28U4tdQl5p5bwCw7BWquaZSzAPlwjlithJtp3pTImSqQRrb2Z8PHGigD4RZuNX6JYj6wj7O4TFLbCO/Mn/m8R+h6rYSUb3ekokRY6f/YukArN979jcW+V/S8g0eT/N3VN3kTqWbQ428m9/8k0P/1aIhF36PccEl6EhOcAUCrXKZXXWS3XKd2vc/TRBG9O5ELC17MmWubD2nKhUKZa26Ba2+D3P+4/MNCFwg59oWVeYhkzgN/JDR8deKBoD7Y+ljEjGZ0sosXVTvbc6RHirr2reNy1OXd6pJsQ+gqjk8VWFYmHrwBzW/n+uMPFiRwHB2I7ih8ciHFxIkd/3Omk5tCDV1t+2nNu5sxxpDFNx+huNhVT3/zMDz8usXC3ddaHBj1GHj/As08fwTS7Kt1HBTmyN29vdwAw+/wbwLVOJ3uAD1wi/dUH7Qei66PfyuRj4Ik9is+hglfbkbfR3cnZm7chlUWLdwmprtCohX4HUtlOcQjLYCu+fzGJH2QRKvP3UNz8bWk1qMxjGTOMThZ3kvgLI5AzFfo379UAAAAASUVORK5CYII=";
            // image.val(imageData);
            formData.append('image', imageData)

            $.ajax({
                url: urlBuilder.build('saveddesigns/save/product'),
                data: formData,
                type: 'post',
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,

                /** @inheritdoc */
                beforeSend: function () {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.processStart);
                    }
                },

                /** @inheritdoc */
                success: function (res) {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.processStop);
                    }

                    if (res.messages) {
                        $(self.options.messagesSelector).html(res.messages);
                    }

                    self.enableSaveDesignButton(res);
                }

            });
        },

        /**
         * @param {String} form
         */
        disableSaveDesignButton: function () {
            var saveDesignButton = $(this.options.saveDesignButtonSelector);

            saveDesignButton.addClass(this.options.saveDesignButtonDisabledClass);
            saveDesignButton.find('span').text(this.options.saveDesignButtonTextWhileSaving);
            saveDesignButton.prop('title', this.options.saveDesignButtonTextWhileSaving);
        },

        /**
         * @param {String} form
         */
        enableSaveDesignButton: function (res) {
            var self = this,
                saveDesignButton = $(this.options.saveDesignButtonSelector);
            if (res.success) {
                saveDesignButton.find('span').text(this.options.saveDesignButtonTextAdded);
                saveDesignButton.prop('title', this.options.saveDesignButtonTextAdded);
                saveDesignButton.addClass('save-success');
            }

            setTimeout(function () {
                saveDesignButton.removeClass(self.options.saveDesignButtonDisabledClass);
                saveDesignButton.find('span').text(self.options.saveDesignButtonTextDefault);
                saveDesignButton.prop('title', self.options.saveDesignButtonTextDefault);
                saveDesignButton.removeClass('save-success');
            }, 1000);
        }
    });

    return $.mage.catalogSaveDesign;
});
