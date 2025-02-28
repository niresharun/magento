 define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'uiRegistry',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
     'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
], function ($, _, ko, Component, registry, stepNavigator, customizerDataResolver) {
    'use strict';

    var position = "right";
    return Component.extend({
        editmode: ko.observable(true),
        defaults: {
            template: 'Ziffity_ProductCustomizer/option-group-list',
            visible: true,
            canScrollLeft: ko.observable(false),
            canScrollRight: ko.observable(false),
            steps:stepNavigator.steps,
            scrollLeft: ko.observable(0),
            navigateTo: ko.observable(),
            imports: {
                editmode: '${ $.provider }:editmode',
                srcType: '${ $.provider }:options.additional_data.src_type',
                canScrollLeft: '${ $.provider }:canScrollLeft',
                canScrollRight: '${ $.provider }:canScrollRight',
            },
            listens:{
                '${ $.provider }:editmode': 'updateProccessedTabs'
            },
            exports: {
                canScrollLeft: '${ $.provider }:canScrollLeft',
                canScrollRight: '${ $.provider }:canScrollRight',
                navigateTo: '${ $.provider }:navigate',
            },
        },
        updateProccessedTabs: function(value)
        {
            var self = this;
            if(value){
                if(self.srcType === 'default' && !customizerDataResolver.existInStorage()) {
                    stepNavigator.resetFirstStep();
                    stepNavigator.resetAllProcessed();
                }
            }

        },
        initialize: function() {
            this._super();
            let self = this;
            this.initiateButtons();

            $(window).resize(function(){
                if (_.findIndex($('.option-group-tab .options').toArray(),[0])) {
                    var optionSelector = $('.option-group-tab .options')[0];
                    if(optionSelector.scrollWidth > optionSelector.clientWidth) {
                        self.canScrollRight(true);
                    } else {
                        self.canScrollRight(false);
                    }
                }
            })
        },
        navigate: function(item, self) {
            var self = this;
            self.navigateTo() ? self.navigateTo(false): self.navigateTo(true);
            let checkProcessed = false;
            if(self.srcType == 'default') {
                checkProcessed = true;
            }
            stepNavigator.navigateTo(item.code, true);
        },

        loadInfoSlide: function(element, event) {
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('popup',{
                    content:element.info,
                    show:true,
                    position:position

                });
                var popup = document.querySelector('.customizer-slidein');
                var main_popup = document.querySelector('.customizer-main-popup')
                popup.style.display = 'flex';
                main_popup.style.cssText = 'animation:slide-in-'+position+' .5s ease; animation-fill-mode: forwards';
            });
        },

        scrollBarLeft: function(element, event) {
            var self = this;
            var optionSelector = $('.option-group-tab .options')[0];
            var clientWidth = optionSelector.clientWidth;
            var scrollLeft = optionSelector.scrollLeft;
            var scrollWidth = optionSelector.scrollWidth;
            var updatedScroll = scrollLeft - clientWidth* 0.3;
            clientWidth !== scrollWidth ? self.canScrollRight(true): self.canScrollRight(false);
            var nextScroll = scrollLeft - 2 * (clientWidth* 0.3);
            nextScroll <= 0 ? self.canScrollLeft(false) : self.canScrollLeft(true);
            if(scrollLeft != 0) {
                self.scrollLeft(updatedScroll);
                $('.option-group-tab .options').animate({
                    scrollLeft: updatedScroll
                }, 600);
            }
        },

        scrollBarRight: function(element, event) {
            var self = this;
            var optionSelector = $('.option-group-tab .options')[0];
            var clientWidth = optionSelector.clientWidth;
            var scrollLeft = optionSelector.scrollLeft;
            var scrollWidth = optionSelector.scrollWidth;
            var updatedScroll = clientWidth* 0.3 + scrollLeft;
            var nextScroll = (clientWidth*0.3)*2+scrollLeft;
            clientWidth !== scrollWidth ? self.canScrollLeft(true): self.canScrollLeft(false);
            if(clientWidth+nextScroll >= scrollWidth ) {
                self.canScrollRight(false)
            }
            $('.option-group-tab .options').animate({
                scrollLeft: updatedScroll
            }, 600);
        },

        initiateButtons: function() {
            var self = this;
            this.editmode.subscribe(function(value){
                if(value) {
                    setTimeout(function(){
                        var optionSelector = $('.option-group-tab .options')[0];
                        if(optionSelector.scrollWidth > optionSelector.clientWidth) {
                            self.canScrollRight(true);
                        }
                    }, 200)
                    registry.async('customizerProvider')(function (customizerProvider) {
                        customizerProvider.set('exitCustomization', false);
                    });
                }
            })
        },
        exitCustomization: function() {
            stepNavigator.resetFirstStep();
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('exitCustomization', true);
            });
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('editmode', false);
            });
            $('body').removeClass('customizer-active');
        },
        resetToDefault: function() {
            stepNavigator.resetFirstStep();
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('editmode', false);
            });
            $('body').removeClass('customizer-active');
        }
    } );
});
