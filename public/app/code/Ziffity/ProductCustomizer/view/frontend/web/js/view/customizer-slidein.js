define([
    'jquery',
    'ko',
    'uiComponent',
    'uiRegistry',
], function ($, ko, Component, registry) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Ziffity_ProductCustomizer/customizer-slidein',
            content: ko.observable(),
            position: ko.observable('left'),
            header: ko.observable(),
            show: ko.observable(false),
            imports: {
                content: '${ $.provider }:popup.content',
                header: '${ $.provider }:popup.header',
                position: '${ $.provider }:popup.position',
                show: '${ $.provider }:popup.show',
            }
        },
        initialize: function() {
            this._super();
            var self = this;
            $(document).on('click','.popup-overlay', (function(){
                self.close();
            }));
        },
        close: function() {
            var self = this;
            var popup = document.querySelector('.customizer-slidein');
            var main_popup = document.querySelector('.customizer-main-popup');
            $('body').removeClass('slidein-active');
            if(self.position() == 'left') {
                main_popup.style.cssText = 'animation:slide-out-left .5s ease; animation-fill-mode: forwards';
            } else {
                main_popup.style.cssText = 'animation:slide-out-right .5s ease; animation-fill-mode: forwards';
            }
            setTimeout(()=>{
                popup.style.display = 'none';
            }, 500)
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('popup',{
                    header: '',
                    content:'',
                    show:false
                });
            })
        },

    });
});
