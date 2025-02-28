define([
    'jquery',
    'underscore',
    'ko',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/model/label-types',
    'Ziffity_ProductCustomizer/js/accordion-utils',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'jquery/ui'
], function ($, _, ko, AbstractOption, stepNavigator, labelTypes,accordion, customizerData) {
    'use strict';
    //TODO: Have to load the google font to the page after getting the fonts from the controller
    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(true),
            sortOrder:2,
            template: 'Ziffity_ProductCustomizer/options/text-label',
            optionId: '2',
            textLabelArray:ko.observableArray([]),
            labelDataArray:ko.observableArray([]),
            labelFonts:[],
            labelFontsArray:ko.observableArray([]),
            fontColorsArray:ko.observableArray([]),
            fontColors:[],
            textIncrement:0,
            productSku:window.customizerConfig.productSku,
            objectSize:ko.observable(null),
            defaultData:{width_inch:'4 3/8',text_color:'white',text_align:'left',
                height_inch:'1 1/2',font_size_points:81,font_size_inch:'1 1/8',
                font:'Alegreya SC',dev_top_inch:'5 7/16',dev_left_inch:'0'},
            exports: {
                optionId: '${ $.provider }:options.label.text_label.option_id',
                textLabelArray: '${ $.provider }:options.label.text_label.textLabelArray'
            },
            imports: {
                options: '${ $.provider }:options',
                labelDataArray:'${ $.provider }:options.label.labelDataArray',
                labelFontsArray:'${ $.provider }:options.label.labelFonts',
                fontColorsArray:'${ $.provider }:options.label.fontColors',
                objectSize:'${ $.provider }:image.objectSize'
            },
            listens:{
                '${ $.provider }:editmode':'updateSelection',
                '${ $.provider }:image.objectSize':'updatedImageSize'
            }
        },
        initialize: function() {
            this._super();
            let self = this;
            self.initTextLabel();
            self.loadLabelData(self);
            return this;
        },
        initSelection: function(value){
            this.loadLabelData(this);
        },
        updatedImageSize:function(value){
           console.log(value);
        },
        convertViewArea:function(data){
            if (data!==undefined) {
                return data.width_inch + " Wide x " + data.height_inch + " High";
            }
            return "";
        },
        loadLabelData:function(self){
            let textData = self.labelDataArray().texts;

                if (!_.isEmpty(self.labelDataArray())){
                    if(customizerData.resolveLabelText()){
                        let textLabel = customizerData.resolveLabelText();
                        if(textLabel.textLabelArray){
                            self.textLabelArray.removeAll();
                            textData = textLabel.textLabelArray;
                        }
                    }
                    _.each(textData,function(item){
                        self.textIncrement++;
                        let newInstance = new self.createNewLabel(
                            {
                                labelSize:self.convertViewArea(item),
                                inputText:item.inputText !== undefined ? item.inputText: item.text,
                                labelFonts:item.labelFonts !== undefined? item.labelFonts:self.labelFontsArray(),
                                fontColors:item.fontColors !== undefined? item.fontColors: self.fontColorsArray(),
                                selectedFont:item.selectedFont !== undefined ? item.selectedFont: sitem.font,
                                text_align:item.selectedAlignment !== undefined? item.selectedAlignment: item.text_align,
                                text_color:item.selectedColor !== undefined? item.selectedColor : item.text_color,
                                bold:item.bold !== undefined ? item.bold:item.font_style.bold,
                                italic:item.italic !== undefined ? item.italic:item.font_style.italic,
                                underline:item.underline !== undefined ? item.underline :item.font_style.underline,
                                fontColorIndex:item.fontColorIndex !== undefined? item.fontColorIndex: "font-color-"+self.textIncrement,
                                textAlignIndex:item.textAlignIndex !== undefined? item.textAlignIndex: "text-align-"+self.textIncrement,
                                textLabelName:item.textLabelName !== undefined? item.textLabelName:'Text Label '+self.textIncrement,
                                font_size_inch: item.font_size_inch,
                                font_size_points: item.font_size_points,
                                width_inch:item.width_inch,
                                height_inch:item.height_inch,
                                dev_left_inch:item.dev_left_inch,
                                dev_top_inch:item.dev_top_inch,
                            });
                        self.textLabelArray.push(newInstance);
                    });
                }
        },
        initTextLabel: function() {
            let self = this;
            let code  = 'Text Label';
            labelTypes.registerLabel(code, self.isActive, self.sortOrder, self.selection, self.defaultSelection);
        },
        createNewLabel:function(obj){
            let self = this;
            self.textLabelName = ko.observable(obj.textLabelName);
            self.sizeObservable = ko.observable(obj.labelSize);
            self.inputText = ko.observable(obj.inputText);
            self.labelFonts = ko.observableArray(obj.labelFonts);
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
            self.headerLabelType = 'label';
        },
        reduceSize:function(self){
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
        setOptionStyle:function(option, item) {
            ko.applyBindingsToNode(option, { attr: { style: item.family } }, item);
        },
        deleteTextLabel:function($data){
            this.textLabelArray.remove($data);
        },
        addTextLabel:function(){
            let self = this;
            if (this.textLabelArray().length === 0){
                self.textIncrement = 1;
            }
            if (this.textLabelArray().length !== 0){
                self.textIncrement = self.textLabelArray().length + 1;
            }
            let newInstance = new this.createNewLabel(
                {
                    labelSize:null,
                    inputText:null,
                    labelFonts:self.labelFontsArray(),
                    fontColors:self.fontColorsArray(),
                    selectedFont:null,
                    text_align:null,
                    text_color:accordion.selectFontColor(self.fontColorsArray()),
                    bold:false,
                    italic:false,
                    underline:false,
                    fontColorIndex:"font-color-"+self.textIncrement,
                    textAlignIndex:"text-align-"+self.textIncrement,
                    textLabelName:'Text Label '+self.textIncrement,
                    font_size_inch: self.defaultData.font_size_inch,
                    font_size_points: self.defaultData.font_size_points,
                    width_inch:self.defaultData.width_inch,
                    height_inch:self.defaultData.height_inch,
                    dev_left_inch:self.defaultData.dev_left_inch,
                    dev_top_inch:self.defaultData.dev_top_inch,
                });
            this.textLabelArray.push(newInstance);
            accordion.renderAccordion($("#text-label"),(this.textLabelArray().length-1));
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
        renderAccordion:function(){
            accordion.renderAccordion($("#text-label"));
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
