define([
    'jquery',
    'underscore',
    'ko',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/model/header-types',
    'Ziffity_ProductCustomizer/js/accordion-utils',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'jquery/ui'
], function ($, _, ko, AbstractOption, stepNavigator, headerTypes,accordion, customizerData) {
    'use strict';
    //TODO: Have to load the google font to the page after getting the fonts from the controller
    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(true),
            sortOrder:2,
            template: 'Ziffity_ProductCustomizer/options/text-header',
            optionId: '2',
            overallHeaderSize:ko.observable(''),
            textHeaderArray:ko.observableArray([]),
            textHeaderInnerArray: ko.observable([]),
            backgroundColors:ko.observableArray([]),
            selectedBackgroundColor:ko.observable(null),
            headerFonts:[],
            fontColors:[],
            textIncrement:0,
            productSku:window.customizerConfig.productSku,
            headerDataArray:ko.observableArray([]),
            defaultData:{width_inch:'4 3/8',text_color:'white',text_align:'left',
                height_inch:'1 1/2',font_size_points:81,font_size_inch:'1 1/8',
                font:'Alegreya SC',dev_top_inch:'5 7/16',dev_left_inch:'0'},
            exports: {
                textHeaderArray: '${ $.provider }:options.header.text_header.textHeaderArray',
                optionId: '${ $.provider }:options.header.text_header.option_id',
                selectedBackgroundColor:'${ $.provider }:options.header.text_header.selectedBackgroundColor'
            },
            imports: {
                options: '${ $.provider }:options',
                headerDataArray:'${ $.provider }:options.header.headerDataArray',
                overallHeaderSize: '${ $.provider }:image.overallSize'
            },
            listens:{
                '${ $.provider }:editmode':'updateSelection',
                '${ $.provider }:image.overallSize':'updatedHeaderSize',
                '${ $.provider }:options.header.headerDataArray':'loadHeaderData'
            }
        },
        updatedHeaderSize:function(value){
            this.overallHeaderSize(value);
        },
        initialize: function() {
            this._super();
            let self = this;
            self.initTextHeader();
            self.loadHeaderData(self.headerDataArray());
            return this;
        },
        initSelection: function(value){
            this.loadHeaderData(this.headerDataArray());
        },
        updateSelection: function(value) {
            if(!value) {
                this.initSelection();
            }
        },
        convertViewArea:function(data){
            if (data!==undefined) {
                return data.width_inch + " Wide x " + data.height_inch + " High";
            }
            return "";
        },
        loadHeaderData:function(headerDataArray){
            let self = this;
            let textData = headerDataArray.texts;
            if (!_.isEmpty(headerDataArray)) {
                self.headerFonts = self.convertFonts(headerDataArray.fonts);
                self.fontColors = headerDataArray.text_colors;
                self.backgroundColors(headerDataArray.bg_colors);
                self.selectedBackgroundColor(headerDataArray.bg_color_active);
                if(customizerData.resolveHeaderText()){
                    let textHeader = customizerData.resolveHeaderText();
                    if(textHeader.textHeaderArray){
                        self.textHeaderArray.removeAll();
                       textData = textHeader.textHeaderArray;
                    }
                }
                _.each(textData, function (item, index) {
                    self.textIncrement++;
                    let innerObservableArray = ko.observableArray([]);
                    let newInstance = new self.createNewHeader(
                        {
                            headerSize: item.header_inch !== undefined ? item.header_inch: self.convertViewArea(item),
                            inputText: item.inputText !== undefined ? item.inputText: item.text,
                            headerFonts: self.headerFonts,
                            fontColors: self.fontColors,
                            selectedFont: item.selectedFont !== undefined? item.selectedFont: item.font,
                            text_align: item.text_align,
                            text_color: item.selectedColor !== undefined ? item.selectedColor: item.text_color,
                            bold: item.bold !== undefined ? item.bold : false,
                            italic: item.italic !== undefined ? item.italic :false,
                            underline: item.underline !== undefined ? item.underline :false,
                            fontColorIndex: item.fontColorIndex !== undefined? item.fontColorIndex: "font-color-" + self.textIncrement,
                            textAlignIndex: item.textAlignIndex !== undefined? item.textAlignIndex:"text-align-" + self.textIncrement,
                            font_size_inch: item.font_size_inch,
                            font_size_points: item.font_size_points,
                            width_inch:item.width_inch,
                            height_inch:item.height_inch,
                            dev_left_inch:item.dev_left_inch,
                            dev_top_inch:item.dev_top_inch,
                        });
                    innerObservableArray.push(newInstance);
                    self.textHeaderArray.push(newInstance);
                });
            }
        },
        initTextHeader: function() {
            let self = this;
            let code  = 'Text Header';
            headerTypes.registerHeader(code, self.isActive, self.sortOrder, self.selection, self.defaultSelection);
        },
        createNewHeader:function(obj){
            let self = this;
            self.sizeObservable = ko.observable(obj.headerSize);
            self.inputText = ko.observable(obj.inputText);
            self.headerFonts = ko.observableArray(obj.headerFonts);
            self.fontColors = ko.observableArray(obj.fontColors);
            self.selectedFont = ko.observable(obj.selectedFont);
            self.selectedColor = ko.observable(obj.text_color);
            self.bold = ko.observable(obj.bold);
            self.italic = ko.observable(obj.italic);
            self.underline = ko.observable(obj.underline);
            self.selectedAlignment = ko.observable(obj.text_align);
            self.fontColorIndex = ko.observable(obj.fontColorIndex);
            self.textAlignIndex = ko.observable(obj.textAlignIndex);
            self.font_size_inch = ko.observable(obj.font_size_inch);
            self.font_size_points = ko.observable(obj.font_size_points);
            self.width_inch = ko.observable(obj.width_inch);
            self.height_inch = ko.observable(obj.height_inch);
            self.dev_left_inch = ko.observable(obj.dev_left_inch);
            self.dev_top_inch = ko.observable(obj.dev_top_inch);
        },
        reduceSize:function(){
            let self = this;
            let points = self.font_size_points();
            points-=1;
            self.font_size_points(points);
        },
        increaseSize:function(self){
            let points = self.font_size_points();
            points+=1;
            self.font_size_points(points);
        },
        updateBold:function(self){
            if (self.bold()) {
                self.bold(false);
            }else{
                self.bold(true);
            }
        },
        updateUnderline:function(self){
            if (self.underline()) {
                self.underline(false);
            }else{
                self.underline(true);
            }
        },
        updateItalic:function(self){
            if (self.italic()) {
                self.italic(false);
            }else{
                self.italic(true);
            }
        },
        convertFonts:function(data){
            let result = [];
            let self = this;
            _.each(data,function(value){
                let obj = {};
                obj.name = value;
                obj.family = "font-family: '"+value+"';";
                self.loadFont(value);
                result.push(obj);
            });
            return result;
        },
        setOptionStyle:function(option, item) {
            ko.applyBindingsToNode(option, { attr: { style: item.family } }, item);
        },
        deleteTextHeader:function($data){
            var self = this;
            accordion.destroyAccordion($("#text-header"));
            this.textHeaderArray.remove($data);
            setTimeout(self.renderAccordion.bind(self,true));
        },
        addTextHeader:function(){
            let self = this;
            self.textIncrement++;
            let newInstance = new this.createNewHeader({
                headerSize:null,
                inputText:'',
                headerFonts:self.headerFonts,
                fontColors:self.fontColors,
                text_color:accordion.selectFontColor(self.fontColors),
                fontColorIndex:"font-color-"+self.textIncrement,
                textAlignIndex:"text-align-"+self.textIncrement,
                font_size_inch: self.defaultData.font_size_inch,
                font_size_points: self.defaultData.font_size_points,
                width_inch: self.defaultData.width_inch,
                height_inch: self.defaultData.height_inch,
                dev_left_inch: self.defaultData.dev_left_inch,
                dev_top_inch: self.defaultData.dev_top_inch,
            });
            this.textHeaderArray.push(newInstance);
            // accordion.renderAccordion($("#text-header"));
            setTimeout(self.renderAccordion.bind(self));
        },
        renderAccordion:function(removed=false){
            accordion.renderAccordion($("#text-header"),(this.textHeaderArray().length-1),removed);
        },
        loadFont:function (font_name) {
            require([
                'https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js'
            ], function (webfont) {
                webfont.load({
                    google: {
                        families: [font_name + ':400,400i,700,700i']
                    }
                });
            });
        },
        returnUniqueBackgroundColorId:function(param){
            return 'background-color-'+param+'-'+this.textIncrement;
        },
        returnUniqueColorId:function(param){
            return 'color-'+param+'-'+this.textIncrement;
        },
        returnUniqueName:function(param){
            return param+'-'+this.textIncrement;
        }
    });
});
