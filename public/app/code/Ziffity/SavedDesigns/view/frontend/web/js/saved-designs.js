define(
    ['jquery', 'uiComponent', 'ko']
    ,function ($, Component, ko) {
        'use strict';
        return Component.extend({
            options: '',

            initialize: function () {
                this._super();
                //this.currentTime();
            },

            // addtoQuote: function (data, event){
            //     var self = this;
            //     //let data = {};
            //     let params = {};
            //     let addUrl = $(event.currentTarget).attr('data-addurl');
            //     let productId = $(event.currentTarget).attr('data-product-id');
            //     let designId = $(event.currentTarget).attr('data-design-id');
            //     let result;
            //     params = {
            //         designId: designId
            //     }
            //     self.getDesignById(params, self);
            //     params = {
            //         form_key: $.cookie('form_key'),
            //         product: productId,
            //         designId: designId,
            //         options: self.options.additional_data,
            //         related_product: '',
            //         item: 1,
            //         qty: 1
            //     };
            //     $.ajax({
            //         url: addUrl,
            //         // showLoader:true,
            //         type: 'POST',
            //         data: params,
            //         cache: true,
            //         beforeSend: function () {
            //             $('body').trigger('processStart'); // start loader
            //         },
            //
            //     }).done(function (response) {
            //
            //         $('body').trigger('processStop');
            //     });
            // },
            addToCartAction: function (data, event) {
                var self = this;
                //let data = {};
                let params = {};
                let addUrl = $(event.currentTarget).attr('data-addurl');
                let productId = $(event.currentTarget).attr('data-product-id');
                let designId = $(event.currentTarget).attr('data-design-id');
                let result;
                params = {
                    designId: designId
                }
                self.getDesignById(params, self);

                params = {
                    form_key: $.cookie('form_key'),
                    product: productId,
                    designId: designId,
                    options: self.options,
                    updateItem: false,
                    related_product: '',
                    item: 1,
                    qty: 1
                };
                $.ajax({
                    url: addUrl,
                    // showLoader:true,
                    type: 'POST',
                    data: params,
                    cache: true,
                    beforeSend: function () {
                        $('body').trigger('processStart'); // start loader
                    },

                }).done(function (response) {

                    $('body').trigger('processStop');
                });
            },
            addToQuoteAction: function (data, event) {
                var self = this;
                //let data = {};
                let params = {};
                let addUrl = $(event.currentTarget).attr('data-addurl');
                let productId = $(event.currentTarget).attr('data-product-id');
                let designId = $(event.currentTarget).attr('data-design-id');
                let result;
                params = {
                    designId: designId
                }
                self.getDesignById(params, self);
                params = {
                    form_key: $.cookie('form_key'),
                    product: productId,
                    designId: designId,
                    options: self.options,
                    updateItem: false,
                    related_product: '',
                    item: 1,
                    qty: 1
                };
                $.ajax({
                    url: addUrl,
                    // showLoader:true,
                    type: 'POST',
                    data: params,
                    cache: true,
                    beforeSend: function () {
                        $('body').trigger('processStart'); // start loader
                    },

                }).done(function (response) {

                    $('body').trigger('processStop');
                });
            },

            getDesignById: function(params, self){
                let baseUrl = window.location.protocol + "//" + window.location.host;
                if(params){
                    $.ajax({
                        url:baseUrl+'/saveddesigns/getdetails/product',
                        // showLoader:true,
                        type: 'POST',
                        data: params,
                        cache: true,
                        async: false,
                        beforeSend: function () {
                            $('body').trigger('processStart'); // start loader
                        },
                    }).done(function (response) {
                        $('body').trigger('processStop');
                        if(response) {
                            self.options = JSON.parse(response.saved_design);
                        }
                    }, self);
                }

            }

            // currentTime: ko.computed(function () {
            //     setInterval(function(){
            //         var currentTime = new Date().toLocaleTimeString();
            //         // console.log(currentTime);
            //     }, 1000);
            //     return currentTime;
            // })
        });
    }
);
