define(['jquery', 'uiComponent', 'ko','Magento_Ui/js/modal/modal','mage/url'],
    function ($, Component, ko,modal,urlBuilder) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Ziffity_SavedDesigns/popup',
                showPreview:ko.observable(false),
                showPopup:ko.observable(false),
                showForm:ko.observable(false),
                popupImage:ko.observable(''),
                senderName:ko.observable(''),
                senderEmail:ko.observable(''),
                nameOther:ko.observable(''),
                emailOther:ko.observable(''),
                shareCode:null,
                image:null,
                popup:null,
            },
            initialize: function () {
                this._super();
                return this;
            },
            openPopup:function (self){
                self.shareCode = this.share_code;
                self.image = this.image;
                self.resetFormFields();
                self.popupImage(self.image);
                self.showPopup(true);
                self.showForm(true);
                let options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: 'Share To Friend',
                    buttons: [{
                        text: $.mage.__('Preview Mail'),
                        class: 'action-primary',
                        click: function () {
                        // this.closeModal();
                        // self.showPopup(false);
                        self.showPreview(true);
                        self.showForm(false);
                    }
                    }]
                };
                self.popup = modal(options, $('#modal-content'));
                $('#modal-content').modal('openModal');
            },
            goBack:function(){
               let self = this;
               self.showPreview(false);
               self.showForm(true);
            },
            sendEmail:function(){
                let self = this;
                let ajax = $.post(urlBuilder.build('saveddesigns/lists/sendMail'),
                    self.getFormFields());
                $('body').trigger('processStart');
                ajax.done(function(response){
                    $('body').trigger('processStop');
                    if (response.success){
                        if (self.popup){
                            $('#modal-content').modal('closeModal');
                            self.resetFormFields();
                            self.showPopup(false);
                            self.showPreview(false);
                        }
                    }
                });
            },
            resetFormFields:function(){
                let self = this;
                self.senderName('');
                self.senderEmail('');
                self.nameOther('');
                self.emailOther('');
            },
            getFormFields:function(){
                let data = {};
                let self = this;
                data.name_customer = self.senderName();
                data.email_customer = self.senderEmail();
                data.name_other = self.nameOther();
                data.email_other = self.emailOther();
                data.share_code = self.shareCode;
                data.image = self.image;
                return data;
            }
        });
    }
);
