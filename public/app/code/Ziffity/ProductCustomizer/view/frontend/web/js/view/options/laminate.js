define([
    'jquery',
    'underscore',
    'ko',
    'uiRegistry',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Magento_Catalog/js/price-utils',
    'Ziffity_ProductCustomizer/js/model/laminate-types',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'Ziffity_ProductCustomizer/js/view/your-selections',
    'text!Ziffity_ProductCustomizer/template/optioninfo/laminate-info.html',
], function ($, _, ko, registry, AbstractOption, stepNavigator, priceUtils, laminateTypes, customizerDataResolver,yourSelection, info) {
    'use strict';

    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(false),
            isVisible:ko.observable(false),
            productSelection:ko.observable(),
            productSku:window.customizerConfig.productSku,
            position: 70,
            index:2,
            selection :ko.observable(),
            pageNumber:ko.observable(0),
            laminates: laminateTypes.laminates,
            productsPerPage:2,
            offsetValue:0,
            template: 'Ziffity_ProductCustomizer/options/laminate',
            exports: {
                isActive: '${ $.provider }:options.laminate_finish.isActive',
            },
            imports: {
                options: '${ $.provider }:options'
            }
        },
        initialize: function () {
            this._super();
            let self = this;
            stepNavigator.registerStep(
                'Laminate Finish',
                'laminate_finish',
                this.isActive,
                this.isVisible,
                self.position,
                self.sortOrder,
                false,
                false,
                info
            );
        },
        callPopup:function(){
            yourSelection().loadSelectionPopup();
        },
        updateCurrentLaminateType: function (laminateType) {
            let self = this;
            ko.utils.arrayForEach(self.laminates(), function (element) {
                element.isActive(false);
                if (element.code == laminateType.code) {
                    element.isActive(true);
                }
            });
        },
        laminateDetails: function() {
            var self = this;
            var position = 'right';
            var content = '<span class="it-content"><label for="tooltip-laminate-wats" class="close-tooltip"></label>' +
                '<strong class="ict-heading">What is Laminate?</strong><span class="itc-holder"><br><strong class="itc-sub-heading">' +
                '<span class="itc-text">Laminate is a thin durable film of material, bonded (glued) to wood surfaces that enhance your shadow box design. Laminates are offered in a wide variety of metal or faux wood finishes and solid colors. Based on your design selections, the exterior and interior laminates will either compliment or contrast the front picture frame finish you\'ve chosen.</span>' +
                '<br><span class="itc-text">Have questions? <a href="https://www.displayframes.com/contact/">Contact our design specialists for help.</a></span>' +
                "<img src="+require.toUrl('Ziffity_ProductCustomizer/images/laminate/tooltip-laminate.jpg')+"></strong></span></span>"
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('popup',{
                    content:content,
                    show:true,
                    position:position

                });
                var popup = document.querySelector('.customizer-slidein');
                var main_popup = document.querySelector('.customizer-main-popup')
                popup.style.display = 'flex';
                main_popup.style.cssText = 'animation:slide-in-'+position+' .5s ease; animation-fill-mode: forwards';
            });

        },
    });
});
