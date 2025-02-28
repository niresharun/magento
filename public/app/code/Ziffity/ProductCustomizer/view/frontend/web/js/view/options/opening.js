define([
    'jquery',
    'underscore',
    'ko',
    'uiRegistry',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'text!Ziffity_ProductCustomizer/template/optioninfo/opening-info.html',
], function ($, _, ko, registry, AbstractOption, stepNavigator,performAjax, customizerData, info) {
    'use strict';
    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(false),
            isVisible:ko.observable(false),
            template: 'Ziffity_ProductCustomizer/options/opening',
            position: 75,
            shapeSelection: ko.observable('circle'),
            graphicSize:ko.observable('Have to render the graphic size here'),
            viewableWidth:ko.observable(0),
            viewableHeight:ko.observable(0),
            graphicWidth:ko.observable(0),
            graphicHeight:ko.observable(0),
            openingDataArray:ko.observableArray([]),
            hasOpening: ko.observable(false),
            graphics:ko.observableArray([]),
            //TODO: File data will be set once the graphic upload field is implemented
            fileData:ko.observable(''),
            openingActivated:1,
            exports: {
                fileData: '${ $.provider }:options.opening.fileData',
                shapeSelection: '${ $.provider }:options.opening.shapeSelection',
                openingDataArray:'${ $.provider }:options.openings.openingDataArray',
                openingActivated:'${ $.provider }:options.openings.openingActivated',
                graphics: '${ $.provider }:img_upload',
            },
            imports: {
                viewableWidth:'${ $.provider }:options.mat.viewableWidth',
                viewableHeight:'${ $.provider }:options.mat.viewableHeight',
                graphicWidth:'${ $.provider }:options.mat.graphicWidth',
                graphicHeight:'${ $.provider }:options.mat.graphicHeight',
                options: '${ $.provider }:options',
            },
            listens:{
                '${ $.provider }:editmode':'updateSelection'
            }
        },
        initialize: function() {
            this._super();
            let self =this;
            stepNavigator.registerStep(
                'Opening',
                'opening',
                this.isActive,
                this.isVisible,
                self.position,
                self.sortOrder,
                false,
                true,
                info
            );
            self.loadOpeningData(self);
            self.updateGraphics();
            this.isActive.subscribe(function(value){
                if (value && !self.openingDataArray().length){
                    self.loadOpeningData(self);
                }
            })
        },
        initSelection: function(){
            let self =this;
            self.loadOpeningData(self);
        },
        updateSelection: function(value) {
            if(!value) {
                this.initSelection();
            }
        },

        updateGraphics: function(){
            let self = this;
            let openingData = self.openingDataArray();
            if (self.options.hasOwnProperty('backing_board')) {
                if (typeof openingData != "undefined") {
                    self.hasOpening(true);
                    Object.keys(openingData).forEach(key => {
                        let uploadData =  {
                            name: ko.observable(''),
                            url: ko.observable(openingData[key].upload().url)
                        };
                        self.graphics.push(uploadData);
                    }, self);
                } else {
                    let uploadData = {
                        'url': ko.observable(''),
                        'name' : ko.observable('')
                    }
                    self.graphics.push(uploadData);
                }
            }
        },
        createNewOpening:function(obj,context){
            let self = this;
            self.shapeSelection = ko.observable(obj.shapeSelection);
            self.openingName = ko.observable(obj.openingName);
            self.viewableArea = ko.observable(obj.viewableArea);
            self.img = obj.item.img === undefined ? ko.observable({'url': ''}): ko.observable(obj.item.img);
            self.upload = ko.observable({'url': ''});
            self.name = ko.observable(obj.item.name);
            self.position = ko.observable(obj.item.position);
            self.position_dev = ko.observable(obj.item.position_dev);
            self.size = ko.observable(obj.item.size);
            obj.shape = obj.item.shape === 'rectangle' ? 'rect' : 'circle';
            self.shape = ko.observable(obj.shape);
            //TODO: Have to provide the graphic size here soon.
            self.graphicSize = ko.observable("");
        },
        convertViewArea:function(data){
            if (data!==undefined) {
                return data.width_inch + " Wide x " + data.height_inch + " High";
            }
            return "";
        },
        loadOpeningData:function(self){
            let openingSource = customizerData.resolveOpenings();
            self.openingDataArray.removeAll();
            if(openingSource){
                _.each(openingSource.openingDataArray,function(item) {
                    let newInstance = new self.createNewOpening(
                        {
                            shapeSelection: item.shapeSelection,
                            openingName: item.name,
                            viewableArea: self.convertViewArea(item.size),
                            graphicSize: item.size,
                            item: item
                        });
                    self.openingDataArray.push(newInstance);
                });
            } else{
                let response =  performAjax.performNonAsyncAjaxOperation('customizer/option/getOpeningData',
                    'POST',window.customizerConfig.productSku);
                response.done(function(response){
                    if (response.success){
                        _.each(response.opening_data.list,function(item){
                            let newInstance = new self.createNewOpening(
                                {
                                    shapeSelection:item.shape,
                                    openingName:item.name,
                                    viewableArea:self.convertViewArea(item.size),
                                    graphicSize:item.size,
                                    item:item
                                });
                            self.openingDataArray.push(newInstance);
                        }, self);
                    }
                });
            }
            $('body').trigger('processStop');
        },
    });
});
