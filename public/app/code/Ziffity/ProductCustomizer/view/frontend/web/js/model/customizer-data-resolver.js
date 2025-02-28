
/**
 * Customizer adapter for loading options
 *
 * @api
 */
 define([
    'jquery',
    'ko',
], function ($, ko) {
    'use strict';

    return {
        // options: ko.observableArray(window.customizerConfig.options),
        productName: window.customizerConfig.productName,
        productSku: window.customizerConfig.productSku,
        srcType: window.customizerConfig.src_type,
        quoteScope: window.customizerConfig.quote_scope,
        customizerData: null,
        getOptions :  function() {
            console.log('getting the options');
            return this.options();
        },

        optionItemsAjaxUrl : function () {
            return window.customizerConfig.optionItemsAjaxUrl;
        },
        prepareOptions: function() {
            // window.customizerConfig.options.map(option => {
            //     return {
            //         selection:ko.observable(option.defaultSelection),
            //     }
            // })
        },

        getCustomizerData: function() {
            if (!this.customizerData) {
                let data = localStorage.getItem('customizer_data');
                if (data) {
                    data = JSON.parse(data);
                    //this.srcType = 'your_selection';
                    switch(this.srcType){
                        case 'checkout':
                            if(window.customizerConfig.quote !== undefined)
                            {
                                this.customizerData = (data.hasOwnProperty('checkout') && data['checkout'].hasOwnProperty(window.customizerConfig.quote.item_id) &&
                                data['checkout'][window.customizerConfig.quote.item_id].hasOwnProperty('options')) ?
                                    data['checkout'][window.customizerConfig.quote.item_id]['options']: null;
                            }
                            break;
                        case 'default':
                            this.customizerData = (data.hasOwnProperty('product') && data['product'].hasOwnProperty(this.productSku) &&
                            data['product'][this.productSku].hasOwnProperty('options')) ? data['product'][this.productSku]['options'] : null;
                            break;
                        case 'request_quote':
                            if(window.customizerConfig.quote !== undefined)
                            {
                                this.customizerData = (data.hasOwnProperty('request_quote') && data['request_quote'].hasOwnProperty(window.customizerConfig.quote.item_id) &&
                                    data['request_quote'][window.customizerConfig.quote.item_id].hasOwnProperty('options')) ?
                                    data['request_quote'][window.customizerConfig.quote.item_id]['options']: null;
                            }
                            break;
                        case 'saved_designs':
                            if(window.customizerConfig.saved_designs !== undefined)
                            {
                                this.customizerData = (data.hasOwnProperty('saved_designs') && data['saved_designs'].hasOwnProperty(window.customizerConfig.saved_designs.id) &&
                                    data['saved_designs'][window.customizerConfig.saved_designs.id].hasOwnProperty('options')) ?
                                    data['saved_designs'][window.customizerConfig.saved_designs.id]['options']: null;
                            }
                            break;
                    }
                }
                 this.customizerData  =  this.customizerData ?? window.customizerConfig.options;
            }
            return this.customizerData;
        },

        resetCustomizerData: function(){
            let customizerdata = localStorage.getItem('customizer_data') !== null ? JSON.parse(localStorage.getItem('customizer_data')): {};
            switch(this.srcType){
                case 'default':
                    if(customizerdata.hasOwnProperty('product')) {
                        if(customizerdata['product'][this.productSku]!== undefined){
                            delete customizerdata['product'][this.productSku];
                        }
                    }
                    break;
                case 'request_quote':
                    if(window.customizerConfig.quote !== undefined)
                    {
                        customizerdata.hasOwnProperty('request_quote') &&
                        customizerdata['request_quote'].hasOwnProperty(window.customizerConfig.quote.item_id) ?
                            delete customizerdata['request_quote'][window.customizerConfig.quote.item_id]: '';
                    }
                    break;
                case 'saved_designs':
                    if(window.customizerConfig.saved_designs !== undefined)
                    {
                        customizerdata.hasOwnProperty('saved_designs') &&
                        customizerdata['saved_designs'].hasOwnProperty(window.customizerConfig.saved_designs.id) ?
                            delete customizerdata['saved_designs'][window.customizerConfig.saved_designs.id]: '';
                    }
                    break;
            }
            localStorage.setItem('customizer_data', JSON.stringify(customizerdata));
            this.customizerData = '';
        },
        resolveQuoteAllowed: function (){
            return (window.customizerConfig.quote_active);
        },
        saveInStorage: function($value){
            $value = ko.toJS($value)
            let customizerdata = localStorage.getItem('customizer_data') !== null ? JSON.parse(localStorage.getItem('customizer_data')): {};
            var newDate = new Date();
            let data = {
                'options': $value,
                'expiry': newDate.getTime()+3600*1000
            }
            switch(this.srcType){
                case 'default':
                    customizerdata.hasOwnProperty('product') ?'':customizerdata['product'] = {};
                    customizerdata['product'][this.productSku] = data;
                    break;
                case 'checkout':
                    if(window.customizerConfig.quote !== undefined)
                    {
                        customizerdata.hasOwnProperty('checkout') ?'':customizerdata['checkout'] = {};
                        customizerdata['checkout'][window.customizerConfig.quote.item_id] = data
                    }
                    break;
                case 'request_quote':
                    if(window.customizerConfig.quote !== undefined)
                    {
                        customizerdata.hasOwnProperty('request_quote') ?'':customizerdata['request_quote'] = {};
                        customizerdata['request_quote'][window.customizerConfig.quote.item_id] = data
                    }
                    break;
                case 'saved_designs':
                    if(window.customizerConfig.saved_designs !== undefined)
                    {
                        customizerdata.hasOwnProperty('saved_designs') ?'':customizerdata['saved_designs'] = {};
                        customizerdata['saved_designs'][window.customizerConfig.saved_designs.id] = data
                    }
                    break;
            }
            customizerdata['last_updated'] = Date.now();
            localStorage.setItem('customizer_data', JSON.stringify(customizerdata));
        },
        existInStorage:function (){
            let exist = false;
            let data = localStorage.getItem('customizer_data');
            if (data) {
                data = JSON.parse(data);
                //this.srcType = 'your_selection';
                switch(this.srcType){
                    case 'checkout':
                        if(window.customizerConfig.quote !== undefined)
                        {
                            exist = (data.hasOwnProperty('checkout') && data['checkout'].hasOwnProperty(window.customizerConfig.quote.item_id));
                        }
                        break;
                    case 'default':
                            exist = data.hasOwnProperty('product') && data['product'].hasOwnProperty(this.productSku);
                        break;
                    case 'request_quote':
                        if(window.customizerConfig.quote !== undefined)
                        {
                            exist = data.hasOwnProperty('request_quote') && data['request_quote'].hasOwnProperty(window.customizerConfig.quote.item_id);
                        }
                        break;
                    case 'saved_designs':
                        if(window.customizerConfig.saved_designs !== undefined)
                        {
                            exist = data.hasOwnProperty('saved_designs') && data['saved_designs'].hasOwnProperty(window.customizerConfig.saved_designs.id);
                        }
                        break;
                }
                return exist;
            }
        },
        resolveSrcType: function() {
            this.getCustomizerData();
            return this.srcType;
        },
        resolveQuoteScope: function (){
            return this.quoteScope;
        },
        resolveSizeSelection: function() {
            return (this.getCustomizerData().size !== undefined ? this.getCustomizerData().size: '') ;
        },
        resolveFrameSelection: function() {
            return (this.getCustomizerData().frame !== undefined ? this.getCustomizerData().frame.active_item: '');
        },
        resolveMatSelection: function() {
            return (this.getCustomizerData().mat !== undefined ? this.getCustomizerData().mat: '');
        },
        resolveTopMatSelection: function() {
            return ((this.getCustomizerData().mat !== undefined && this.getCustomizerData().mat.active_items.top_mat !== undefined)
                    ? this.getCustomizerData().mat.active_items.top_mat : '');
        },
        resolveMiddleMatSelection: function() {
            return ((this.getCustomizerData().mat !== undefined && this.getCustomizerData().mat.active_items.middle_mat !== undefined)
                ? this.getCustomizerData().mat.active_items.middle_mat : '')
        },
        resolveBottomMatSelection: function() {
            return ((this.getCustomizerData().mat !== undefined && this.getCustomizerData().mat.active_items.bottom_mat !== undefined)
                ? this.getCustomizerData().mat.active_items.bottom_mat : '');
        },
        resolveChalkboardSelection: function() {
            return (this.getCustomizerData().chalk_board !== undefined ? this.getCustomizerData().chalk_board.active_item: '');
        },
        resolveCorkboardSelection: function() {
            return (this.getCustomizerData().cork_board !== undefined ? this.getCustomizerData().cork_board.active_item: '');
        },
        resolveDryeraseboardSelection: function() {
            return (this.getCustomizerData().dryerase_board !== undefined ? this.getCustomizerData().dryerase_board.active_item: '');
        },
        resolveFabricSelection: function() {
            return (this.getCustomizerData().fabric !== undefined ? this.getCustomizerData().fabric.active_item: '');
        },
        resolveLetterBoardSelection: function() {
            return (this.getCustomizerData().letter_board !== undefined ? this.getCustomizerData().letter_board.active_item: '');
        },
        resolveBackingBoardSelection: function() {
            return (this.getCustomizerData().backing_board !== undefined ? this.getCustomizerData().backing_board.active_item: '');
        },
        resolveLightingSelection: function() {
            return (this.getCustomizerData().lighting !== undefined ? this.getCustomizerData().lighting.form_data: '');
        },
        resolveAddonsSelection:function() {
            return (this.getCustomizerData().addons !== undefined ? this.getCustomizerData().addons: '');
        },
        resolveGlassSelection: function() {
            return (this.getCustomizerData().glass !== undefined ? this.getCustomizerData().glass.active_item: '');
        },
        resolveShelvesSelection: function() {
            return (this.getCustomizerData().shelves !== undefined ? this.getCustomizerData().shelves : '');
        },
        resolvePostFinishSelection: function() {
            return (this.getCustomizerData().post_finish !== undefined ? this.getCustomizerData().post_finish.active_item : '');
        },
        resolveExteriorLaminateSelection: function() {
            return (this.getCustomizerData().laminate_finish !== undefined ? this.getCustomizerData().laminate_finish.active_items.laminate_exterior :'');
        },
        resolveInteriorLaminateSelection: function() {
            return (this.getCustomizerData().laminate_finish ? this.getCustomizerData().laminate_finish.active_items.laminate_interior: '');
        },
        resolveAccessoriesSelection: function() {
            return (this.getCustomizerData().accessories !== undefined ? this.getCustomizerData().accessories.active_items: '');
        },
        resolveWallColor: function() {
            let wallColor = (this.getCustomizerData().additional_data === undefined) ?
                '#FFFFFF':
                this.getCustomizerData().additional_data.wallcolor.color;

            return wallColor;
        },
        resolveHeaderText: function () {
            return this.getCustomizerData().header !== undefined ? this.getCustomizerData().header.text_header: '';
        },
        resolveOpenings: function () {
            return this.getCustomizerData().openings !== undefined ? this.getCustomizerData().openings: '';
        },
        resolveLabelText: function () {
            return this.getCustomizerData().label !== undefined ? this.getCustomizerData().label.text_label: '';
        },
    }

});
