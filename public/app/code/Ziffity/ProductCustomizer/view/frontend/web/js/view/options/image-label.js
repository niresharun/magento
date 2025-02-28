define([
    'jquery',
    'underscore',
    'ko',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/model/label-types',
    'Ziffity_ProductCustomizer/js/accordion-utils',
    'jquery/ui'
], function ($, _, ko, AbstractOption, stepNavigator, labelTypes,accordion) {
    'use strict';
    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(false),
            sortOrder:1,
            template: 'Ziffity_ProductCustomizer/options/image-label',
            optionId: '1',
            imageIncrement: 1,
            overallLabelSize:ko.observable(''),
            imageDataArray:ko.observableArray([]),
            labelDataArray:ko.observableArray([]),
            defaultData:{width_inch:'4 3/8', height_inch:'1 1/2',
                dev_top_inch:'5 7/16',dev_left_inch:'0'},
            exports: {
                optionId: '${ $.provider }:options.label.image-label.option_id',
                imageDataArray: '${ $.provider }:options.label.image-label.imageDataArray'
            },
            imports: {
                options: '${ $.provider }:options',
                labelDataArray:'${ $.provider }:options.label.labelDataArray'
            }
        },
        initialize: function() {
            this._super();
            let self = this;
            self.initImageLabel();
            self.loadImageData(self);
            return this;
        },
        loadImageData:function(self){
            if (!_.isEmpty(self.labelDataArray())){
                _.each(self.labelDataArray().images,function(item){
                    let newInstance = new self.createNewImageFields(
                        {
                            imageLabelName:'Image Label '+self.imageIncrement++,
                            fileData:item.url,
                            fileName:item.url
                        },item);
                    self.imageDataArray.push(newInstance);
                });
            }
        },
        initImageLabel: function() {
            let self = this;
            let code  = 'Image Label';
            labelTypes.registerLabel(code, self.isActive, self.sortOrder, self.selection, self.defaultSelection);
        },
        createNewImageFields:function(obj,item){
            this.imageLabelName = ko.observable(obj.imageLabelName);
            this.fileData = ko.observable(obj.fileData);
            this.fileName = ko.observable(obj.fileName);
            this.width_inch = ko.observable(item.width_inch);
            this.height_inch = ko.observable(item.height_inch);
            this.dev_top_inch = ko.observable(item.dev_top_inch);
            this.dev_left_inch = ko.observable(item.dev_left_inch);
        },
        uploadImage:function(event,$data){
            if (event.target.files!==undefined) {
                $data.fileData(URL.createObjectURL(event.target.files[0]));
                $data.fileName($(event.target).val().split('\\').pop());
            }
        },
        deleteImageLabel:function($data){
            this.imageDataArray.remove($data);
        },
        renderAccordion:function(){
            accordion.renderAccordion($("#image-label"));
        },
        addImageLabel:function(){
            let self = this;
            if (this.imageDataArray().length === 0){
                self.imageIncrement = 1;
            }
            if (this.imageDataArray().length !== 0){
                self.imageIncrement = self.imageDataArray().length + 1;
            }
            let newInstance = new this.createNewImageFields(
                {
                    imageLabelName:'Image Label '+self.imageIncrement,
                    fileData:null,
                    fileName:null
                },self.defaultData);
            self.imageDataArray.push(newInstance);
            accordion.renderAccordion($("#image-label"),(this.imageDataArray().length-1));
        },
    });
});
