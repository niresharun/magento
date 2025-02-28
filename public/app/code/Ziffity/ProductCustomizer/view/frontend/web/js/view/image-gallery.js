define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'domReady!'
], function ($, _, ko, Component,performAjax) {
    'use strict';
    return Component.extend(
        {
            defaults: {
                canvasData: ko.observable(false),
                editmode: ko.observable(false),
                exitCustomization: ko.observable(),
                template: 'Ziffity_ProductCustomizer/image-gallery',
                mainImage: ko.observable(""),
                imagesArray: ko.observableArray([]),
                isFotoramaLoaded: false,
                oldCanvasData: ko.observable(),
                imports: {
                    editmode: '${ $.provider }:editmode',
                    exitCustomization: '${ $.provider }:exitCustomization',
                    canvasData: '${ $.provider }:options.additional_data.canvasData'
                },
                listens: {
                    '${ $.provider }:options.additional_data.canvasData':'addCanvasImage'
                },
                exports: {
                    oldCanvasData: '${ $.provider }:oldCanvasData',
                }
            },
            initialize: function () {
                this._super();
                let self = this;
                self.editmode.subscribe(function(value){
                    if (value){
                        let fotoramaInstance = $('[data-gallery-role="gallery"]').data('fotorama');
                        if (fotoramaInstance!==undefined){
                            fotoramaInstance.destroy();
                            let element = $("#fotorama-gallery");
                            // Trigger custom event on the Fotorama element
                            element.trigger('fotorama:destroyed');
                            element.trigger('contentUpdated');
                            self.isFotoramaLoaded = false;
                        }
                    }
                    if(!value){
                        self.addCanvasImage(self.canvasData());
                    }
                });
                self.editmode.subscribe(function(value){
                    if (value){
                        self.oldCanvasData(self.canvasData());
                        let fotoramaInstance = $('[data-gallery-role="gallery"]').data('fotorama');
                        if (fotoramaInstance!==undefined){
                            fotoramaInstance.destroy();
                            let element = $("#fotorama-gallery");
                            // Trigger custom event on the Fotorama element
                            element.trigger('fotorama:destroyed');
                            element.trigger('contentUpdated');
                            self.isFotoramaLoaded = false;
                        }
                    }
                    if(!value){
                        self.addCanvasImage(self.canvasData());
                    }
                }, self);
                return this;
            },
            addCanvasImage:function(value){
                let self = this;
                if (!self.editmode() && !self.isFotoramaLoaded &&
                    $('[data-gallery-role="gallery-placeholder"]').data('gallery') === undefined) {
                    let data = {};
                    data.canvas = self.exitCustomization() ? self.oldCanvasData(): value;
                    data.gallery_data = window.customizerConfig.gallery_data;
                    data.magnifier = window.customizerConfig.magnifier;
                    data.gallery_options = window.customizerConfig.gallery_options;
                    data.fullscreen_options = window.customizerConfig.fullscreen_options;
                    data.breakpoints = window.customizerConfig.breakpoints;
                    data.sku = window.customizerConfig.productSku;
                    self.isFotoramaLoaded = true;
                    //TODO: Have to add the image to the fotorama from js itself and not by using the ajax later.
                    let ajax = performAjax.performAjaxOperation('customizer/option/getGalleryData/', 'POST', data);
                    ajax.done(function (response) {
                        let element = $("#fotorama-gallery");
                        element.append(response.image);
                        element.append(response.video);
                        element.trigger('contentUpdated');
                        $('body').trigger('processStop');
                        $("[data-gallery-role=gallery-placeholder]").on('fotorama:ready', function() {
                            jQuery(".fotorama__wrap--toggle-arrows").mouseover();
                            setTimeout(function() {
                                jQuery(".fotorama__wrap--toggle-arrows").mouseout();
                            });
                        });
                    });
                }
            }
        });
});
