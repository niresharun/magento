
/**
 * Customizer adapter for loading options
 *
 * @api
 */
 define([
    'jquery',
    'ko',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
     'mageUtils',
     'jquery/jquery-storageapi'
], function ($, ko, stepNavigator, storage, utils) {
    'use strict';

     // var cacheKey = 'customizer-data';
     // var buffer = $.Deferred();
     //
     // /**
     //  * @param {Object} data
     //  */
     // // saveData = function (data) {
     // //     storage.set(cacheKey, data);
     // // };

     // buffer = {
     //     data: {},
     //
     //     /**
     //      * @param {String} sectionName
     //      */
     //     bind: function (sectionName) {
     //         this.data[sectionName] = ko.observable({});
     //     },
     //
     //     /**
     //      * @param {String} sectionName
     //      * @return {Object}
     //      */
     //     get: function (sectionName) {
     //         if (!this.data[sectionName]) {
     //             this.bind(sectionName);
     //         }
     //
     //         return this.data[sectionName];
     //     },
     //
     //     /**
     //      * @return {Array}
     //      */
     //     keys: function () {
     //         return _.keys(this.data);
     //     },
     //
     //     /**
     //      * @param {String} sectionName
     //      * @param {Object} sectionData
     //      */
     //     notify: function (sectionName, sectionData) {
     //         if (!this.data[sectionName]) {
     //             this.bind(sectionName);
     //         }
     //         this.data[sectionName](sectionData);
     //     },
     //
     //     /**
     //      * @param {Object} sections
     //      */
     //     update: function (sections) {
     //         var sectionId = 0,
     //             sectionDataIds = $.cookieStorage.get('section_data_ids') || {};
     //
     //         _.each(sections, function (sectionData, sectionName) {
     //             sectionId = sectionData['data_id'];
     //             sectionDataIds[sectionName] = sectionId;
     //             storage.set(sectionName, sectionData);
     //             storageInvalidation.remove(sectionName);
     //             buffer.notify(sectionName, sectionData);
     //         });
     //         $.cookieStorage.set('section_data_ids', sectionDataIds);
     //     },
     //
     //     /**
     //      * @param {Object} sections
     //      */
     //     remove: function (sections) {
     //         _.each(sections, function (sectionName) {
     //             storage.remove(sectionName);
     //
     //             if (!sectionConfig.isClientSideSection(sectionName)) {
     //                 storageInvalidation.set(sectionName, true);
     //             }
     //         });
     //     }
     // };

     return {
        // options: ko.observableArray(window.customizerConfig.options),
        // productName: window.customizerConfig.productName,
        // productSku: window.customizerConfig.productSku,
        //
        // getOptions :  function() {
        //     console.log('getting the options');
        //     console.log(this.options());
        //     return this.options();
        // },
    }

});
