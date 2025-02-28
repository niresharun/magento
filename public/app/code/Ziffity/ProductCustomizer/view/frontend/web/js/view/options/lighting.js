define([
    'jquery',
    'underscore',
    'ko',
    'uiRegistry',
    'Magento_Catalog/js/price-utils',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'text!Ziffity_ProductCustomizer/template/optioninfo/lighting-info.html',
], function ($, _, ko, registry, priceUtils, AbstractOption, stepNavigator, performAjax, customizerDataResolver, info) {
    'use strict';

    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(false),
            isVisible:ko.observable(false),
            productSku:window.customizerConfig.productSku,
            // selection : ko.observable(),
            // plungeLock: ko.observable(),
            // hingePosition: ko.observable(),
            ledStripImg: require.toUrl('Ziffity_ProductCustomizer/images/led_strip_1.jpg'),
            ledTopImg: require.toUrl('Ziffity_ProductCustomizer/images/led_strip_2.jpg'),
            ledPerimeterImg: require.toUrl('Ziffity_ProductCustomizer/images/led_strip_3.jpg'),
            topLightWatt: ko.observable(),
            topLightPs: ko.observable(),
            perimeterLightWatt: ko.observable(),
            perimeterLightPs:ko.observable(),
            topLedPrice: ko.observable(),
            perimeterLedPrice: ko.observable(),
            powerConnectionPrice: ko.observable(),
            powerConnectionPlugPrice: ko.observable(),
            powerConnectionHardwiredPrice: ko.observable(),
            lightingPosition: ko.observable('top'),
            powerConnection: ko.observable('hardwired'),
            cordColor: ko.observable('black'),

            // price: window.customizerConfig.options.addons.plunge_lock.unit_price,
            template: 'Ziffity_ProductCustomizer/options/lighting',
            position: 40,
            exports: {
                lightingPosition: '${ $.provider }:options.lighting.form_data.lighting_position',
                powerConnection: '${ $.provider }:options.lighting.form_data.power_connection',
                cordColor: '${ $.provider }:options.lighting.form_data.cord_color'
            },
            imports: {
                options: '${ $.provider }:options'
            },
            listens: {
                '${ $.provider }:editmode': 'updateSelection'
            },
        },
        initSelection: function() {
            var self = this;
            let variables = customizerDataResolver.resolveLightingSelection();
            this.lightingPosition(variables.lighting_position);
            this.powerConnection(variables.power_connection);
            this.cordColor(variables.cord_color);
        },
        updateSelection: function(value) {
            if(!value) {
                this.initSelection();
            }
        },
        initialize: function() {
            this._super();
            var self =this;
            self.initLighting();
            self.initSelection();
            stepNavigator.registerStep(
                'Lighting',
                'lighting',
                this.isActive,
                this.isVisible,
                self.position,
                self.sortOrder,
                false,
                true,
                info
            );
            this.isActive.subscribe(function (value) {
                if (value) {
                    self.calculateLighting(self);
                }
            });
        },
        convertPrice:function(price){
            //TODO:// Have to change the currency format.
            var priceFormat = {
                decimalSymbol: '.',
                groupLength: 3,
                groupSymbol: ",",
                integerRequired: false,
                pattern: "$%s",
                precision: 2,
                requiredPrecision: 2
            };
            return priceUtils.formatPrice(price, priceFormat);
        },
        initLighting: function() {
            var self = this;
            if(window.customizerConfig.options.lighting.vars !== undefined) {
                let variables = window.customizerConfig.options.lighting.vars;
                self.topLightWatt(variables.top_total_led_strip_wattage);
                self.topLightPs(variables.top_led_strip_power_supply);
                self.perimeterLightWatt(variables.perimeter_total_led_strip_wattage);
                self.perimeterLightPs(variables.perimeter_led_strip_power_supply);
                self.topLedPrice(variables.top_led_strip_price);
                self.perimeterLedPrice(variables.perimeter_led_strip_price);
                self.powerConnectionPrice(variables.power_connection_price);
                self.powerConnectionPlugPrice(variables.power_connection_plug_price);
                self.powerConnectionHardwiredPrice(variables.power_connection_hardwired_price);
            }
        },
        calculateLighting: function(self) {
            let data = {};
            let result;
            data.sku = self.productSku;
            data.option = 'lighting';
            data.options = self.options;
            result = performAjax.performAjaxOperation('customizer/option/getValues/','POST',data);
            result.done(function(response){
                if(response.options.lighting.vars !== undefined) {
                    let variables = response.options.lighting.vars;
                    self.topLightWatt(variables.top_total_led_strip_wattage);
                    self.topLightPs(variables.top_led_strip_power_supply);
                    self.perimeterLightWatt(variables.perimeter_total_led_strip_wattage);
                    self.perimeterLightPs(variables.perimeter_led_strip_power_supply);
                    self.topLedPrice(self.convertPrice(variables.top_led_strip_price));
                    self.perimeterLedPrice(self.convertPrice(variables.perimeter_led_strip_price));
                    self.powerConnectionPrice(self.convertPrice(variables.power_connection_price));
                    self.powerConnectionPlugPrice(self.convertPrice(variables.power_connection_plug_price));
                    self.powerConnectionHardwiredPrice(self.convertPrice(variables.power_connection_hardwired_price));
                }
                // self.frameWidth(self.changeLabelToInches(new Fraction(response.options.shelves.frame_width).toFraction(true)));
                $('body').trigger('processStop');
            });
        },

        calculatePrice: function(self) {
            let data = {};
            let result;
            data.sku = self.productSku;
            data.option = 'lighting';
            data.options = self.options;
            result = performAjax.performAjaxOperation('customizer/option/getLightingPrice/','POST',data);
            result.done(function(response){
                self.topLightWatt(variables.top_total_led_strip_wattage);
                self.topLightPs(variables.top_led_strip_power_supply);
                self.perimeterLightWatt(variables.perimeter_total_led_strip_wattage);
                self.perimeterLightPs(variables.perimeter_led_strip_power_supply);
                self.topLedPrice(variables.top_led_strip_price);
                self.perimeterLedPrice(variables.perimeter_led_strip_price);
                self.powerConnectionPrice(variables.power_connection_price);
                self.powerConnectionPlugPrice(variables.power_connection_plug_price);
                self.powerConnectionHardwiredPrice(variables.power_connection_hardwired_price);
                // self.frameWidth(self.changeLabelToInches(new Fraction(response.options.shelves.frame_width).toFraction(true)));
                $('body').trigger('processStop');
            });
        },
        lightingTypeDetails: function() {
            var self = this;
            var position = 'right';
            var content = "<div></div><p>Our LED Strip Lights with adhesive taped back is an excellent interior lighting solution for our Shadow Box Display Cases." +
                "<br><br><span style='color: #800000;'>4200K cool white color temperature is our standard strip light we provide in our cases, " +
                "however upon request we offer a wide variety of color options; available from 6300K ultra cool white light to 2000K, " +
                "a color temperature that mimics the glow of soft candlelight adding that warm color needed in softer environments.<br><br>" +
                "<span style='color: #000000;'>Please contact customer service for more information.</span></span></p> </div>"
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
        lightingPositionDetails: function() {
            var self = this;
            var position = 'right';
            var content = "<div><p>When choosing a Top or Inside Perimeter LED lighting option and which will work best for your application,&nbsp;you must factor in the lighting environment you will be placing your shadow box display case into.</p>"+
                "<p><span style='color: #800000;'><strong>NOTE:</strong>&nbsp;The inside of the Shadow Box will appear brighter when a&nbsp;<span>WHITE or LIGHTER interior laminate</span>&nbsp;is selected. The darker the interior, the less the light will travel and spread.</span><br><br><br></p></div>";
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
        powerConnectionDetails: function() {
            var self = this;
            var position = 'right';
            var content = "<div id=\"lighting_tab_power_connection\" class=\"lighting-popup\" style=\"display: block;\">" +
                "<p><span style=\"font-size: medium;\"><strong>CHOOSE FROM 2 POWER CONNECTION OPTIONS</strong></span></p>" +
                "<p><strong><span style=\"text-decoration: underline; color: #000000;\">OPTION 1</span> - 12v Plug-In Adapter for Wall Outlet</strong><br>The 12v Plug-In Adapter can be plugged into any standard wall outlet.&nbsp;They come in Black or White, along with its wiring. The plug has a wire that is 5 feet to the power supply,&nbsp;then additional 3-4 feet depending on the adapter wattage capacity (see specs below for more information).</p>" +
                "<p><img src="+require.toUrl('Ziffity_ProductCustomizer/images/Connection_Adapters.jpg')+"></p>" +
                "<p><strong><span style=\"text-decoration: underline; color: #000000;\">OPTION 2</span> - 12v Driver for Hardwired Applications</strong><br>For this option a licensed electrician will be required to provide power to the display case.&nbsp;The benefit of this option is that the wires and power source (driver) can be concealed by the electrician&nbsp;(see specs below for more information).</p>" +
                "<p><img src="+require.toUrl('Ziffity_ProductCustomizer/images//Connection_Hardwired.jpg')+"></p>";

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
        cordColorDetails: function() {
            var self = this;
            var position = 'right';
            var content = "<div id=\"lighting_tab_cord_colors\" class=\"lighting-popup\" style=\"display: block;\">" +
                "<p><span>If you selected the&nbsp;<strong>Plug</strong>&nbsp;option for your power connection, the cord is available in Black or White.</span></p>" +
                "<p><span><img src="+require.toUrl('Ziffity_ProductCustomizer/images/Cord_Colors.jpg')+"><br>" +
                "<br>If you selected <strong>Hardwired</strong> for your power connection, the wire comes in White only.</span></p></div>";

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
        InteriorTopDetails: function() {
            var self = this;
            var position = 'right';
            var content = "<p><span><span><strong>INTERIOR TOP LIT ONLY</strong></span></span><br>For this option we place an LED strip light on the top side of the interior display case. The light will shine downward, highlighting the contents inside.<br><br><span style=\"color: #800000;\"><strong>NOTE:</strong>&nbsp;The inside of the Shadow Box will appear brighter when a&nbsp;WHITE or LIGHTER interior laminate&nbsp;is selected. The darker the interior, the less the light will travel and spread.</span></p>" +
                "<p></p>" +
                "<p><img alt=\"FLEXIBLE LED LIGHTING STRIP | 50,000 HOUR LIFE SPAN | CONCEALED LED LIGHTING\" src="+require.toUrl('Ziffity_ProductCustomizer/images/Static_LED_1.jpg')+"></p>";

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
        InteriorPerimeterDetails: function() {
            var self = this;
            var position = 'right';
            var content = "<div id=\"lighting_tab_position_intetior_perimeter_lit\" class=\"lighting-popup\" style=\"display: block;\">\n" +
                "<p><strong>INTERIOR PERIMETER LIT - ALL 4 SIDES<br></strong>For this option we place an LED strip light along the entire Perimeter of the interior display case.&nbsp;The light will shine from all 4 sides of the interior case,&nbsp;bringing more light intensity to the contents being displayed inside.</p>" +
                "<p><span style=\"color: #800000;\"><strong>NOTE:</strong>&nbsp;The inside of the Shadow Box will appear brighter when a&nbsp;WHITE or LIGHTER interior laminate&nbsp;is selected. The darker the interior, the less the light will travel and spread.</span></p>" +
                "<p><span style=\"color: #800000;\"><img alt=\"FLEXIBLE LED LIGHTING STRIP | 50,000 HOUR LIFESPAN | CONCEALED PERIMETER LIGTHING\" src="+require.toUrl('Ziffity_ProductCustomizer/images/Static_LED_Perimeter.jpg')+"></span></p></div>";

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
