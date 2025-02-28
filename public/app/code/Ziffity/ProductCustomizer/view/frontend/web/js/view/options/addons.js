define([
    'jquery',
    'underscore',
    'ko',
    'uiRegistry',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'text!Ziffity_ProductCustomizer/template/optioninfo/addon-info.html',
], function ($, _, ko, registry, AbstractOption, stepNavigator, performAjax, customizerDataResolver, info) {
    'use strict';

    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(false),
            isVisible:ko.observable(false),
            selection : ko.observable(),
            plungeLock: ko.observable('no'),
            hingePosition: ko.observable('left'),
            plungeImg: require.toUrl('Ziffity_ProductCustomizer/images/addon-1.jpg'),
            rightHingeImg: require.toUrl('Ziffity_ProductCustomizer/images/addon-3.jpg'),
            leftHingeImg: require.toUrl('Ziffity_ProductCustomizer/images/addon-4.jpg'),
            price: window.customizerConfig.options.addons.form_data.plunge.unit_price,
            template: 'Ziffity_ProductCustomizer/options/addons',
            position: 60,
            exports: {
                plungeLock: '${ $.provider }:options.addons.form_data.plunge_lock',
                hingePosition: '${ $.provider }:options.addons.form_data.hinge_position'
            },
            listens: {
                '${ $.provider }:editmode': 'updateSelection',
            },
        },
        initSelection:function(){
            var self = this;
            let selection = customizerDataResolver.resolveAddonsSelection();
            self.plungeLock(selection.form_data.plunge_lock);
            self.hingePosition(selection.form_data.hinge_position);
        },
        updateSelection: function(value) {
            if(!value) {
                this.initSelection();
            }
        },
        initialize: function() {
            this._super();
            var self =this;
            this.initSelection();
            stepNavigator.registerStep(
                'Add-on',
                'addons',
                this.isActive,
                this.isVisible,
                self.position,
                self.sortOrder,
                false,
                true,
                info
            );
        },
        plungeDetails: function() {
            var self = this;
            var position = 'right';
            var content = "<p style='text-align: center'><img src="+
                require.toUrl('Ziffity_ProductCustomizer/images/Wide_Face_Plunge_Lock.jpg')+">" +
                "<br>Side Plunge Lock (All Keyed the Same)</p>";
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
        hingeDetails: function() {
            var self = this;
            var position = 'right';
            var content = "<p style='text-align: center'><img src="+require.toUrl('Ziffity_ProductCustomizer/images/Left_Right_Hinge_Drawings_1.jpg')+
                "><br>Choose Left or Right Hinge for your SwingFrame</p>";
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

        }
    });
});
