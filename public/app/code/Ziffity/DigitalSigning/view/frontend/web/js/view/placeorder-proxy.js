define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment-service',
], function (ko, $, Component, stepNavigator, checkoutData, quote, paymentService) {
    'use strict';
    return Component.extend({
        validations : [],
        initialize: function(){
            this._super();
            this.enablePlaceOrder = ko.computed(function (){
                var selectedPaymentMethod = quote.paymentMethod();
                if (selectedPaymentMethod && selectedPaymentMethod.method === 'braintree_paypal') {
                    return false;
                }
                return true;
            });
        },
        addValidation : function(validation){
            this.validations.push(validation);
        },
        isButtonVisible: function () {
            return stepNavigator.getActiveItemIndex() == 1;
        },
        linkPlaceOrder: function(element, value){
            var self = this;
            $(element).on("click", function () {
                var isValid = true;
                self.validations.forEach(function(validateAction){
                    if(!validateAction()){
                        isValid  = false;
                    }
                });
                if(isValid){
                    let paymentMethod = checkoutData.getSelectedPaymentMethod();

                    switch(paymentMethod) {
                        case 'braintree_paypal':
                            break;
                        case 'braintree_googlepay':
                            $('.braintree-googlepay-button').trigger('click');
                            break;
                        default:
                            $(".payment-method._active")
                                .find('.action.primary.checkout')
                                .trigger( 'click' );
                    }
                }
            });
        },
    });
});
