define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'uiRegistry',
    'mage/template',
    'Magento_Ui/js/modal/modal',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Ziffity_ProductCustomizer/js/dropdowndialog-utils',
    'text!Ziffity_ProductCustomizer/template/popup/frame.html',
    'text!Ziffity_ProductCustomizer/template/popup/mat.html',
    'text!Ziffity_ProductCustomizer/template/popup/size-restriction.html',
], function ($, _, ko, Component, registry, template, modal, performAjax, dropdowndialog, frame, mat, sizeTpl) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Ziffity_ProductCustomizer/actions',
            frameDetails: ko.observable(),
            matDetails: ko.observable(),
            isMatEnabled: ko.observable(false),
            toggleUpload: ko.observable(false),
            toggleUploadImage: ko.observable(false),
            toggleBoxShadow:ko.observable(false),
            openings: ko.observableArray(),
            graphics:ko.observableArray([]),
            fileData: ko.observable(),
            uploadImg: ko.observable(''),
            imgUploaded: ko.observable(false),
            editmode: ko.observable(),
            uploadAllowed: ko.observable(),
            hasOpening: ko.observable(false),
            backingBoardExist: false,
            temp: 'frame',
            position: 'right',
            opening:2,
            imports: {
                editmode: '${ $.provider }:editmode',
                options: '${ $.provider }:options',
                isMatEnabled: '${ $.provider }:isMatEnabled',
                graphics: '${ $.provider }:img_upload',
                openingDataArray: '${ $.provider }:options.openings.openingDataArray',
            },
            exports:{
                uploadImg: '${ $.provider }:options.additional_data.graphic_img',
                imgUploaded: '${ $.provider }:img_uploaded',
                toggleUploadImage: '${ $.provider }:options.openings.toggle_upload_img',
                toggleBoxShadow:'${ $.provider }:options.openings.toggle_shadow_box',
            }

        },
        initialize: function() {
            this._super();
            var self = this;
            self.toggleUpload(true);
            if(window.customizerConfig.options.hasOwnProperty('backing_board')){
                self.backingBoardExist = true;
            }
            ko.computed(function(){
                self.uploadAllowed(self.editmode() && self.options.hasOwnProperty('backing_board'));
            })
            // self.openingDataArray.foreach(function(value){
            //     console.log(value);
            // })
            ko.bindingHandlers.toggleClick = {
                init: function (element, valueAccessor) {
                    var value = valueAccessor();

                    ko.utils.registerEventHandler(element, "click", function () {
                        value(!value());
                    });
                }
            };
            ko.bindingHandlers.popover = {
                init: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
                    ko.bindingHandlers.value.init(element, valueAccessor, allBindings);
                    var source = allBindings.get('popoverTitle');
                    var sourceUnwrapped = ko.unwrap(source);
                    $(element).popover({
                        trigger: 'focus',
                        content: valueAccessor(),
                        title: sourceUnwrapped
                    });
                },
                update: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
                    var value = valueAccessor();
                    ko.bindingHandlers.value.update(element, valueAccessor);
                }
            };
        },


        createGraphic: function(obj){
            let self = this;
            self.name = ko.observable(obj.name);
            self.url = ko.observable(obj.url);
        },

        formatUploadUrl: function(url){
            console.log(url);
            return url;
        },

        loadFrameDetails: function() {
            var self = this;
            self.temp = 'frame';
            var frame = self.options.frame.active_item.id;
            self.getDetails(self, 'frame', frame);
        },

        loadMatDetails: function() {
            var self = this;
            let ids = [];
            self.temp = 'mat';
            var selections = self.options.mat.active_items;
            self.getDetails(self,'mat', selections);
        },

        handleFileSelect: function(element, event){
            let self = this;
            console.log((element, event));
            var file    = event.target.files[0];
            var reader  = new FileReader();

            reader.onloadend =  (e) =>
            {
                var result = reader.result; // Here is your base 64 encoded file. Do with it what you want.
                //self.uploadImg(result);
                console.log(file);
                element.url(result);
                element.name(file['name']);
                self.imgUploaded() === true ? self.imgUploaded(false): self.imgUploaded(true);
            };

            if(file)
            {
                reader.readAsDataURL(file);
            }
            console.log(self);
        },

        resetUpload: function(element, event){
            console.log(element, event);
            var self = this;
            element.url('');
            element.name('');
            $(event.currentTarget.parentElement).find('input[type=file]').val("");
            self.imgUploaded() === true ? self.imgUploaded(false): self.imgUploaded(true);
        },

        getDetails: function(self, type, ids){
            let data = {};
            let result;
            data.type = type;
            data.ids = ids;
            data.getDetails = true;
            data.selections = self.options;
            result = performAjax.performAjaxOperation('customizer/option/getDetails/','POST',data);
            result.done(function(response){
                self.loadTemplate(response);
                $('body').trigger('processStop');
            })
        },

        loadTemplate: function (response){
            var self = this;
            var tpl = '';
            var header = '';
            if(response) {
                if(self.temp === 'frame') {
                    header = '<h4>Frame Details</h4>';
                     tpl = template(frame, {response})
                }
                if(self.temp === 'mat') {
                    header = '<h4>Matboard Details</h4>';
                    tpl = template(mat, {response})
                }
                self.loadPopup(tpl, header);
            }
        },
        uploadImage:function(element,event){
            console.log(element, event);
            let self = this;
            var file    = event.target.files[0];
            var reader  = new FileReader();

            reader.onloadend = function (onloadend_e)
            {
                var result = reader.result; // Here is your base 64 encoded file. Do with it what you want.
                self.uploadImg(result);
            };

            if(file)
            {
                reader.readAsDataURL(file);
            }

        },

        loadPopup: function(content, header) {
            var self = this;
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('popup',{
                    header: header,
                    content:content,
                    show:true,
                    position:self.position

                });
                $('body').addClass('slidein-active');
                var popup = document.querySelector('.customizer-slidein');
                var main_popup = document.querySelector('.customizer-main-popup')
                popup.style.display = 'flex';
                main_popup.style.cssText = 'animation:slide-in-'+self.position+' .5s ease; animation-fill-mode: forwards';
            });
        },
        loadUploadImagePopup: function(){
            console.log('uploadimage');
            var viewModel = this;
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: '',
                clickableOverlay: false,
                customTpl: sizeTpl,
                buttons: [{
                    text: $.mage.__('OK'),
                    class: 'size-restriction-modal',
                    click: function () {
                        this.closeModal();
                    }
                }],
                opened: function () {
                    // Because magento modal copies the dom... we need to apply bindings. But it only copies once. So we want to only apply the bindings on first open.
                    if (this.appliedBindings === undefined) {
                        ko.applyBindings(viewModel, this);
                        this.appliedBindings = true;
                    }
                }
            };
            var popup = modal(options, $('#custom-popup-modal'));
            $("#custom-popup-modal").html(sizeTpl);
            $("#custom-popup-modal").modal("openModal");
        },

        renderDropdownDialog: function(){
            dropdowndialog.renderDropdownDialog($("#upload-content-wrappers"), {
            //     // appendTo: '.dropdown-wrap',
            //     // triggerTarget: '.dropdown-button',
            //     // closeOnMouseLeave: false,
            //     // closeOnEscape: true,
            //     // timeout: 2000,
            //     // triggerClass: 'active',
            //     // parentClass: 'active',
            //     // buttons: [{
            //     // text: $.mage.__('Close'),
            //     // click: function () {
            //     //     $(this).dropdownDialog("close");
            //     // }
            // }]
                "appendTo": "[data-block=dropdown]",
                "triggerTarget":"[data-trigger=trigger]",
                "autoPosition": true,
                "position":  { my: 'center top', at: 'right-20 top-70', of: "[data-trigger=trigger]" },
                "timeout": 2000,
                "closeOnMouseLeave": false,
                "closeOnEscape": true,
                "autoOpen": false,
                "triggerClass": "active",
                "parentClass": "active",
                "buttons": []
            });
        }

    } );
});
