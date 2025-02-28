define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'mage/url'
], function ($, _, ko, Component,urlBuilder) {
    'use strict';
    return Component.extend(
        {
            defaults: {
                editmode: ko.observable(),
                enableSaveDesignButton:ko.observable(false),
                saveDesignShareCode:window.customizerConfig.options.save_design.share_code,
                editSaveDesign:ko.observable(window.customizerConfig.options.save_design.edit_mode),
                imports: {
                    options: '${ $.provider }:options',
                    editmode: '${ $.provider }:editmode',
                },
            },
            initialize: function () {
                this._super();
                return this;
            },
            addToSaveDesign: function () {
                let self = this;
                if (!self.enableSaveDesignButton()) {
                    let ajax = self.sendPostRequest('saveddesigns/save/product',{
                        form_key: $.mage.cookies.get('form_key'),
                        options: self.options,
                        product: window.customizerConfig.productId
                    });
                    ajax.done(function (data) {
                        if (data.success === true) {
                            self.enableSaveDesignButton(true);
                            self.savedDesignId = data.id;
                        }
                        if (data.success === false) {
                            self.enableSaveDesignButton(false);
                        }
                    });
                }
                if (self.enableSaveDesignButton()){
                    let data = {};
                    data.form_key = $.mage.cookies.get('form_key');
                    data.id = self.savedDesignId;
                    let ajax = self.sendPostRequest('saveddesigns/save/delete',data);
                    ajax.done(function (data) {
                        if (data.success === true) {
                            self.enableSaveDesignButton(false);
                            self.savedDesignId = null;
                        }
                        if (data.success === false) {
                            self.enableSaveDesignButton(true);
                        }
                    });
                }
            },
            sendPostRequest:function(url,params)
            {
                return $.post(urlBuilder.build(url),params);
            },
            saveDesignAsEdit:function()
            {
                let self = this;
                let ajax = self.sendPostRequest('saveddesigns/save/saveEdit',{
                    form_key: $.mage.cookies.get('form_key'),
                    options: self.options,
                    share_code: self.saveDesignShareCode,
                    product: window.customizerConfig.productId
                });
                ajax.done(function (data) {
                    if (data.success === true) {
                        window.open(urlBuilder.build('saveddesigns/lists/index/'),'_self');
                    }
                });
            }
        },
    );
});
