define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/form/form',
    'underscore',
    'openingVue',
    'headerVue',
    'labelVue',
    'mage/translate',
    'uiRegistry',
    'Magento_Ui/js/lib/view/utils/dom-observer'
], function ($, uiAlert, uiConfirm, Form, _,openingVue,headerVue,labelVue, $t,registry,domObserver) {
    'use strict';
    return Form.extend({
        defaults: {
            additionalTabs:'',
            addTabs: [],
            imports:{
                additionalTabs: '${ $.provider }:data.product.additional_tabs'
            }
        },
        /**
         * execution starts
         */
        initialize: function () {
            this._super();
            let self = this;
            let sizeElements = [
                "select[name='product[size_type]']",
                "select[name='product[dimension_1]']",
                "select[name='product[dimension_2]']",
                "select[name='headers[size][height]']",
                "select[name='labels[size][height]']",
                "select[name='product[matboard_overlap]']",
                "select[name='product[dimension_1_default]']",
                "select[name='product[dimension_2_default]']",
            ];
            let additionalTabs = this.getAdditionalTabValue();
            if (additionalTabs){
                _.each(additionalTabs,function (item){
                    if (item !== undefined) {
                        self.switchCaseToggleVisibility(item, sizeElements);
                    }
                });
            }
            return this;
        },
        /**
         * init observers
         */
        initObservable: function () {
            this._super().observe(
                'additionalTabs'
            );
            return this;
        },
        save:function(){
            let self = this;
            this.setEmptyValues();
            if (!this.checkAdditionalTabs(self)){
                return false;
            }
            if (!this.checkGroupsPresent(self)){
                return false;
            }
            return this._super();
        },
        checkAdditionalTabs:function(self){
            let result = true;
            let alreadyPresent = [];
            let additionalTabs = this.getAdditionalTabValue();
            _.each(additionalTabs,function(title){
                if (title === "Headers" || title === "Labels") {
                    if (alreadyPresent.indexOf('Labels')!==-1){
                        alert('Product can have header or label only');
                        result = false;
                    }
                    if (alreadyPresent.indexOf('Headers')!==-1){
                        alert('Product can have header or label only');
                        result = false;
                    }
                    alreadyPresent.push(title);
                }
            });
            return result;
        },
        switchCaseToggleVisibility:function(value,sizeElements){
            let openingGroup = registry.get('index=opening_group');
            let openingSet = false;
            switch (value) {
                case "Openings":
                    if (openingGroup!==undefined){
                        openingGroup.visible(true);
                        this.initializeOpeningVue(sizeElements);
                        openingSet = true;
                    }
                    break;
                case "Headers":
                    let headerGroup = registry.get('index=header_group');
                    if (!openingSet) {
                        this.showOpening(openingGroup, sizeElements);
                        openingSet = true;
                    }
                    if (headerGroup!==undefined){
                        headerGroup.visible(true);
                        this.initializeHeaderVue(sizeElements);
                    }
                    break;
                case "Labels":
                    let labelGroup = registry.get('index=label_group');
                    if (!openingSet) {
                        this.showOpening(openingGroup, sizeElements);
                        openingSet = true;
                    }
                    if (labelGroup!==undefined){
                        labelGroup.visible(true);
                        this.initializeLabelVue(sizeElements);
                    }
                    break;
            }
        },
        showOpening:function(openingGroup,sizeElements){
            if (openingGroup!==undefined){
                openingGroup.visible(true);
                this.initializeOpeningVue(sizeElements);
            }
        },
        initializeOpeningVue:function(sizeElements){
            $(document).ready(function(){
                domObserver.get(sizeElements[0],function(element){
                    if (window.openingWidget === undefined && window.openingWidget !== true) {
                        if (element.length) {
                            openingVue({}, element);
                            window.openingWidget = true;
                        }
                    }
                })
            });
        },
        initializeHeaderVue:function(sizeElements){
            $(document).ready(function(){
                domObserver.get(sizeElements[7],function(element){
                    if (window.headerWidget == undefined && window.headerWidget !== true) {
                        if (element.length) {
                            headerVue({}, element);
                            window.headerWidget = true;
                        }
                    }
                })
            });
        },
        initializeLabelVue:function(sizeElements){
            $(document).ready(function(){
                domObserver.get(sizeElements[0],function(element){
                    if (window.labelWidget == undefined && window.labelWidget !== true) {
                        if (element.length) {
                            labelVue({}, element);
                            window.labelWidget = true;
                        }
                    }
                })
            });
        },
        setEmptyValues:function(){
            let arr = ['opening_data','opening_size','header_data','label_data'];
            arr.each(function(element,index){
                let group = registry.get('index='+element);
                if (group!==undefined){
                    group.value(JSON.stringify([]));
                }
            });
        },
        checkGroupsPresent:function(self){
            let openingSet = false;
            let result = true;
            let additionalTabs = this.getAdditionalTabValue();
            if (additionalTabs!==undefined){
                _.each(additionalTabs, function(element,index){
                    if (element === "Openings"){
                        if (!self.saveOpeningData()){
                            result = false;
                            openingSet = true;
                        }
                    }
                    if (element === "Headers"){
                        if(!openingSet){
                            if (!self.saveOpeningData()){
                                result = false;
                            }
                        }
                        if (!self.saveHeaderData()){
                            result = false;
                        }
                    }
                    if (element === "Labels"){
                        if(!openingSet){
                            if (!self.saveOpeningData()){
                                result = false;
                            }
                        }
                        if (!self.saveLabelData()){
                            result = false;
                        }
                    }
                });
            }
            return result;
        },
        saveOpeningData:function(){
            let OPENINGS_ADMIN = window.OpeningObject;
            if (typeof OPENINGS_ADMIN !== 'undefined' && OPENINGS_ADMIN !== null) {
                let error = OPENINGS_ADMIN.error;
                if (error) {
                    uiAlert({
                        content: $t('Please check Openings Section.')
                    });
                    return false;
                }
                let openings = OPENINGS_ADMIN.exportList();
                if (typeof openings !== 'undefined' && openings !== null) {
                    registry.get('index=opening_data').value(JSON.stringify(openings));
                    if (typeof window.productJson !== 'undefined' && window.productJson !== null) {
                        registry.get('index=opening_size').value(JSON.stringify(window.productJson));
                    }
                }
            }
            return true;
        },
        saveHeaderData:function(){
            if (typeof window.headerObjectData !== 'undefined' && window.headerObjectData !== null) {
                let error = window.headerObjectData.error;
                if (error) {
                    uiAlert({
                        content: $t('Please check Labels/Headers Section.')
                    });
                    return false;
                }
                window.headerObjectData.exportData();
                let headersData = {
                    images: '',
                    texts: '',
                    customHeaders:''
                };
                try {
                    headersData.images = window.productJson.modules.crheader.images;
                } catch (e) {
                    headersData.images = {};
                }
                try {
                    headersData.texts = window.productJson.modules.crheader.texts;
                } catch (e) {
                    headersData.texts = {};
                }
                try {
                    headersData.bg_color_active = window.productJson.modules.crheader.bg_color_active;
                } catch (e) {

                }
                let headerDataInput = registry.get('index=header_data');
                if (headerDataInput!==undefined) {
                    headersData.customHeaders = this.buildCustomHeaders();
                    headerDataInput.value(JSON.stringify(headersData));
                }
            }
            return true;
        },
        saveLabelData:function(){
            if (typeof window.labelObjectData !== 'undefined' && window.labelObjectData !== null) {
                let error = window.labelObjectData.error;
                if (error) {
                    uiAlert({
                        content: $t('Please check Labels/Headers Section.')
                    });
                    return false;
                }
                window.labelObjectData.exportData();
                let labelsData = {
                    images: '',
                    texts: '',
                    customHeaders:''
                };
                try {
                    labelsData.images = window.productJson.modules.labels.images;
                } catch (e) {
                    labelsData.images = {};
                }
                try {
                    labelsData.texts = window.productJson.modules.labels.texts;
                } catch (e) {
                    labelsData.texts = {};
                }
                let labelsDataInput = registry.get('index=label_data');
                if (labelsDataInput!==undefined) {
                    labelsData.customHeaders = this.buildCustomLabelHeaders();
                    labelsDataInput.value(JSON.stringify(labelsData));
                }
            }
            return true;
        },
        buildCustomHeaders:function(){
            let data = {};
            data.position = registry.get('index=header_position').value();
            data.size = {};
            data.size.height = registry.get('index=header_height').value();
            data.size.width = registry.get('index=header_width').value();
            data.font_size = {};
            data.font_size.min = registry.get('index=font_size_min').value();
            data.font_size.step = registry.get('index=font_size_step').value();
            data.font_size.default = registry.get('index=font_size_default').value();
            return [data];
        },
        buildCustomLabelHeaders:function(){
            let data = {};
            data.position = registry.get('index=label_position').value();
            data.size = {};
            data.size.height = registry.get('index=label_height').value();
            data.size.width = registry.get('index=label_width').value();
            data.font_size = {};
            data.font_size.min = registry.get('index=label_font_size_min').value();
            data.font_size.step = registry.get('index=label_font_size_step').value();
            data.font_size.default = registry.get('index=label_font_size_default').value();
            return [data];
        },
        getAdditionalTabValue:function(){
            let tabs = [];
            let self = this;
            this.addTabs = registry.get('index=additional_tabs');
            if(this.addTabs) {
                let value = this.addTabs.initialValue;
                this.addTabs = this.addTabs.indexedOptions;
                value.forEach(function (value, index) {
                    if (value) {
                        tabs.push(this.addTabs[value].label);
                    }
                }, this);
            }
            return tabs;
        }
    });
});

