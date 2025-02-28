define([
    'jquery',
    'underscore',
    'ko',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/model/header-types',
    'Ziffity_ProductCustomizer/js/accordion-utils',
    'jquery/ui'
], function ($, _, ko, AbstractOption, stepNavigator, headerTypes,accordion) {
    'use strict';

    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(false),
            sortOrder:1,
            template: 'Ziffity_ProductCustomizer/options/image-header',
            optionId: '1',
            overallHeaderSize:ko.observable(''),
            imageDataArray:ko.observableArray([]),
            imageIncrement: 1,
            headerDataArray:ko.observableArray([]),
            defaultData:{width_inch:'4 3/8', height_inch:'1 1/2',
                dev_top_inch:'5 7/16',dev_left_inch:'0'},
            exports: {
                optionId: '${ $.provider }:options.header.image-header.option_id',
                imageDataArray: '${ $.provider }:options.header.image-header.imageDataArray'
            },
            imports: {
                options: '${ $.provider }:options',
                headerDataArray:'${ $.provider }:options.header.headerDataArray'
            },
            listens:{
                '${ $.provider }:options.header.headerDataArray':'loadImageData'
            }
        },
        initialize: function() {
            this._super();
            let self = this;
            self.initImageHeader();
            self.loadImageData(self.headerDataArray());
        },
        initImageHeader: function() {
            let self = this;
            let code  = 'Image Header';
            headerTypes.registerHeader(code, self.isActive, self.sortOrder, self.selection, self.defaultSelection);
        },
        loadImageData:function(headerDataArray){
            let self = this;
            _.each(headerDataArray.images, function (item, index) {
                let newInstance = new self.createNewImageFields(
                    {
                        imageHeaderName: 'Image Header ' + self.imageIncrement++,
                        fileData: item.url,
                        fileName: item.url,
                    }, item);
                self.imageDataArray.push(newInstance);
            });
        },
        createNewImageFields:function(obj,item){
            this.imageHeaderName = ko.observable(obj.imageHeaderName);
            this.fileData = ko.observable(obj.fileData);
            this.fileName = ko.observable(obj.fileName);
            this.width_inch = ko.observable(item.width_inch);
            this.height_inch = ko.observable(item.height_inch);
            this.dev_top_inch = ko.observable(item.dev_top_inch);
            this.dev_left_inch = ko.observable(item.dev_left_inch);
        },
        addImageHeader:function(){
            let self = this;
            if (this.imageDataArray().length === 0){
                self.imageIncrement = 1;
            }
            if (this.imageDataArray().length !== 0){
                self.imageIncrement = self.imageDataArray().length + 1;
            }
            let newInstance = new self.createNewImageFields(
                {
                    imageHeaderName:'Image Header '+self.imageIncrement,
                    fileData:null,
                    fileName:null
                },self.defaultData);
            self.imageDataArray.push(newInstance);
            accordion.renderAccordion($("#image-header"),(this.imageDataArray().length-1));
        },
        renderAccordion:function(){
            accordion.renderAccordion($("#image-header"));
        },
        uploadImage:function(event,$data){
            if (event.target.files!==undefined) {
                $data.fileData(URL.createObjectURL(event.target.files[0]));
                $data.fileName($(event.target).val().split('\\').pop());
            }
        },
        deleteImageHeader:function($data){
            this.imageDataArray.remove($data);
        },
    });
});
