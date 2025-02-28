define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'jquery/colorpicker/js/colorpicker',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
], function ($, _, ko, Component, colorpicker, customizerDataResolver) {
    'use strict';

    return Component.extend({
        defaults: {
            wallcolor: ko.observable('#FFFFFF'),
            currentColor:ko.observable(),
            colorChanged: ko.observable(false),
            editmode: ko.observable(),
            exports: {
                wallcolor: '${ $.provider }:options.additional_data.wallcolor.color',
                currentColor: '${ $.provider }:options.additional_data.wallcolor.current_color'
            },
            imports:{
                editmode: '${ $.provider }:editmode',
            },
            listens:{
                '${ $.provider }:editmode': 'updateSelection'
            },
        },

        initSelection: function() {
            let self = this;
            let color = customizerDataResolver.resolveWallColor();
            this.currentColor(color);
            this.currentColor.subscribe(function(value){
                if(value !== '#FFFFFF'){
                    self.colorChanged(true)
                } else {
                    self.colorChanged(false)
                }

            })
            // ko.computed(function(){
            //     self.editmode() ? self.currentColor(self.wallcolor()) : self.currentColor('#FFFFFF');
            // },self)
        },
        updateSelection: function(value) {
            let self =this;
            if(value) {
                self.currentColor(self.wallcolor());
            } else {
                self.currentColor('#FFFFFF');
            }
        },
        resetColor: function(){
            let self =this;
            self.currentColor('#FFFFFF');
            self.wallcolor('#FFFFFF');
        },

        initialize: function(){
            this._super();
            var self = this;
            self.initSelection();
            // jQuery('#colorSelector').colorpicker({
            //     color:'#00FF00',
            //     colorFormat: ['#HEX']
            // });
            // $.fn.extend({
            //     ColorPicker: colorpicker.init,
            //     ColorPickerHide: colorpicker.hidePicker,
            //     ColorPickerShow: colorpicker.showPicker,
            //     ColorPickerSetColor: colorpicker.setColor
            // });
            // $('#colorSelector').ColorPicker({
            //     color: '#000',
            //     onShow: function (colpkr) {
            //         $(colpkr).fadeIn(500);
            //         return false;
            //     },
            //     onHide: function (colpkr) {
            //         $(colpkr).fadeOut(500);
            //         return false;
            //     },
            //     onChange: function (hsb, hex, rgb) {
            //         $('#colorSelector').css('backgroundColor', '#' + hex);
            //     }
            // });
            //            $('#<%=txtReserveType.ClientID %>')

    $(document).ready(function () {
                $('#colorSelector').ColorPicker({
                    onSubmit: function (hsb, hex, rgb, el) {
                        $(el).val('#' + hex);
                        $(el).ColorPickerHide();
                        var borderColor = $('#tbcontentBorder').val();
                        $('#news').css('border-color', borderColor);
                    },
                    onBeforeShow: function () {
                        $(this).ColorPickerSetColor(this.value);
                    }
                }).bind('keyup', function () {

                    $(this).ColorPickerSetColor(this.value);

                });
            });
        },

        wallColors: function(event, elemet, value) {
            var self = this;
           self.currentColor(elemet.currentTarget.value);
            self.wallcolor(elemet.currentTarget.value);
        }
    });
});
