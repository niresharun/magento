define([
    'jquery',
    'underscore',
    'ko',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'mage/translate',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'Fraction',
    'Ziffity_ProductCustomizer/js/model/customizer-helper',
    'text!Ziffity_ProductCustomizer/template/optioninfo/size-info.html',
], function ($, _, ko, AbstractOption, stepNavigator,$t, performAjax, customizerDataResolver, Fraction, customizerHelper, info) {
    'use strict';

    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(true),
            isVisible:ko.observable(true),
            ajaxData:{},
            sizeType: window.customizerConfig.options.size.type, //TODO get type from attribute
            productSku:window.customizerConfig.productSku,
            sizeSentencePart1:ko.observable($t('The maximum size cannot exceed 47" x 96"')),
            sizeSentencePart2:ko.observable($t('If you select a dimension greater than 48", the other dimension must be 48" or less.')),
            widthInteger:ko.observableArray([]),
            widthFractional:ko.observableArray([]),
            heightFractional:ko.observableArray([]),
            heightInteger:ko.observableArray([]),
            selectedWidthInteger:ko.observable(),
            rotateFrame: ko.observable(false),
            selectedHeightInteger:ko.observable(),
            selectedWidthFractional:ko.observable(),
            selectedHeightFractional:ko.observable(),
            availableThickness: ko.observableArray(),
            sizeThickness:ko.observable(),
            interiorDepth: ko.observable(false),
            selection : ko.observable(),
            depthType: 'none',
            template: 'Ziffity_ProductCustomizer/options/size',
            subtotal: ko.observable(),
            sizeRestricted: ko.observable(false),
            singleWidthInteger: ko.observable(false),
            singleWidthFraction: ko.observable(false),
            singleHeightInteger: ko.observable(false),
            singleHeightFraction: ko.observable(false),
            singleThickness: ko.observable(false),
            matSizeLock: ko.observable(false),
            canRotate:ko.observable(false),
            supressValidation: false,
            openingDataArray:ko.observableArray([]),
            exports: {
                selectedWidthInteger: '${ $.provider }:options.size.width.integer',
                selectedWidthFractional: '${ $.provider }:options.size.width.tenth',
                selectedHeightInteger: '${ $.provider }:options.size.height.integer',
                selectedHeightFractional: '${ $.provider }:options.size.height.tenth',
                rotateFrame: '${ $.provider }:options.size.rotate',
                sizeType: '${ $.provider }:options.size.type',
                sizeThickness: '${ $.provider }:options.size.thickness',
                subtotal: '${ $.provider }:price.subtotal',
                sizeRestricted: '${ $.provider }:size_restricted'
            },
            imports: {
                interiorDepth: '${ $.provider }:options.size.interior_depth',
                sizeThickness: '${ $.provider }:options.size.thickness',
                sizeRestricted: '${ $.provider }:size_restricted',
                matSizeLock: '${ $.provider }:options.mat.sizes.sizes_lock',
                openingDataArray: '${ $.provider }:options.openings.openingDataArray',
            },
            listens: {
                '${ $.provider }:editmode': 'initSelection',
            }
        },
        initSelection: function() {
            var self = this;
            let sizeSelections = customizerDataResolver.resolveSizeSelection();
            self.recalculatePrice(self);
            self.sizeRestricted(false);
            if(sizeSelections){
                let widthInt = sizeSelections.rotate ? sizeSelections.height.integer : sizeSelections.width.integer;
                let widthFraction = sizeSelections.rotate ? sizeSelections.height.tenth : sizeSelections.width.tenth;
                let heightInt = sizeSelections.rotate ? sizeSelections.width.integer : sizeSelections.height.integer;
                let heightFraction = sizeSelections.rotate ? sizeSelections.width.tenth : sizeSelections.height.tenth;
                self.selectedWidthInteger(widthInt);
                self.selectedWidthFractional(widthFraction);
                self.selectedHeightInteger(heightInt);
                self.selectedHeightFractional(heightFraction);
                if(sizeSelections.thickness !== undefined) {
                    let thickness = new Fraction(sizeSelections.thickness).toFraction(true);
                    self.sizeThickness(thickness);
                }
                self.rotateFrame(sizeSelections.rotate);
            }
        },
        updateSelection: function(value) {
            if(!value) {
                this.initSelection();
            }
        },
        initialize: function () {
            this._super();
            let self = this;
            self.initSelection();
            self.depthType = window.customizerConfig.options.size.depth_type;
            stepNavigator.registerStep(
                'Size',
                'size',
                this.isActive,
                this.isVisible,
                0,
                0,
                true,
                false,
                info
            );
            self.loadAjaxData(self);
           self.initSizes(self);
            if(window.customizerConfig.options.size.interior_depth &&
                window.customizerConfig.options.size.available_thickness !== undefined){
                var thickness = new Fraction(window.customizerConfig.options.size.available_thickness[0]).toFraction(true);
                self.sizeThickness(thickness);
            }
            self.canRotate = ko.computed(function(){
                return ((self.openingDataArray().length <= 1) &&
                    !(self.singleHeightInteger() && self.singleHeightInteger() &&
                        self.singleWidthFraction() && self.singleHeightFraction()) &&
                    (self.selectedWidthInteger() !== self.selectedHeightInteger() ||
                            self.selectedWidthFractional() !== self.selectedHeightFractional()));
            })
            // self.sizeRestricted.subscribe(function (value){
            //     console.log(value);
            // });
            this.isActive.subscribe(function (value) {
                if (value) {
                    self.loadAjaxData(self);
                    self.sizeRestricted(false);
                    //self.calculateYourSelections()
                }
                if (!value) {
                    self.ajaxData = {};
                }
            });
            this.rotateFrame.subscribe(function (newValue) {
                $('body').trigger('processStart');
                let self = this;
                //self.loadAjaxData(this);
                let widthInteger1 = self.selectedWidthInteger();
                let widthFractional1 = self.selectedWidthFractional();
                let heightInteger1 = self.selectedHeightInteger();
                let heightFraction1 = self.selectedHeightFractional();


                setTimeout(function(){
                    self.supressValidation = true;
                    self.selectedWidthInteger(heightInteger1);
                    self.selectedWidthFractional(heightFraction1);
                    self.selectedHeightInteger(widthInteger1);
                    self.selectedHeightFractional(widthFractional1);
                    self.supressValidation = false;
                }, 0)

               // self.loadAjaxData(this);
                // let sizeSelections = customizerDataResolver.resolveSizeSelection();
               // self.recalculatePrice(self);



                // if(newValue){
                //     console.log('checked');
                //     if(sizeSelections){
                //         let widthInteger = self.selectedWidthInteger();
                //         let widthFractional = self.selectedWidthFractional();
                //         self.selectedWidthInteger(self.selectedHeightInteger());
                //         self.selectedWidthFractional(self.selectedHeightFractional());
                //         self.selectedHeightInteger(self.widthInteger);
                //         self.selectedHeightFractional(self.widthFractional);
                //     }
                // }else{
                //     if(sizeSelections){
                //         let widthInteger = self.selectedWidthInteger();
                //         let widthFractional = self.selectedWidthFractional();
                //         self.selectedWidthInteger(self.selectedHeightInteger());
                //         self.selectedWidthFractional(self.selectedHeightFractional());
                //         self.selectedHeightInteger(self.widthInteger);
                //         self.selectedHeightFractional(self.widthFractional);
                //     }
                //     console.log('Unchecked');
                // }
                $('body').trigger('processStop');
            }, self);
            self.selectedWidthInteger.subscribe(function (value) {
                self.getWidthFraction(value, self);
            });
            self.selectedWidthInteger.subscribeChanged(function (latestValue, previousValue) {
                if(self.validateSize() && (latestValue !== undefined && previousValue !== undefined) && !self.supressValidation){
                    self.selectedWidthInteger(previousValue);
                }
            });
            self.selectedWidthFractional.subscribeChanged(function (latestValue, previousValue) {
                if(self.validateSize() && (latestValue !== undefined && previousValue !== undefined) && !self.supressValidation){
                    self.selectedWidthFractional(previousValue);
                }
            });
            self.selectedHeightInteger.subscribeChanged(function (latestValue, previousValue) {
                if(self.validateSize() && (latestValue !== undefined && previousValue !== undefined) && !self.supressValidation){
                    self.selectedHeightInteger(previousValue);
                }
            });
            self.selectedHeightFractional.subscribeChanged(function (latestValue, previousValue) {
                if(self.validateSize() && (latestValue !== undefined && previousValue !== undefined) && !self.supressValidation){
                    self.selectedHeightFractional(previousValue);
                }
            });
            self.selectedHeightInteger.subscribe(function (value) {
                self.getHeightFraction(value, self)
            });
        },
        initSizes: function() {
            var self =this;
            if (window.customizerConfig.options.size) {
                var defaultSize = window.customizerConfig.options.size;
                self.selectedWidthInteger() !== undefined ? null: self.selectedWidthInteger(defaultSize.width.integer);
                self.selectedWidthFractional() !== undefined ? null:self.selectedWidthFractional(defaultSize.width.tenth);
                self.selectedHeightInteger() !== undefined ? null: self.selectedHeightInteger(defaultSize.height.integer);
                self.selectedHeightFractional() !== undefined ? null:self.selectedHeightFractional(defaultSize.height.tenth);
                self.getWidthFraction(self.selectedWidthInteger(), self);
                self.getHeightFraction(self.selectedHeightInteger(), self);
            }
        },
        validateSize: function(){
            let self = this;
            let width = customizerHelper.getFullNumber({'integer': self.selectedWidthInteger(), 'tenth':self.selectedWidthFractional()});
            let height = customizerHelper.getFullNumber({'integer': self.selectedHeightInteger(), 'tenth': self.selectedHeightFractional()});
            return width > 48 && height > 48;
        },
        loadAjaxData:function (self) {
            let baseUrl = window.location.protocol + "//" + window.location.host;
            $.ajax({
                url:baseUrl+'/customizer/Option/GetSizeOptions',
                type:'POST',
                async:false,
                global:true,
                showLoader:false,
                data:{product_sku:this.productSku},
                dataType:'json'
            }).done(function (data) {
                if (data!==undefined && data.size_option !== undefined) {
                    self.ajaxData.size_option = data.size_option;
                }
                if (window.customizerConfig.options.size) {
                    var defaultSize = window.customizerConfig.options.size;
                    self.selectedWidthInteger() !== undefined ? null: self.selectedWidthInteger(defaultSize.width.integer);
                    self.selectedWidthFractional() !== undefined ? null:self.selectedWidthFractional(defaultSize.width.tenth);
                    self.selectedHeightInteger() !== undefined ? null: self.selectedHeightInteger(defaultSize.height.integer);
                    self.selectedHeightFractional() !== undefined ? null:self.selectedHeightFractional(defaultSize.height.tenth);
                }
            });
        },
        changeLabelToInches:function (string) {
            return string+'"';
        },
        getWidthIntegers:function () {
            this.loadIntegerWidth(this);
            this.widthInteger().length === 1 ? this.singleWidthInteger(true) :this.singleWidthInteger(false);
            return this.widthInteger;
        },
        getWidthFractional:function () {
            this.loadWidthFractional(this);
            this.widthFractional().length === 1 ? this.singleWidthFraction(true) :this.singleWidthFraction(false);
            return this.widthFractional;
        },
        getHeightInteger:function () {
            this.loadIntegerHeight(this);
            this.heightInteger().length === 1 ? this.singleHeightInteger(true) :this.singleHeightInteger(false);
            return this.heightInteger;
        },
        getHeightFractional:function () {
            this.loadHeightFractional(this);
            this.heightFractional().length === 1 ? this.singleHeightFraction(true) :this.singleHeightFraction(false);
            return this.heightFractional;
        },
        loadIntegerWidth: function (self) {
            if (_.isEmpty(self.ajaxData)) {
                self.loadAjaxData(self);
            }
            if(self.rotateFrame()) {
                let height = self.ajaxData.size_option;
                if (!_.isEmpty(height) &&  height!==undefined) {
                    self.widthInteger.removeAll();
                    height.fractionalData.height.forEach(function (element,index) {
                        self.widthInteger.push({label:self.changeLabelToInches(element.integer),value:element.integer});
                    });
                }
            }
            else {
                let width = self.ajaxData.size_option;
                if (!_.isEmpty(width) && width !== undefined) {
                    self.widthInteger.removeAll();
                    width.fractionalData.width.forEach(function (element, index) {
                        self.widthInteger.push({
                            label: self.changeLabelToInches(element.integer),
                            value: element.integer
                        });
                    });
                }
            }
            return;
        },
        loadIntegerHeight:function (self) {
            if (_.isEmpty(self.ajaxData)) {
                self.loadAjaxData(self);
            }
            if(self.rotateFrame()){
                let width = self.ajaxData.size_option;
                if (!_.isEmpty(width) && width !== undefined) {
                    self.heightInteger.removeAll();
                    width.fractionalData.width.forEach(function (element, index) {
                        self.heightInteger.push({
                            label: self.changeLabelToInches(element.integer),
                            value: element.integer
                        });
                    });
                }
            } else {
                let height = self.ajaxData.size_option;
                if (!_.isEmpty(height) && height !== undefined) {
                    self.heightInteger.removeAll();
                    height.fractionalData.height.forEach(function (element, index) {
                        self.heightInteger.push({
                            label: self.changeLabelToInches(element.integer),
                            value: element.integer
                        });
                    });
                }
            }
            return;
        },
        // loadAvailableHeight: function (self) {
        //     if (_.isEmpty(self.ajaxData)) {
        //         self.loadAjaxData(self);
        //     }
        //     let availableHeight = self.ajaxData.size_option;
        //     if (!_.isEmpty(availableHeight) && availableHeight!==undefined) {
        //         self.heightInteger.removeAll();
        //         availableHeight.available_height.forEach(function (element,index) {
        //             self.heightInteger.push(element);
        //         });
        //     }
        //     return;
        // },
        loadWidthFractional:function (self) {
            if (_.isEmpty(self.ajaxData)) {
                self.loadAjaxData(self);
                self.getWidthFraction(self.selectedWidthInteger(), self);
            }
        },
        getWidthFraction: function(value, self) {
            self.loadWidthFractional(self);
            let width = self.ajaxData.size_option;
                if (value!==undefined) {
                    if(self.rotateFrame()){
                        self.widthFractional.removeAll();
                        if (!_.isEmpty(width) && width!==undefined) {
                            width.fractionalData.height.forEach(function (element, index) {
                                if (value === element.integer) {
                                    element.tenth.forEach(function (ele, i) {
                                        self.widthFractional.push({label: self.changeLabelToInches(ele), value: ele});
                                    });
                                }
                            });
                        }

                    } else{
                        self.widthFractional.removeAll();
                        if (!_.isEmpty(width) && width!==undefined) {
                            width.fractionalData.width.forEach(function (element, index) {
                                if (value == element.integer) {
                                    element.tenth.forEach(function (ele, i) {
                                        self.widthFractional.push({label: self.changeLabelToInches(ele), value: ele});
                                    });
                                }
                            });
                        }
                    }
                }
                if (value == undefined) {
                    self.widthFractional.removeAll();
                }
        },
        loadHeightFractional:function (self) {
            if (_.isEmpty(self.ajaxData)) {
                self.loadAjaxData(self);
                self.getWidthFraction(self.selectedHeightInteger(), self);
            }
        },
        getHeightFraction: function(value, self) {
            self.loadHeightFractional(self);
            let height = self.ajaxData.size_option;
            if (value!==undefined) {
                if(self.rotateFrame()) {
                    self.heightFractional.removeAll();
                    if (!_.isEmpty(height) && height!==undefined) {
                        height.fractionalData.width.forEach(function (element, index) {
                            if (value == element.integer) {
                                element.tenth.forEach(function (ele, i) {
                                    self.heightFractional.push({label: self.changeLabelToInches(ele), value: ele});
                                });
                            }
                        });
                    }
                } else {
                    self.heightFractional.removeAll();
                    if (!_.isEmpty(height) && height!==undefined) {
                        height.fractionalData.height.forEach(function (element, index) {
                            if (value == element.integer) {
                                element.tenth.forEach(function (ele, i) {
                                    self.heightFractional.push({label: self.changeLabelToInches(ele), value: ele});
                                });
                            }
                        });
                    }
                }
            }
            if (value == undefined) {
                self.heightFractional.removeAll();
            }
        },
        getAvailableThickness: function(){
            var self = this;
            self.availableThickness.removeAll();
            var thickness = window.customizerConfig.options.size.available_thickness;
            if(thickness) {
                thickness.forEach(function (element, index) {
                    self.availableThickness.push({
                        label: self.changeLabelToInches(new Fraction(element).toFraction(true)),
                        value: (new Fraction(element).toFraction(true))
                    })
                })
            }
            (self.availableThickness().length === 1) ? self.singleThickness(true) :self.singleThickness(false);
            return self.availableThickness;
        },

        recalculatePrice:function (self){
            var self = this;
            let data = {};
            let result;
            data.options = self.options; //self.subPriceSumUp(self.pricing, self);
            data.sku = self.productSku;
            result = performAjax.performAjaxOperation('customizer/option/getSubtotal/','POST',data);
            $('body').trigger('processStop');
            result.done(function(response){
                if(response!== undefined){
                    self.subtotal(response.subtotal);
                    self.pricingSummary(response.price_summary);
                }
            }, self);
        },

    });
});
