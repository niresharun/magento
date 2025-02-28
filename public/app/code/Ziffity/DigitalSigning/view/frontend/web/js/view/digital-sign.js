define([
    'ko',
    'jquery',
    'uiComponent',
    'uiRegistry',
    'mage/url',
    'Magento_Checkout/js/model/totals',
    'Ziffity_DigitalSigning/js/model/digital-signing',
    'FabricJs',
], function (ko, $, Component,uiRegistry, url, totals, digitalSigning) {
    'use strict';
    return Component.extend({
        defaults:{
            agree: ko.observable(false),
            digiSignAgree: ko.observable(false),
            digiSignCanvas : ko.observable(false),
            digiSignEnabled: ko.observable(false),
            errors:{
                agree:ko.observable(false),
                canvas:ko.observable(false),
            },
            exports:{
                digiSignAgree:'checkout.steps.billing-step.payment.payments-list.braintree_paypal:digiSignAgree',
                digiSignCanvas:'checkout.steps.billing-step.payment.payments-list.braintree_paypal:digiSignCanvas',
                digiSignEnabled:'checkout.steps.billing-step.payment.payments-list.braintree_paypal:digiSignEnabled',
                errors: '${ $.provider }:errors'
            },
        },

        initialize: function() {
            var self = this;
            this._super();
            ko.computed(function(){
                totals.totals().grand_total >= window.checkoutConfig.digitalSigningThreshold ?
                    self.digiSignEnabled(true):
                    self.digiSignEnabled(false);
            }, self)
            var parent = uiRegistry.get(this.parentName);
            parent.addValidation(self.validateAgree.bind(self));
            parent.addValidation(self.validateCanvas.bind(self));
            self.agree.subscribe(function(value) {
                digitalSigning.setAgreement(value);
                self.digiSignAgree(value);
                if(self.validateAgree()){
                self.errors.agree(false);
                }
            });
        },
        validateAgree: function () {
            var isValid = true;
            if(totals.totals().grand_total >= window.checkoutConfig.digitalSigningThreshold){
                if(!digitalSigning.getAgreement()){
                    isValid = false;
                    this.errors.agree(true);
                }
            }
            return isValid;
        },
        validateCanvas: function () {
            var isValid = true;
            if(totals.totals().grand_total >= window.checkoutConfig.digitalSigningThreshold){
                if(digitalSigning.getImage() === null){
                    isValid = false;
                    this.errors.canvas(true);
                }
            }
            return isValid;
        },
        isVisible: function () {
            return totals.totals().grand_total >= window.checkoutConfig.digitalSigningThreshold;
        },
        initCanvas: function(element, value) {
            var self = this;
            var canvas = new fabric.Canvas(element, {
                isDrawingMode:true
            });
            canvas.on('path:created', function(event) {
                var imageData = "data:image/svg+xml;utf8," + encodeURIComponent(event.path.canvas.toSVG());
                    digitalSigning.setImage(imageData);
                    self.digiSignCanvas(true);
                    if(self.validateCanvas()){
                        self.errors.canvas(false);
                    }
            });
            $('#clearCanvas').on('click', function(event, data) {
                canvas.clear();
                digitalSigning.setImage(null);
                self.digiSignCanvas(false);
            });
        },
        getCustomUrl: function() {
            return url.build('terms-of-use');
        }
    });
});
