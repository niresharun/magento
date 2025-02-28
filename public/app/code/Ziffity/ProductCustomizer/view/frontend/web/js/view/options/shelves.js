define([
    'jquery',
    'underscore',
    'ko',
    'Fraction',
    'uiRegistry',
    'Ziffity_ProductCustomizer/js/model/customizer-helper',
    'Ziffity_ProductCustomizer/js/view/options/abstract-option',
    'Ziffity_ProductCustomizer/js/model/step-navigator',
    'Magento_Catalog/js/price-utils',
    'Ziffity_ProductCustomizer/js/model/customizer-data-resolver',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'text!Ziffity_ProductCustomizer/template/optioninfo/shelves-info.html',
], function ($, _, ko, Fraction, registry, customizerHelper, AbstractOption, stepNavigator, priceUtils, customizerDataResolver, performAjax, info) {
    'use strict';

    return AbstractOption.extend({
        defaults: {
            isActive: ko.observable(false),
            isVisible:ko.observable(false),
            productSku:window.customizerConfig.productSku,
            // position: parseInt(window.customizerConfig.options.mat.position),
            index:2,
            reveals: ko.observableArray([]),
            revealSelected: ko.observable(),
            availableShelvesQty: ko.observableArray(),
            availableShelvesThickness: ko.observableArray(),
            selectedShelfQty: ko.observable(),
            shelvesQtyImage: ko.observable(),
            selectedShelfThickness: ko.observable(),
            frameWidth: ko.observable(),
            shelfThickness: ko.observable(),
            depth: '',
            // availableMats:ko.observableArray(),
            // mats: matTypes.mats,
            // matWide: ko.observableArray([]),
            // matWideSelected: ko.observable(),
            // matHigh: ko.observableArray([]),
            showReveal: false,
            // matHighSelected: ko.observable(),
            interiorDepth: ko.observable(false),
            position: 65,

            template: 'Ziffity_ProductCustomizer/options/shelves',
            exports: {
                selectedShelfQty: '${ $.provider }:options.shelves.shelves_qty',
                selectedShelfThickness: '${ $.provider }:options.shelves.shelves_thickness',
                interiorDepth: '${ $.provider }:options.size.interior_depth'
            },
            imports: {
                options: '${ $.provider }:options',
                shelfThickness: '${ $.provider }:options.size.thickness',
                // selectedWidthInteger: '${ $.provider }:options.size.width.integer',
                // selectedWidthFractional: '${ $.provider }:options.size.width.tenth',
                // selectedHeightInteger: '${ $.provider }:options.size.height.integer',
                // selectedHeightFractional: '${ $.provider }:options.size.height.tenth',
            },
            listens: {
                '${ $.provider }:editmode': 'updateSelection'
            },
        },
        initSelection: function() {
            var self = this;
            let shelves = customizerDataResolver.resolveShelvesSelection();
            shelves['shelves_qty'] != undefined ? self.selectedShelfQty(shelves['shelves_qty']): '';
            shelves['shelves_thickness'] != undefined ? self.selectedShelfThickness(shelves['shelves_thickness']): '';
        },
        updateSelection: function(value) {
            if(!value) {
                this.initSelection();
            }
        },
        initialize: function () {
            this._super();
            let self = this;
            self.interiorDepth(true);
            stepNavigator.registerStep(
                'Shelves',
                'shelves',
                this.isActive,
                this.isVisible,
                self.position,
                self.sortOrder,
                false,
                false,
                info
            );
            self.getShelvesThickness();
            this.isActive.subscribe(function (value) {
                if (value) {
                    self.loadShelvesData(self);
                    self.depth = self.changeLabelToInches(self.shelfThickness());
                }
            });
            this.selectedShelfQty.subscribe(function (value) {
                switch (value){
                    case 0:
                        self.shelvesQtyImage(null);
                        break;
                    case 1:
                        self.shelvesQtyImage( require.toUrl('Ziffity_ProductCustomizer/images/shelves/1_Shelf.jpg'));
                        break;
                    case 2:
                        self.shelvesQtyImage( require.toUrl('Ziffity_ProductCustomizer/images/shelves/2_Shelves.jpg'));
                        break;
                    case 3:
                        self.shelvesQtyImage( require.toUrl('Ziffity_ProductCustomizer/images/shelves/3_Shelves.jpg'));
                        break;
                    case 4:
                        self.shelvesQtyImage( require.toUrl('Ziffity_ProductCustomizer/images/shelves/4_Shelves.jpg'));
                        break;
                    case 5:
                        self.shelvesQtyImage( require.toUrl('Ziffity_ProductCustomizer/images/shelves/5_Shelves.jpg'));
                        break;
                    case 6:
                        self.shelvesQtyImage( require.toUrl('Ziffity_ProductCustomizer/images/shelves/6_Shelves.jpg'));
                        break;
                    case 'default':
                        self.shelvesQtyImage(null);
                        break;
                }
            });
            self.initSelection();
        },
        getShelvesQty: function() {
            var self = this;
            self.availableShelvesQty.removeAll();
            var shelvesQty = window.customizerConfig.options.shelves.available_shelves_qty;
            shelvesQty.forEach(function (element,index){
                self.availableShelvesQty.push(element);
            });
            return self.availableShelvesQty();
        },
        getShelvesThickness: function() {
            var self = this;
            self.availableShelvesThickness.removeAll();
            var shelvesThickness = window.customizerConfig.options.shelves.available_shelves_thickness;
            self.availableShelvesThickness()
            shelvesThickness.forEach(function (element,index){
                self.availableShelvesThickness.push(element);
            }, self);
            return self.availableShelvesThickness();
        },
        loadShelvesData: function(self) {
            let data = {};
            let result;
            data.sku = self.productSku;
            data.option = 'shelves';
            data.options = self.options;
            result = performAjax.performAjaxOperation('customizer/option/getValues/','POST',data);
            result.done(function(response){
                self.frameWidth(self.changeLabelToInches(new Fraction(response.options.shelves.frame_width).toFraction(true)));
                $('body').trigger('processStop');
            });
        },
        changeLabelToInches:function (string) {
            return string+'"';
        },
        shelvesQtyDetails:function () {
            var self = this;
            var position = 'right';
            var content = "<div id=\"shelves_tab_number_of_shelves_popup\" class=\"shelves-popup\" style=\"display: block;\">" +
                "<ul><li>Shadow Boxes 48\" in width or less, receive metal support clips</li>" +
                "<li>Shelf holes are aligned on the interior sides for shelf height adjustment</li>" +
                "<li>Shelf holes are spaced out 2 1/2\" apart down the interior sides</li>" +
                "<li>(4) shelf clips per shelf are included (sized to fit the pre-drilled shelf holes)</li></ul>" +
                "<p><strong>PLEASE NOTE: Shadow Boxes that have a width larger than 48\", include standards &amp; bracket hardware.</strong>" +
                "<br><span style=\"color: #800000;\">" +
                "<br>*See additional images under your Display Frame design, for more visual clarification</span></p></div>";

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
        thicknessDetails:function() {
            var self = this;
            var position = 'right';
            var content = "<div id=\"shelves_tab_thickness_popup\" class=\"shelves-popup\">" +
                "<p><span style=\"font-size: medium;\"><strong>Annealed Glass Shelves (1/4\" Thick)</strong></span></p>" +
                "<ul><li>Most display cases include 1/4\" thick plate glass with buffed edges, ideal for lightweight items.</li></ul>" +
                "<p><span style=\"font-size: medium;\"><strong>Annealed Glass Shelves (3/8\" Thick)</strong></span></p>" +
                "<ul><li>The thicker glass with buffed edges is perfect for heavier objects being placed inside the display case, while presenting an impressive look for your interior environment.</li>" +
                "<li>The 3/8\" thick glass is highly recommended for shelves wider than 36\"</li></ul>" +
                "<p><span style=\"color: #800000;\"><strong>PLEASE NOTE:</strong>&nbsp;Tempered Glass, along with additional / replacement shelves are available upon request. " +
                "Please contact customer service for more information.</span></p></div>";

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
