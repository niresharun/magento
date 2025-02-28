define([
    'jquery',
    'ko',
    'mage/template',
    'uiRegistry',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'text!Ziffity_ProductCustomizer/template/popup/frame.html',
    'text!Ziffity_ProductCustomizer/template/popup/mat.html',
    'text!Ziffity_ProductCustomizer/template/popup/cork-board.html',
    'text!Ziffity_ProductCustomizer/template/popup/dryerase-board.html',
    'text!Ziffity_ProductCustomizer/template/popup/fabric.html',
    'text!Ziffity_ProductCustomizer/template/popup/letter-board.html',
    'text!Ziffity_ProductCustomizer/template/popup/chalk-board.html',
    'text!Ziffity_ProductCustomizer/template/popup/laminate.html',
], function ($, ko, template, registry,  performAjax, frame, mat, corkboard, dryeraseboard, fabric, letterboard, chalkboard, laminate) {
    'use strict';

    let mats = ko.observableArray();
    let position =  'right';

    return {
       showPopup:function(type, value, price=null) {
           if(type){
               let header = '';
               switch (type) {
                   case 'frame':
                       header = '<h4>Frame Details</h4>';
                       this.getDetails(value, type, frame, header);
                       break;
                   case 'top_mat':
                       header = '<h4>Top Mat Details</h4>';
                       this.getDetails(value, type, mat, header);
                       break;
                   case 'middle_mat':
                       header = '<h4>Middle Mat Details</h4>';
                       this.getDetails(value, type, mat, header);
                       break;
                   case 'bottom_mat':
                       header = '<h4>Bottom Mat Details</h4>';
                       this.getDetails(value, type, mat, header);
                       break;
                   case 'chalk_board':
                       header = '<h4>Chalk Board Details</h4>';
                       this.getDetails(value, type, chalkboard, header);
                       break;
                   case 'cork_board':
                       header = '<h4>Corkboard Details</h4>';
                       this.getDetails(value, type, corkboard, header);
                       break;
                   case 'dryerase_board':
                       header = '<h4>Dry Erase Board Details</h4>';
                       this.getDetails(value, type, dryeraseboard, header);
                       break;
                   case 'letter_board':
                       header = '<h4>Frame Details</h4>';
                       this.getDetails(value, type, letterboard, header);
                       break;
                   case 'fabric':
                       header = '<h4>Fabric Details</h4>';
                       this.getDetails(value, type, fabric, header);
                       break;
                   case 'laminate_exterior':
                       header = '<h4>Laminate Details</h4>';
                       this.getDetails(value, type, laminate, header);
                       break;
                   case 'laminate_interior':
                       header = '<h4>Laminate Details</h4>';
                       this.getDetails(value, type, laminate, header);
                       break;
               }
           }

       },
        getDetails: function (value, type, tmp, header){
            let data = {};
            let result;
            let self = this;
            data.type = type;
            data.ids = value.id;
            data.price = value.price;
            result = performAjax.performAjaxOperation('customizer/option/getDetails/','GET',data);
            result.done(function(response){
                $('body').trigger('processStop');
                self.loadTemplate(response, tmp, header)
            }, self)
        },
        loadTemplate: function(response, tmp, header){
            var self = this;
            var tpl = '';
            if(response) {
                tpl = template(tmp, {response})
                self.loadPopup(tpl, header);
            }
        },
        loadPopup: function(content, header) {
            var self = this;
            let position = 'right';
            registry.async('customizerProvider')(function (customizerProvider) {
                customizerProvider.set('popup',{
                    header: header,
                    content:content,
                    show:true,
                    position:position

                }, self);
                $('body').addClass('slidein-active');
                var popup = document.querySelector('.customizer-slidein');
                var main_popup = document.querySelector('.customizer-main-popup')
                popup.style.display = 'flex';
                main_popup.style.cssText = 'animation:slide-in-'+position+' .5s ease; animation-fill-mode: forwards';
            });
        },

    };
});
