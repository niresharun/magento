define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'Magento_Catalog/js/price-utils',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'Ziffity_ProductCustomizer/js/pixel-converter-utils',
    'Ziffity_ProductCustomizer/js/perform-ajax',
    'Ziffity_ProductCustomizer/js/view/price-summary',
    'PsColor',
    'Fraction',
    'FabricJs5',
    'Modernizr',
], function ($, _, ko, Component, priceUtils, domObserver,pixelConverter,performAjax, priceSummary, ps, Fraction) {
    'use strict';

    return Component.extend({
        productSku: window.customizerConfig.productSku,
        size_type: window.customizerConfig.size_option.size_type,
        defaults: {
            canvasLoaded:false,
            totals:ko.observable(),
            editmode:ko.observable(false),
            isActive: ko.observable(false),
            isVisible:ko.observable(false),
            openingActivated:ko.observable(0),
            template: 'Ziffity_ProductCustomizer/image',
            frameData:ko.observableArray([{width:0, height:0, widtht:0, heighth:0}]),
            openingDataArray:ko.observableArray([]),
            overallSize:ko.observable(''),
            toggleUploadImage: ko.observable(),
            shadowBox: ko.observable(),
            //header defaults
            headerImageDataArray:ko.observableArray([]),
            headerTextDataArray:ko.observableArray([]),
            selectedBackgroundColor:ko.observable(null),
            headerLabelType:window.customizerConfig.headerLabel,
            headerLabelStatus:window.customizerConfig.headerLabelStatus,
            headerPosition:ko.observable('top'),
            labelPosition:ko.observable('top'),
            headerDimensions:ko.observable({width:0,height:15}),
            labelDimensions:ko.observable({width:0,height:15}),
            //label defaults
            labelImageDataArray:ko.observableArray([]),
            labelTextDataArray:ko.observableArray([]),
            canvasData: ko.observable(null),
            objectSize:ko.observable(null),
            imagezoom:ko.observable(false),
            imgUploaded: ko.observable(),
            currentCanvasVersion: 0,
            graphics:ko.observableArray(),
            exports: {
                selection: '${ $.provider }:options.image',
                canvasData: '${ $.provider }:options.additional_data.canvasData',
                objectSize: '${ $.provider }:image.objectSize',
                overallSize: '${ $.provider }:image.overallSize',
                // totals: '${ $.provider }:options.additional_data.subtotal'
            },
            listens: {
                '${ $.provider }:options.openings.toggle_shadow_box': 'hideShow',
                '${ $.provider }:options.frame.active_item': 'drawImage',
                '${ $.provider }:options.size': 'drawImage',
                '${ $.provider }:editmode': 'drawImage',
                //'customizer.option-renderer.Size': 'updateSelection',
                // 'customizer.option-renderer.Size:selectedWidthFractional': 'updateSelection',
                // 'customizer.option-renderer.Size:selectedHeightInteger': 'updateSelection',
                // 'customizer.option-renderer.Size:selectedHeightFractional': 'updateSelection',
                '${ $.provider }:options.mat.sizes':'drawImage',
                // '${ $.provider }:options.mat.sizes.top.tenth': 'updateSelection',
                // '${ $.provider }:options.mat.sizes.reveal': 'updateSelection',
                '${ $.provider }:options.laminate_finish.active_items': 'drawImage',
                '${ $.provider }:options.mat.active_items.top_mat':'drawImage',
                '${ $.provider }:options.mat.active_items.middle_mat':'drawImage',
                '${ $.provider }:options.mat.active_items.bottom_mat':'drawImage',
                '${ $.provider }:options.dryerase_board.active_item': 'drawImage',
                '${ $.provider }:options.fabric.active_item': 'drawImage',
                '${ $.provider }:options.letter_board.active_item': 'drawImage',
                '${ $.provider }:options.backing_board.active_item': 'drawImage',
                '${ $.provider }:options.chalk_board.active_item': 'drawImage',
                '${ $.provider }:options.cork_board.active_item': 'drawImage',
                '${ $.provider }:img_uploaded' : 'drawImage',
                '${ $.provider }:img_upload': 'drawImage',
                //'${ $.provider }:options': 'updateSelection'
                //here listens is used to track the overallchanges of that property.
                //when that property changes to a new value , it runs the method which receives the newValue as argument.
                // 'customizer.option-renderer.Frame:productSelection': 'frameOptionSelected',
                // 'customizer.option-renderer.Size:selectedWidthInteger': 'widthIntegerSelected',
                // 'customizer.option-renderer.Size:selectedWidthFractional': 'widthFractionalSelected',
                // 'customizer.option-renderer.Size:selectedHeightInteger': 'selectedHeightInteger',
                // 'customizer.option-renderer.Size:selectedHeightFractional': 'selectedHeightFractional',
                '${ $.provider }:options.openings.openingDataArray': 'updateOpening',
                //header
                '${ $.provider }:options.header.text_header.selectedBackgroundColor': 'changeSelectedBackgroundColor',
                '${ $.provider }:options.header.text_header.textHeaderArray': 'addSubscription',
                '${ $.provider }:options.header.image_header.imageDataArray': 'updatedHeaderImage',
                //label
                '${ $.provider }:options.label.text_label.textLabelArray': 'addSubscription',
                '${ $.provider }:options.label.image_label.imageDataArray': 'updatedLabelImage',
            },
            imports: {
                editmode: '${ $.provider }:editmode',
                shadowBox: '${ $.provider }:options.openings.toggle_shadow_box',
                toggleUploadImage: '${ $.provider }:options.openings.toggle_upload_img',
                //opening
                openingActivated:'${ $.provider }:options.openings.openingActivated',
                openingDataArray: '${ $.provider }:options.openings.openingDataArray',
                //header
                headerPosition:'${ $.provider }:options.header.headerPosition',
                headerDimensions:'${ $.provider }:options.header.headerDimensions',
                labelDimensions:'${ $.provider }:options.label.labelDimensions',
                headerImageDataArray:'${ $.provider }:options.header.headerImageDataArray',
                headerTextDataArray:'${ $.provider }:options.header.headerTextDataArray',
                selectedBackgroundColor:'${ $.provider }:options.header.text_header.selectedBackgroundColor',
                //label
                labelPosition:'${ $.provider }:options.label.labelPosition',
                labelImageDataArray:'${ $.provider }:options.label.image_label.imageDataArray',
                labelTextDataArray:'${ $.provider }:options.label.text_label.textLabelArray',
                options: '${ $.provider }:options',
                matCount: '${ $.provider }:options.mat.mat_count',
                imgUploaded: '${ $.provider }:img_uploaded',
                graphics: '${ $.provider }:img_upload',

            }
        },

        drawImage: function(){
            let self = this;
            if(self.canvasLoaded){
                self.redrawFabric();
            }
        },

        updateOpening:function(){
            if (this.canvasLoaded) {
                this.redrawFabric();
            }
        },
        addSubscription:function(value){
            let self = this;
            if (self.headerLabelType === 'header'){
                self.headerTextDataArray(value);
            }
            if (self.headerLabelType === 'label'){
                self.labelTextDataArray(value);
            }
            _.each(value,function(item){
                if (!item.inputText.getSubscriptionsCount()) {
                    item.inputText.subscribe(function () {
                        self.redrawFabric();
                        self.recalculatePrice();

                    });
                }
                if(!item.selectedFont.getSubscriptionsCount()) {
                    item.selectedFont.subscribe(function () {
                        self.redrawFabric();
                    });
                }
                if (!item.bold.getSubscriptionsCount()) {
                    item.bold.subscribe(function () {
                        self.redrawFabric();
                    });
                }
                if (!item.italic.getSubscriptionsCount()) {
                    item.italic.subscribe(function () {
                        self.redrawFabric();
                    });
                }
                if (!item.underline.getSubscriptionsCount()) {
                    item.underline.subscribe(function () {
                        self.redrawFabric();
                    });
                }
                if (!item.selectedAlignment.getSubscriptionsCount()) {
                    item.selectedAlignment.subscribe(function () {
                        self.redrawFabric();
                    });
                }
                if (!item.selectedColor.getSubscriptionsCount()) {
                    item.selectedColor.subscribe(function () {
                        self.redrawFabric();
                    });
                }
                if (!item.font_size_points.getSubscriptionsCount()) {
                    item.font_size_points.subscribe(function () {
                        self.redrawFabric();
                    });
                }
            });
            if (self.canvasLoaded){
                self.redrawFabric();
            }
        },
        updatedLabelImage:function(value){
            let self = this;
            this.labelImageDataArray(value);
            _.each(value,function(item){
                if (!item.fileData.getSubscriptionsCount()) {
                    item.fileData.subscribe(function () {
                        self.redrawFabric();
                    });
                }
            });
            if (self.canvasLoaded) {
                this.redrawFabric();
            }
        },
        updatedHeaderImage:function(value){
            let self = this;
            this.headerImageDataArray(value);
            _.each(value,function(item){
                if (!item.fileData.getSubscriptionsCount()) {
                    item.fileData.subscribe(function () {
                        self.redrawFabric();
                        self.recalculatePrice();
                    });
                }
            });
            if (self.canvasLoaded){
                self.redrawFabric();
            }
        },
        buildOpeningData:function()
        {
            let self = this;
            let openingData = [];
            _.each(self.openingDataArray(),function(item){
                let data = {};
                data.img  = item.img();
                data.name = item.name();
                data.position = item.position();
                data.upload = item.upload();
                data.position_dev = item.position_dev();
                data.shape = item.shapeSelection() === 'rectangle' ? 'rect' : 'circle';
                data.size = item.size();
                openingData.push(data);
            });
            return openingData;
        },

        buildGraphicData:function()
        {
            let self = this;
            let graphicData = [];
            _.each(self.graphics(),function(item){
                let data = {};
                data.url  = item.url();
                data.name = item.name();
                graphicData.push(data);
            });
            return graphicData;
        },
        buildImageHeaderData:function(imageData){
            let data = [];
            //sample data to be built like this
            // "images":[
            //     {"url":"http://local.displayframes.com/media/mat/maaa.jpg",
            //     "width_inch":"2 11/16",
            //     "height_inch":"2 11/16","dev_top_inch":"0","dev_left_inch":"0"},
            //     {"url":"http://local.displayframes.com/media/mat/aaa.jpg",
            //     "width_inch":"2 11/16",
            //     "height_inch":"2 11/16","dev_top_inch":"0",
            //     "dev_left_inch":"3 11/16"}
            // ]
            _.each(imageData,function(item){
                let imageData = {};
                imageData.url = item.fileData();
                imageData.width_inch = item.width_inch;
                imageData.height_inch = item.height_inch;
                imageData.dev_top_inch = item.dev_top_inch;
                imageData.dev_left_inch = item.dev_left_inch;
                data.push(imageData);
            });
            return data;
        },

        buildTextHeaderData:function(textData){
            let data = [];
            //sample data to be built like this
            // "texts":[
            //     {"width_inch":"7 11/16",
            //     "height_inch":"2 5/8",
            //     "dev_left_inch":"3/16",
            //     "dev_top_inch":"3/4",
            //     "text":"header",
            //     "font":"Alegreya SC",
            //     "font_size_inch":2,
            //     "font_size_points":40,
            //     "text_color":"red",
            //     "text_align":"left",
            //     "font_style":{"bold":true,"italic":true,"underline":true}},
            // ],
            _.each(textData,function(item){
                let textData = {};
                textData.width_inch = item.width_inch;
                textData.height_inch = item.height_inch;
                textData.dev_top_inch = item.dev_top_inch;
                textData.dev_left_inch = item.dev_left_inch;
                textData.text = item.inputText;
                textData.font = item.selectedFont;
                textData.font_size_inch = item.font_size_inch();
                textData.font_size_points = item.font_size_points;

                textData.text_color = item.selectedColor;
                textData.text_align = item.selectedAlignment;
                textData.sizeObservable = item.sizeObservable;
                textData.font_style = {"bold":item.bold,"italic":item.italic,
                    "underline":item.underline};
                data.push(textData);
            });
            return data;
        },
        changeSelectedBackgroundColor:function(value){
            let self = this;
            this.selectedBackgroundColor(value);
            if (this.canvasLoaded) {
                self.redrawFabric();
            }
        },
        recalculatePrice:function (self){
            var self = this;
            let data = {};
            let result;
            data.options = self.options; //self.subPriceSumUp(self.pricing, self);
            data.sku = self.productSku;
            window.clearTimeout(self.timer);
            //var millisecBeforeRedirect = 10000;
            self.timer = window.setTimeout(function(){
                result = performAjax.performAjaxOperation('customizer/option/getSubtotal/','POST',data);
                $('body').trigger('processStop');
                result.done(function(response){
                    if(response!== undefined){
                        self.totals(response.subtotal);
                    }
                }, self);
            },1500);

        },
        rebuildData:function()
        {
            let selector = $("#image-customizer-container");
            let self = this;
            let defaultWidth = selector.width();
            let defaultHeight = $(window).height() - 292;//292px to accomodate header and footer
            let imagesData = [];
            let textData = [];
            let dimension;
            let position;

            let widthInteger = self.options.size.width.integer === undefined ? 0 : self.options.size.width.integer;
            let widthTenth = self.options.size.width.tenth === undefined ? 0 : self.options.size.width.tenth;
            let heightInteger = self.options.size.height.integer === undefined ? 0 : self.options.size.height.integer;
            let heightTenth = self.options.size.height.tenth === undefined ? 0 : self.options.size.height.tenth;

            let graphicWidth = self.getFullNumber(parseInt(widthInteger), (widthTenth));
            let graphicHeight = self.getFullNumber(parseInt(heightInteger), (heightTenth));

            let frameImg = self.options.frame.active_item.img_draw.src !== undefined ?
                self.options.frame.active_item.img_draw.src:
                require.toUrl('Ziffity_ProductCustomizer/images/graphic/default-frame.jpg')  ;
            let frameInch = self.getFullNumber(parseInt(self.options.frame.active_item.img_draw.height.integer),
                (parseInt(self.options.frame.active_item.img_draw.height.tenth)>0 ?
                    eval(self.options.frame.active_item.img_draw.height.tenth): 0))
            let matCount = self.matCount === undefined ? 0: self.matCount ;
            let matInch = self.matCount > 0 ? self.getFullNumber(parseInt(self.options.mat.sizes.top.integer), eval(self.options.mat.sizes.top.tenth)): 0;
            let reveal = self.matCount> 1 ? (self.options.mat.sizes.reveal !== undefined ? eval(self.options.mat.sizes.reveal): 0) : 0;
            let topMatImage = self.matCount > 0 ? (self.options.mat.active_items.top_mat.img_draw.src ?? '') : '';
            let middleMatImage = self.matCount > 1 ? (self.options.mat.active_items.middle_mat.img_draw.src ?? ''): '';
            let bottomMatImage = self.matCount > 2 ? (self.options.mat.active_items.bottom_mat.img_draw.src ?? ''): '';
            let topMatColor = self.matCount > 0 ? (self.options.mat.active_items.top_mat.color_layer ?? '') : '';
            let middleMatColor = self.matCount > 1 ? (self.options.mat.active_items.middle_mat.color_layer ?? ''): '';
            let bottomMatColor = self.matCount > 2 ? (self.options.mat.active_items.bottom_mat.color_layer ?? ''): '';
            let graphicImage = '';
            let boardStatus = 0;
            let openingStatus = 0;
            let openingUrl = '';
            let fabricStatus = self.options.fabric !== undefined;
            let fabricType = fabricStatus ? self.options.fabric.active_item.img_draw.type : null;
            let controlOverlay = fabricStatus ? self.options.fabric.active_item.img_draw.color : null;
            let openingGraphics = self.graphics;
            if(self.options.letter_board !== undefined){
                boardStatus =1;
                graphicImage = self.options.letter_board.active_item.img_draw.src !== undefined ?
                    self.options.letter_board.active_item.img_draw.src : '';
            }
            if(self.options.dryerase_board !== undefined){
                boardStatus =1;
                graphicImage = self.options.dryerase_board.active_item.img_draw.src !== undefined ?
                    self.options.dryerase_board.active_item.img_draw.src: '';
            }
            if(self.options.backing_board !== undefined){
                boardStatus =1;
                graphicImage = self.options.backing_board.active_item.img_draw.src !== undefined ?
                    self.options.backing_board.active_item.img_draw.src: '';
            }
            if(self.options.cork_board !== undefined){
                boardStatus =1;
                graphicImage = ((self.options.cork_board.active_item.length > 0) && (self.options.cork_board.active_item.img_draw.src !== undefined)) ?
                    self.options.cork_board.active_item.img_draw.src: '';
            }
            if(self.options.chalk_board !== undefined){
                boardStatus =1;
                graphicImage = self.options.chalk_board.active_item.img_draw.src !== undefined ?
                    self.options.chalk_board.active_item.img_draw.src: '';
            }
            if(self.options.laminate_finish !== undefined){
                if(self.options.laminate_finish.active_items.laminate_interior !== undefined &&
                    self.options.laminate_finish.active_items.laminate_interior.img_draw.src !== undefined ){
                    boardStatus =1;
                    graphicImage = self.options.laminate_finish.active_items.laminate_interior.img_draw.src !== undefined ?
                        self.options.laminate_finish.active_items.laminate_interior.img_draw.src: '';
                }
            }
            if(self.options.fabric !== undefined){
                boardStatus =1;
                graphicImage = self.options.fabric.active_item.img_draw.src !== undefined ?
                    self.options.fabric.active_item.img_draw.src: '';
            }

            if(self.hasOwnProperty('graphics') && self.graphics().length === 1){
                openingStatus = self.graphics()[0].url() !== '' ? 1: 0;
                openingUrl = self.graphics()[0].url() !== '' ? self.graphics()[0].url: '';
            }

            if(self.openingDataArray().length === 1 && self.graphics().length != 0){
                graphicImage = self.openingDataArray()[0].img().url !== '' ? self.openingDataArray()[0].img().url: graphicImage;
                if(self.graphics().length > 0) {
                    openingStatus = self.graphics()[0].url() !== '' ? 1 : 0;
                    openingUrl = self.graphics()[0].url() !== '' ? self.graphics()[0].url() : '';
                }
            }

            if (self.headerLabelType === 'header'){
                imagesData = self.buildImageHeaderData(self.headerImageDataArray());
                textData = self.buildTextHeaderData(self.headerTextDataArray());
                dimension = self.headerDimensions();
                position = self.headerPosition();
            }
            if (self.headerLabelType === 'label'){
                imagesData = self.buildImageHeaderData(self.labelImageDataArray());
                textData = self.buildTextHeaderData(self.labelTextDataArray());
                dimension = self.labelDimensions();
                position = self.labelPosition();
            }
            return {
                "defaultCanvas":{'width':defaultWidth,'height':defaultHeight},
                "graphicImageDim":{'width':graphicWidth,'height':graphicHeight,
                    'img':require.toUrl('Ziffity_ProductCustomizer/images/graphic/default-bg.jpg')},
                "matDim":{'matCount': matCount,
                    'defaultImg': require.toUrl('Ziffity_ProductCustomizer/images/pattern/suede.jpg'),
                    'matTop':{'inches':matInch,'imgUrl':topMatImage, 'color': topMatColor},
                    "matReveal":{'inches':reveal,'middleImg':middleMatImage, 'middleColor': middleMatColor,
                        'bottomImg':bottomMatImage, 'bottomColor': bottomMatColor}
                },
                "frameDim":{'inches':frameInch,'imgUrl':frameImg},
                "headerLabel":{
                    'status':self.headerLabelStatus,'position':position,
                    'type':self.headerLabelType,'dim':dimension,
                    'headerData':{
                        "images":imagesData,
                        "texts":textData,
                        "bg_color_active":self.selectedBackgroundColor()
                    }
                },
                "openings":{
                    'status':self.openingActivated(),
                    "data":self.buildOpeningData()
                },
                "backingBoard":{'status':boardStatus,'imgUrl':graphicImage, 'defaultImg': require.toUrl('Ziffity_ProductCustomizer/images/graphic/default-bg.jpg')},
                "fabric":{
                    'status': fabricStatus,
                    'type': fabricType,
                    'colorOverlay': controlOverlay
                },
                "graphicUpload":{'status':openingStatus,'imgUrl':openingUrl},
                "imageZoom": self.imagezoom(),
                "graphics": self.graphics(),
            };
        },
        redrawFabric:function(){
            try {
                this.redrawCanvasFabric();
            }catch (e) {
                console.error(e);
            }
        },
        redrawCanvasFabric:function(){
            let self = this;
            let data = self.rebuildData();//custom code
            // var canvas = new fabric.Canvas("image-customizer");

            let canvas = self.canvas;
            let currentCanvasVersion = ++self.currentCanvasVersion;

            const renderFabric = {
                _state: $.Deferred(),
                _registrations: {},
                registerAsyncFunction: function(name) {
                    this._registrations[name] = false;
                },
                markCompleted: function (name) {
                    let areAllRegistrationsCompleted = true;
                    this._registrations[name] = true;
                    _.each(this._registrations, function(value, key) {
                        if (!value) {
                            areAllRegistrationsCompleted = false;
                        }
                    })
                    if (areAllRegistrationsCompleted) {
                        this._state.resolve();
                    }
                },
                registerSuccessCallback: function (callback) {
                    this._state.done(callback);
                }
            }

            canvas.getObjects().forEach(function(object) {
                object.set("clipPath", null); // Clear the clipping path for each object
                canvas.remove(object);
            });
            canvas.clear();
            canvas.renderAll();

            // Remove the canvas element from the DOM
            let canvasContainer = document.getElementById('image-customizer');
            var canvasElement = canvasContainer.getElementsByTagName("canvas")[0];
            if (canvasElement) {
                canvasElement.remove();
            }

            canvas.selection = false;
            canvas.controlsAboveOverlay = true;


            //Draw Default Canvas
            canvas.setDimensions({width: data.defaultCanvas.width, height: data.defaultCanvas.height});

            var defaultCanvas = new fabric.Rect({
                left: 0,
                top: 0,
                fill: 'transparent',
                width: data.defaultCanvas.width,
                height: data.defaultCanvas.height,
                selectable: false
            });

            canvas.add(defaultCanvas);

            //Overall Frame Dimesions
            var overallFrameInch = [];
            let matDim = 0;
            let matCount = data.matDim.matCount;
            let headerStatus = data.headerLabel.status;
            let headerHeight = 0;
            let fabricStatus = data.fabric.status;
            let openingCount = data.openings.data.length;

            if(openingCount > 1){
                matCount =1;
                headerStatus = 0;
                headerHeight = 0;
            }
            if(headerStatus) {
                headerHeight = data.headerLabel.dim.height;
            }
            var matRevealSize = data.matDim.matReveal.inches;
            switch(matCount) {
                case 2:
                    matDim = data.matDim.matTop.inches  + matRevealSize;
                    break;
                case 3:
                    matDim = data.matDim.matTop.inches + (matRevealSize*2);
                    break;
                case 1:
                    matDim = data.matDim.matTop.inches;
            }
            let headerGapDim = 0;
            let matMath = 2;
            if(headerStatus){
                matMath = 1;
                headerGapDim = 3;
            }
            let sizetype = self.size_type;
            if(sizetype === 'graphic') {
                overallFrameInch['width'] = data.graphicImageDim.width + matDim * 2 + data.frameDim.inches * 2;
                overallFrameInch['height'] = data.graphicImageDim.height + matDim * matMath + data.frameDim.inches * 2 + headerHeight + headerGapDim;
            } else {
                if(isNaN(matDim)) {
                    matDim = 0;
                }
                overallFrameInch['width'] = data.graphicImageDim.width  + data.frameDim.inches * 2;
                overallFrameInch['height'] = data.graphicImageDim.height + data.frameDim.inches * 2;
            }
            let frameDimPx = self.getUpdatedCanvasSize(defaultCanvas, overallFrameInch.width, overallFrameInch.height);
            canvas.setDimensions({width: frameDimPx.width, height: frameDimPx.height});

            //graphic Dimensions
            var graphicDim = self.getGraphicSize(overallFrameInch, frameDimPx, data.graphicImageDim.width, data.graphicImageDim.height);

            if(openingCount <= 1) {
                //Draw Graphic
                var graphicImg = new fabric.Rect({
                    fill: 'transparent',
                    width: graphicDim.width,
                    height: graphicDim.height,
                    selectable: false,
                    controlsAboveOverlay: false
                });
                var graphicShape = 'rect';
                if(openingCount === 1){
                    graphicShape = data.openings.data[0].shape;
                }
                if (graphicShape === 'circle') {
                    graphicImg.set({rx: graphicImg.width / 2, ry: graphicImg.height / 2});
                }
                canvas.add(graphicImg);
                if (headerStatus) {
                    graphicImg.centerH();
                } else {
                    graphicImg.centerV();
                    graphicImg.centerH();
                }
                canvas.renderAll();


                //Fill graphic with default Image
                var graphicImgUrl = data.graphicImageDim.img;
                var graphicImageStatus = data.backingBoard.status;
                var graphicImageUrl = data.backingBoard.imgUrl;
                if(graphicImageStatus && graphicImageUrl) {
                    graphicImgUrl = data.backingBoard.imgUrl;
                }
                if (graphicImgUrl) {
                    renderFabric.registerAsyncFunction('graphicDefaultImage');
                    fabric.util.loadImage(graphicImgUrl, function (img) {
                        if (currentCanvasVersion !== self.currentCanvasVersion) {
                            return;
                        }
                        graphicImg.set({
                            fill: new fabric.Pattern({source: img, repeat: 'repeat'}),
                            scaleX: graphicImg.scaleX,
                            scaleY: graphicImg.scaleY,
                            strokeWidth: 0
                        });
                        canvas.renderAll();

                        //Draw notice flag
                        self.drawPreviewText(canvas, graphicImg, graphicShape, data, self);
                        canvas.renderAll()

                        renderFabric.markCompleted('graphicDefaultImage');
                    });
                }
            }

            //Draw frame
            var frameDim = self.inchToPixelCalc(frameDimPx, overallFrameInch, data.frameDim.inches);
            var frameUrl = data.frameDim.imgUrl;

            if(frameUrl) {
                // fabric.util.loadImage(frameUrl, function (img) {
                // var frame = self.drawFrame(canvas, frameDim.height, frameUrl);
                // canvas.add(frame).renderAll();
                // fabric.Object.prototype.objectCaching = false;
                // canvas.bringToFront(graphicImg);
                // });
                // Create a Fabric.js canvas

                renderFabric.registerAsyncFunction('frame');
                fabric.Image.fromURL(frameUrl, function (img) {
                    if (currentCanvasVersion !== self.currentCanvasVersion) {
                        return;
                    }
                    let canvasContainer = canvas;
                    let width = canvasContainer.width;
                    let height = canvasContainer.height;

                    const frame_scale = frameDim.height;
                    img.scaleToHeight(frame_scale);

                    var scaledImage = new fabric.StaticCanvas();
                    scaledImage.add(img);
                    scaledImage.renderAll();

                    let framePattern = new fabric.Pattern({source: scaledImage.getElement(), repeat: 'repeat'});

                    let horizontalPathHeight = frameDim.height;
                    let horizontalPathWidth = width;

                    const topImage = new fabric.Polygon(
                        [
                            {x: 0, y: 0},
                            {x: horizontalPathWidth, y: 0},
                            {x: horizontalPathWidth - horizontalPathHeight, y: horizontalPathHeight},
                            {x: horizontalPathHeight, y: horizontalPathHeight}
                        ], {
                            fill: framePattern,
                            top: 0,
                            left: 0,
                            selectable: false,
                            selection: false,
                        }
                    );
                    canvas.add(topImage);

                    const bottomImage = fabric.util.object.clone(topImage);
                    bottomImage.set({
                        top: height,
                        left: width,
                        angle: 180,
                        selectable: false,
                        selection: false
                    });
                    canvas.add(bottomImage);

                    let verticalPathHeight = frameDim.height;
                    let verticalPathWidth = height;
                    const leftImage = new fabric.Polygon(
                        [
                            {x: 0, y: 0},
                            {x: verticalPathWidth, y: 0},
                            {x: verticalPathWidth - verticalPathHeight, y: verticalPathHeight},
                            {x: verticalPathHeight, y: verticalPathHeight}
                        ], {
                            fill: framePattern,
                            angle: -90,
                            top: height,
                            left: 0,
                            selectable: false,
                            selection: false,
                        }
                    );
                    canvas.add(leftImage);

                    const rightImage = fabric.util.object.clone(leftImage);
                    rightImage.set({
                        angle: 90,
                        left: width,
                        top: 0,
                        selectable: false,
                        selection: false,
                    });
                    canvas.add(rightImage);


                    canvas.renderAll();

                    renderFabric.markCompleted('frame');
                });
            }


            // Draw Opening Graphic
            if(openingCount>1) {
                if(isNaN(matDim)) {
                    matDim = 0;
                }
                var opentingMatDim = self.inchToPixelCalc(frameDimPx, overallFrameInch, matDim);
                var openingData = data.openings.data;
                var openingBounds = [];

                var openingRect = fabric.util.createClass(fabric.Rect, {
                    type: 'openingRect', // Set a custom type for the object

                    initialize: function (options) {
                        this.callSuper('initialize', options);
                        this.name = options.name || ''; // Add a custom property "name" with default value ''
                    }
                });

                //Draw graphic with opening count
                openingData.forEach((element, index) => {
                    var openingWidthDim = self.inchToPixelCalc(frameDimPx, overallFrameInch, element.size.width_inch);
                    var openingHeightDim = self.inchToPixelCalc(frameDimPx, overallFrameInch, element.size.height_inch);
                    var openingLefttDim = self.inchToPixelCalc(frameDimPx, overallFrameInch, element.position.left_inch);
                    var openingToptDim = self.inchToPixelCalc(frameDimPx, overallFrameInch, element.position.top_inch);
                    let openingLeft = openingLefttDim.height + frameDim.height;
                    let openingTop = openingToptDim.width + frameDim.height;
                    if(sizetype == 'graphic'){
                        openingLeft += opentingMatDim.width;
                        openingTop +=opentingMatDim.height;
                    }
                    var openingObj = new openingRect({
                        fill: '#CBA887',
                        width: openingWidthDim.width,
                        height: openingHeightDim.height,
                        left: openingLeft,
                        top: openingTop,
                        selectable: false,
                        name: element.name,
                        index: index,
                        absolutePositioned: true
                    });

                    canvas.add(openingObj);
                    if (element.shape === 'circle') {
                        openingObj.set({rx: openingObj.width / 2, ry: openingObj.height / 2});
                    }

                    openingBounds[element.name]= openingObj.getBoundingRect();
                    var imgUrl = element.img.url;
                    var uploadUrl = '';
                    if((self.graphics().length > 1) && (self.graphics().length === self.openingDataArray().length)){
                        uploadUrl = self.graphics()[index].url();
                    }
                    var objMovement = true;
                    if (!imgUrl || self.toggleUploadImage()) {
                        imgUrl = data.graphicImageDim.img;
                        // objMovement = false;
                    }
                    if (self.isImageUrl(imgUrl)) {
                        self.checkImageExists(imgUrl).then(function(exists) {
                            if (exists) {
                                imgUrl = imgUrl;
                            } else {
                                imgUrl = data.graphicImageDim.img;
                            }

                            fabric.util.loadImage(imgUrl, function(img) {
                                openingObj.set({
                                    fill: new fabric.Pattern({source: img, repeat: 'repeat'}),
                                    scaleX: openingObj.scaleX,
                                    scaleY: openingObj.scaleY,
                                    strokeWidth: 0
                                });
                                canvas.renderAll();
                            });

                        });
                    }
                    if(uploadUrl && !self.toggleUploadImage()) {
                        var openingImg = new fabric.Image();

                        // openingImg.setSrc(imgUrl, function () {
                        renderFabric.registerAsyncFunction(`openingImage${index}`);
                        fabric.Image.fromURL(uploadUrl, function (img) {
                            if (currentCanvasVersion !== self.currentCanvasVersion) {
                                return;
                            }

                            var openingClipPath;
                            var openingShape = element.shape;
                            if (openingShape === 'circle') {
                                var centerX = openingObj.left + openingObj.width / 2;
                                var centerY = openingObj.top + openingObj.height / 2;
                                var radiusX = openingObj.width / 2;
                                var radiusY = openingObj.height / 2;

                                openingClipPath = new fabric.Ellipse({
                                    left: centerX,
                                    top: centerY,
                                    rx: radiusX,
                                    ry: radiusY,
                                    originX: 'center',
                                    originY: 'center',
                                    absolutePositioned: true,
                                });
                            } else {
                                var width = openingObj.width;
                                var height = openingObj.height;
                                openingClipPath = new fabric.Rect({
                                    left: openingObj.left + width / 2,
                                    top: openingObj.top + height / 2,
                                    width: width,
                                    height: height,
                                    originX: 'center',
                                    originY: 'center',
                                    absolutePositioned: true,
                                });
                            }

                            img.set({
                                fill: '#opening',
                                hoverCursor: 'move',
                                selection: false,
                                selectable: objMovement,
                                lockMovementX: false,
                                lockMovementY: false,
                                hasControls: false,
                                name: element.name,
                                controlsAboveOverlay: false,
                                clipPath: openingClipPath
                            });
                            self.scaleImageToSlot(img, openingObj);
                            canvas.add(img);
                            img.bringToFront();
                            canvas.renderAll();

                            self.drawPreviewText(canvas, openingObj, element.shape, data, self);
                            renderFabric.markCompleted(`openingImage${index}`);
                        });
                        // }, {crossOrigin: 'anonymous'});

                        canvas.renderAll();
                    }
                    self.drawPreviewText(canvas, openingObj, element.shape, data, self);

                    canvas.renderAll();

                });

                canvas.on('object:moving', function (e) {
                    var openingMovObj = e.target;
                    if(openingMovObj.fill === '#opening'){
                        var openingObjBound = openingBounds[openingMovObj.get('name')];
                        var openingLeft = openingObjBound.left;
                        var openingRight = openingObjBound.left + openingObjBound.width;
                        var openingTop = openingObjBound.top;
                        var openingBottom = openingObjBound.top + openingObjBound.height;


                        if (openingMovObj.left >= openingLeft) {
                            openingMovObj.left = openingLeft;
                        }

                        if (openingMovObj.left + openingMovObj.width * openingMovObj.scaleX <= openingRight) {
                            openingMovObj.left = openingRight - openingMovObj.width * openingMovObj.scaleX;
                        }
                        if (openingMovObj.top >= openingTop) {
                            openingMovObj.top = openingTop;
                        }
                        if (openingMovObj.top + openingMovObj.height * openingMovObj.scaleY <= openingBottom) {
                            openingMovObj.top = openingBottom - openingMovObj.height * openingMovObj.scaleY;
                        }
                    }
                });
            }

            //Draw Header
            if(headerStatus) {
                var headerDim = self.inchToPixelCalc(frameDimPx, overallFrameInch, headerHeight);
                var headerWidthDim = self.inchToPixelCalc(frameDimPx, overallFrameInch, data.headerLabel.dim.width);
                var gapDim = self.inchToPixelCalc(frameDimPx, overallFrameInch, 1.5);
                var hgBuffer = gapDim.height;
                var headerType = data.headerLabel.type;
                var headerBgColor = 'transparent';
                if (headerType === 'header') {
                    headerBgColor = data.headerLabel.headerData.bg_color_active;
                }
                var header = new fabric.Rect({
                    fill: headerBgColor,
                    width: graphicImg.width,
                    height: headerDim.height,
                    selectable: false,
                    zIndex: 11
                });
                canvas.add(header);
                //TODO: Added the overall header size here
                self.overallSize(self.updatedImageSize(header.getBoundingRect()));
                header.centerH();
                canvas.renderAll();
                if (data.headerLabel.position === 'bottom') {
                    graphicImg.set({top: frameDim.height + hgBuffer});
                    header.set({top: frameDim.height + graphicImg.height + (hgBuffer * 2)});
                } else {
                    header.set({top: frameDim.height + hgBuffer});
                    graphicImg.set({top: frameDim.height + header.height + (hgBuffer * 2)});
                }
                canvas.renderAll();

                var headerboundLeft = header.left;
                var headerboundRight = header.left + header.width;
                var headerboundTop = header.top;
                var headerboundBottom = header.top + header.height;

                //Draw Header Elements

                // Draw image to header
                var imageData = data.headerLabel.headerData.images;
                let imgLeft = header.left;
                let imgTop = header.top;
                imageData.forEach((element, index) => {
                    if(element.url) {
                        renderFabric.registerAsyncFunction(`headerImage${index}`);
                        fabric.Image.fromURL(element.url, function (img) {
                            if (currentCanvasVersion !== self.currentCanvasVersion) {
                                return;
                            }
                            var headImgWidth = header.width -20;
                            var headImgHeight = header.height;

                            if(img.height < headImgHeight){
                                headImgHeight = img.height;
                                if(img.width < headImgWidth){
                                    headImgWidth = img.width;
                                }
                            }

                            if((typeof element.dev_left_inch() !== "string")&&(parseInt(element.dev_left_inch()) != 0)) {
                                if(!self.isMixedNumber(element.dev_left_inch())) {
                                    imgLeft = parseInt(element.dev_left_inch());
                                }
                            }

                            if ((typeof element.dev_top_inch() !== "string")&&(parseInt(element.dev_top_inch()) != 0)) {
                                if (!self.isMixedNumber(element.dev_top_inch())) {
                                    imgTop = parseInt(element.dev_top_inch());
                                }
                            }

                            if((typeof element.width_inch() !== "string")&&(parseInt(element.width_inch()) != 0)) {
                                if(!self.isMixedNumber(element.width_inch())) {
                                    if(header.width < parseInt(element.width_inch())){
                                        headImgHeight = header.width;
                                    } else {
                                        headImgWidth = parseInt(element.width_inch());
                                    }

                                }
                            }

                            if ((typeof element.height_inch() !== "string")&&(parseInt(element.height_inch()) != 0)) {
                                if (!self.isMixedNumber(element.height_inch())) {
                                    if(header.height < parseInt(element.height_inch())){
                                        headImgHeight = header.height;
                                    } else {
                                        headImgHeight = parseInt(element.height_inch());
                                    }

                                }
                            }

                            img.set({
                                left: imgLeft,
                                top: imgTop,
                                hasRotatingPoint: false,
                                lockRotation    : true,
                                lockSkewingX    : true,
                                lockSkewingY    : true,
                                lockScalingFlip : true,
                                flipX: false,
                                flipY: false,
                                selection:false,
                                selectable:true,
                                minScaleLimit: 0.01,
                                name: 'draggableText'
                            });

                            if (headImgHeight/headImgWidth > img.height/img.width) {
                                img.scaleToWidth(headImgWidth, false);
                                // img.scaleToHeight(headImgHeight);
                            } else {
                                // img.scaleToWidth(headImgWidth);
                                img.scaleToHeight(headImgHeight, false);
                            }

                            img.on('modified', function(event) {
                                // Access the current position of the rectangle
                                let rectPosition = img.getBoundingRect();
                                // Log the current position
                                element.height_inch(rectPosition.height);
                                element.width_inch(rectPosition.width);
                                element.dev_left_inch(rectPosition.left);
                                element.dev_top_inch(rectPosition.top);
                            });
                            canvas.add(img);
                            img.bringToFront();

                            var imgObjWidth = img.getScaledWidth() + img.left;
                            var imgObjHeight = img.getScaledHeight() + img.top;
                            var imgObjLeft = img.left;
                            var imgObjTop = img.top;

                            // // restric text length
                            if (imgObjWidth >= headerboundRight) {
                                img.set('left', headerboundRight - img.getScaledWidth());
                            }
                            if (imgObjLeft <= headerboundLeft) {
                                img.set('left', headerboundLeft);
                            }
                            if (imgObjHeight >= headerboundBottom) {
                                img.set('top', headerboundBottom - img.getScaledHeight());
                            }
                            if (imgObjTop <= headerboundTop) {
                                img.set('top', headerboundTop);
                            }

                            img.setCoords();
                            canvas.renderAll();
                            // Todo set contol for image to resize inside the header or label
                            img.setControlsVisibility({
                                tl: true,
                                tr: true,
                                br: true,
                                bl: true,
                                ml: false,
                                mt: false,
                                mr: false,
                                mb: false,
                                mtr: false
                            });

                            canvas.renderAll();

                            renderFabric.markCompleted(`headerImage${index}`);
                        });
                    }
                });


                //Draw text to header
                var textData = data.headerLabel.headerData.texts;
                let textLeft = header.left;
                let textTop = header.top;


                var DraggableText = fabric.util.createClass(fabric.Text, {
                    type: 'draggableText',
                    initialize: function(text, options) {
                        options || (options = {});
                        this.callSuper('initialize', text, options);
                    }
                });




                textData.forEach(element => {
                    var fontStyle = 'normal',fontWeight ='normal', textDecoration = false, textAlign='left', textColor = 'white';

                    if(element.text_color() && element.text_color() !== 'undefined') {
                        textColor = element.text_color();
                    }
                    if(element.font_style.bold() && element.font_style.bold() !== 'undefined'){
                        fontWeight = 'bold';
                    }
                    if(element.font_style.italic() && element.font_style.italic() !== 'undefined'){
                        fontStyle = 'italic';
                    }
                    if(element.font_style.underline() && element.font_style.underline() !== 'undefined'){
                        textDecoration = true;
                    }
                    if(element.text_align() && element.text_align() !== 'undefined'){
                        textAlign = element.text_align();
                    }

                    if ((typeof element.dev_left_inch() !== "string")&&(parseInt(element.dev_left_inch()) != 0)) {
                        if (!self.isMixedNumber(element.dev_left_inch())) {
                            textLeft = parseInt(element.dev_left_inch());
                        }
                    }


                    if ((typeof element.dev_top_inch() !== "string")&&(parseInt(element.dev_top_inch()) != 0)) {
                        if (!self.isMixedNumber(element.dev_top_inch())) {
                            textTop = parseInt(element.dev_top_inch());
                        }
                    }


                    let elementText = '';
                    if (element.text()){
                        elementText = element.text().trim();
                    }

                    let elementFontFm = '';
                    if (element.font()){
                        elementFontFm = element.font().trim();
                    }

                    var textObject = new DraggableText(elementText, {
                        left: textLeft,
                        top: textTop,
                        textAlign:textAlign,
                        fontFamily: elementFontFm,
                        fontSize: element.font_size_points(),
                        fill: textColor,
                        selection: true,
                        selectable: true,
                        fontWeight: fontWeight,
                        fontStyle: fontStyle,
                        textDecoration: textDecoration,
                        underline: textDecoration,
                        lineHeight: 1,
                        wrap: true
                    });
                    textObject.on('modified', function(event) {
                        // Access the current position of the rectangle
                        let rectPosition = textObject.getBoundingRect();

                        // Log the current position
                        element.height_inch(rectPosition.height);
                        element.width_inch(rectPosition.width);
                        element.dev_left_inch(rectPosition.left);
                        element.dev_top_inch(rectPosition.top);
                    });

                    canvas.add(textObject);

                    var textWidth = self.getObjectWidth(textObject);

                    while (textWidth > header.width || textObject.height > header.height) {
                        let fontSizepoints = element.font_size_points();
                        fontSizepoints -= 1;
                        element.font_size_points(fontSizepoints);
                        textObject.set('fontSize', element.font_size_points());
                        textWidth = self.getObjectWidth(textObject);
                    }
                    var textObjWidth = textWidth + textObject.left;
                    var textObjHeight = textObject.height + textObject.top;
                    var textObjLeft = textObject.left;
                    var textObjTop = textObject.top;

                    // // restric text length
                    if (textObjWidth >= headerboundRight) {
                        textObject.set('left', headerboundRight - textWidth);
                    }
                    if (textObjLeft <= headerboundLeft) {
                        textObject.set('left', headerboundLeft);
                    }
                    if (textObjHeight >= headerboundBottom) {
                        textObject.set('top', headerboundBottom - textObject.height);
                    }
                    if (textObjTop <= headerboundTop) {
                        textObject.set('top', headerboundTop);
                    }

                    // TODO: get Text Obejct size
                    let textObjSize = textObject.getBoundingRect();
                    element.sizeObservable(self.updatedImageSize(textObjSize));
                    textObject.setCoords();
                    canvas.renderAll();

                    // textTop = element.dev_top_inch();
                    // textLeft = element.dev_left_inch();
                    // canvas.renderAll();
                    textObject.setControlsVisibility({
                        mt: false, // top-middle
                        mb: false, // bottom-middle
                        ml: false, // left-middle
                        mr: false, // right-middle
                        bl: false, // bottom-left
                        br: false, // bottom-right
                        tl: false, // top-left
                        tr: false, // top-right
                        mtr: false // rotating point
                    });
                    textObject.bringToFront();
                    canvas.renderAll();
                });

                // Update the canvas
                canvas.renderAll();

                // Pending to work Resize image to rect
                canvas.off('object:scaling');
                canvas.on('object:scaling', function (event) {
                    const obj = event.target;
                    if (obj.type === 'image') {
                        obj.flipX = false;
                        obj.flipY = false;

                        if (obj.left < headerboundLeft) {
                            obj.scale((obj.left + obj.getScaledWidth() - headerboundLeft) / obj.width);
                            obj.left = headerboundLeft;
                        }
                        if (obj.left + obj.getScaledWidth()  > headerboundRight) {
                            obj.scale((headerboundRight - obj.left) / obj.width);
                            obj.left = headerboundRight - obj.getScaledWidth();
                        }
                        if (obj.top < headerboundTop) {
                            obj.scale((obj.top + obj.getScaledHeight() - headerboundTop) / obj.height);
                            obj.top = headerboundTop;
                        }
                        if (obj.top + obj.getScaledHeight()  > headerboundBottom) {
                            obj.scale((headerboundBottom - obj.top) / obj.height);
                            obj.top = headerboundBottom - obj.getScaledHeight();
                        }
                    }

                });

                canvas.off('object:moving');
                canvas.on('object:moving', function(e) {
                    var obj = e.target;
                    // Check if the object being moved is the text object
                    if (obj.type === 'draggableText' || (obj instanceof fabric.Image && obj.fill !='#CBA887')) {

                        if (obj.left <= headerboundLeft) {
                            obj.set({
                                left: headerboundLeft
                            });
                        }
                        var objWidth = obj.width;
                        if(obj.type === 'draggableText') {
                            objWidth= self.getObjectWidth(obj);
                        }
                        if(obj instanceof fabric.Image){
                            objWidth= obj.getScaledWidth();
                        }
                        if (obj.left + objWidth  >= headerboundRight) {
                            obj.set({
                                left: headerboundRight - objWidth
                            });
                        }
                        if (obj.top <= headerboundTop) {
                            obj.set({
                                top: headerboundTop
                            });

                        }
                        var objHeight = obj.height;
                        if(obj instanceof fabric.Image){
                            objHeight= obj.getScaledHeight();
                        }
                        if (obj.top + objHeight  >= headerboundBottom) {
                            obj.set({
                                top: headerboundBottom - objHeight
                            });
                        }
                    }
                });
            }


            //Draw Mat
            var matRevealDim = self.inchToPixelCalc(frameDimPx, overallFrameInch, data.matDim.matReveal.inches);
            if(openingCount > 1) {
                matCount = 1;
            }
            switch(matCount) {
                case 2:
                    renderFabric.registerAsyncFunction('mat');
                    let topMatColor = data.matDim.matTop.color;
                    let topMatImg = data.matDim.matTop.imgUrl;
                    if (self.isImageUrl(topMatImg)) {
                        self.checkImageExists(topMatImg).then(function(exists) {
                            if (!exists) {
                                imgUrl = data.backingBoard.defaultImg;
                            }
                            fabric.Image.fromURL(topMatImg, function (img) {
                                if (topMatColor) {
                                    let colorFilter = new fabric.Image.filters.BlendColor({
                                        color: topMatColor
                                    });
                                    img.filters.push(colorFilter);
                                    img.applyFilters();
                                }
                                defaultCanvas.set(
                                    "fill",
                                    new fabric.Pattern({source: img.getElement(), repeat: 'repeat'})
                                );

                                canvas.renderAll();
                                renderFabric.markCompleted('mat');
                            }, null, { crossOrigin: 'Anonymous' });
                        });
                    } else if(!topMatImg && topMatColor){
                        defaultCanvas.set(
                            "fill",
                            topMatColor
                        );
                        renderFabric.markCompleted('mat');
                    } else {
                        defaultCanvas.set(
                            "fill", '#0d0d0d'
                        );
                        renderFabric.markCompleted('mat');
                    }
                    if(!headerStatus) {
                        var mat2 = new fabric.Rect({
                            fill: 'transparent',
                            width: graphicImg.width + matRevealDim.height*2,
                            height: graphicImg.height + matRevealDim.height*2,
                            selectable: false
                        });
                        canvas.add(mat2);
                        if (graphicShape === 'circle') {
                            mat2.set({rx: mat2.width / 2, ry: mat2.height / 2});
                        }
                        canvas.renderAll();
                        mat2.centerV();
                        mat2.centerH();
                        renderFabric.registerAsyncFunction('reveal1');
                        let middleColor = data.matDim.matReveal.middleColor;
                        let middleImg = data.matDim.matReveal.middleImg;
                        if (self.isImageUrl(middleImg)) {
                            self.checkImageExists(middleImg).then(function(exists) {
                                if (!exists) {
                                    imgUrl = data.backingBoard.defaultImg;
                                }
                                fabric.Image.fromURL(middleImg, function (img) {
                                    if (middleColor) {
                                        let colorFilter = new fabric.Image.filters.BlendColor({
                                            color: middleColor
                                        });
                                        img.filters.push(colorFilter);
                                        img.applyFilters();
                                    }
                                    mat2.set(
                                        "fill",
                                        new fabric.Pattern({source: img.getElement(), repeat: 'repeat'})
                                    );

                                    canvas.renderAll();
                                    renderFabric.markCompleted('reveal1');
                                }, null, { crossOrigin: 'Anonymous' });
                            });
                        } else if(!middleImg && middleColor){
                            mat2.set(
                                "fill",
                                middleColor
                            );
                            renderFabric.markCompleted('reveal1');
                        } else {
                            mat2.set(
                                "fill", '#0d0d0d'
                            );
                            renderFabric.markCompleted('reveal1');
                        }
                        canvas.bringToFront(graphicImg);
                    }
                    break;
                case 3:
                    renderFabric.registerAsyncFunction('mat');
                    let topColor = data.matDim.matTop.color;
                    let topImg = data.matDim.matTop.imgUrl;
                    if (self.isImageUrl(topImg)) {
                        self.checkImageExists(topImg).then(function(exists) {
                            if (!exists) {
                                imgUrl = data.backingBoard.defaultImg;
                            }
                            fabric.Image.fromURL(topImg, function (img) {
                                if (topColor) {
                                    let colorFilter = new fabric.Image.filters.BlendColor({
                                        color: topColor
                                    });
                                    img.filters.push(colorFilter);
                                    img.applyFilters();
                                }
                                defaultCanvas.set(
                                    "fill",
                                    new fabric.Pattern({source: img.getElement(), repeat: 'repeat'})
                                );

                                canvas.renderAll();
                                renderFabric.markCompleted('mat');
                            }, null, { crossOrigin: 'Anonymous' });
                        });
                    } else if(!topImg && topColor){
                        defaultCanvas.set(
                            "fill",
                            topColor
                        );
                        renderFabric.markCompleted('mat');
                    } else {
                        defaultCanvas.set(
                            "fill", '#0d0d0d'
                        );
                        renderFabric.markCompleted('mat');
                    }
                    canvas.renderAll();
                    if(!headerStatus) {
                        var mat3 = new fabric.Rect({
                            fill: 'transparent',
                            width: graphicImg.width + matRevealDim.height * 4,
                            height: graphicImg.height + matRevealDim.height * 4,
                            selectable: false
                        });
                        canvas.add(mat3);
                        if (graphicShape === 'circle') {
                            mat3.set({rx: mat3.width / 2, ry: mat3.height / 2});
                        }
                        canvas.renderAll();
                        renderFabric.registerAsyncFunction('reveal2');
                        let middleColor = data.matDim.matReveal.middleColor;
                        let middleImg = data.matDim.matReveal.middleImg;
                        if (self.isImageUrl(middleImg)) {
                            self.checkImageExists(middleImg).then(function(exists) {
                                if (!exists) {
                                    imgUrl = data.backingBoard.defaultImg;
                                }
                                fabric.Image.fromURL(middleImg, function (img) {
                                    if (middleColor) {
                                        let colorFilter = new fabric.Image.filters.BlendColor({
                                            color: middleColor
                                        });
                                        img.filters.push(colorFilter);
                                        img.applyFilters();
                                    }
                                    mat3.set(
                                        "fill",
                                        new fabric.Pattern({source: img.getElement(), repeat: 'repeat'})
                                    );

                                    canvas.renderAll();
                                    renderFabric.markCompleted('reveal2');
                                }, null, { crossOrigin: 'Anonymous' });
                            });
                        } else if(!middleImg && middleColor){
                            mat3.set(
                                "fill",
                                middleColor
                            );
                            renderFabric.markCompleted('reveal2');
                        } else {
                            mat3.set(
                                "fill", '#0d0d0d'
                            );
                            renderFabric.markCompleted('reveal2');
                        }
                        canvas.renderAll();

                        mat3.centerV();
                        mat3.centerH();

                        var mat2 = new fabric.Rect({
                            fill: 'transparent',
                            width: graphicImg.width + matRevealDim.height*2,
                            height: graphicImg.height + matRevealDim.height*2,
                            selectable: false
                        });
                        canvas.add(mat2);
                        if (graphicShape === 'circle') {
                            mat2.set({rx: mat2.width / 2, ry: mat2.height / 2});
                        }
                        canvas.renderAll();
                        mat2.centerV();
                        mat2.centerH();
                        renderFabric.registerAsyncFunction('reveal1');
                        let bottomColor = data.matDim.matReveal.bottomColor;
                        let bottomImg = data.matDim.matReveal.bottomImg;
                        if (self.isImageUrl(bottomImg)) {
                            self.checkImageExists(bottomImg).then(function(exists) {
                                if (!exists) {
                                    imgUrl = data.backingBoard.defaultImg;
                                }
                                fabric.Image.fromURL(bottomImg, function (img) {
                                    if (bottomColor) {
                                        let colorFilter = new fabric.Image.filters.BlendColor({
                                            color: bottomColor
                                        });
                                        img.filters.push(colorFilter);
                                        img.applyFilters();
                                    }
                                    mat2.set(
                                        "fill",
                                        new fabric.Pattern({source: img.getElement(), repeat: 'repeat'})
                                    );

                                    canvas.renderAll();
                                    renderFabric.markCompleted('reveal1');
                                }, null, { crossOrigin: 'Anonymous' });
                            });
                        } else if(!bottomImg && bottomColor){
                            mat2.set(
                                "fill",
                                bottomColor
                            );
                            renderFabric.markCompleted('reveal1');
                        } else {
                            mat2.set(
                                "fill", '#0d0d0d'
                            );
                            renderFabric.markCompleted('reveal1');
                        }
                        canvas.bringToFront(graphicImg);
                    }
                    break;
                case 1:
                    var colorOverlay = null;
                    renderFabric.registerAsyncFunction('mat');
                    if (currentCanvasVersion !== self.currentCanvasVersion) {
                        return;
                    }
                    let colorData = data.matDim.matTop.color;
                    let imgUrl = data.matDim.matTop.imgUrl;

                    if (self.isImageUrl(imgUrl)) {
                        self.checkImageExists(imgUrl).then(function(exists) {
                            if (!exists) {
                                imgUrl = data.backingBoard.defaultImg;
                            }
                            fabric.Image.fromURL(imgUrl, function (img) {
                                if (data.matDim.matTop.color) {
                                    let colorFilter = new fabric.Image.filters.BlendColor({
                                        color: data.matDim.matTop.color
                                    });
                                    img.filters.push(colorFilter);
                                    img.applyFilters();
                                }
                                defaultCanvas.set(
                                    "fill",
                                    new fabric.Pattern({source: img.getElement(), repeat: 'repeat'})
                                );

                                canvas.renderAll();
                                renderFabric.markCompleted('mat');
                            }, null, { crossOrigin: 'Anonymous' });
                        });
                    } else if(!imgUrl && colorData){
                        defaultCanvas.set(
                            "fill",
                            colorData
                        );
                        renderFabric.markCompleted('mat');
                    }
                    canvas.renderAll();

            }


            //Draw Backing Board
            if(openingCount <= 1) {
                var backingBoadrUrl = data.backingBoard.imgUrl,
                    colorOverlay = null;
                if (data.fabric.status && data.fabric.type == 'pattern' && data.fabric.colorOverlay) {
                    colorOverlay = data.fabric.colorOverlay;
                }

                var backingBoadrStatus = data.backingBoard.status;
                if ((backingBoadrUrl && backingBoadrStatus) || fabricStatus) {
                    var shape = data;
                    if(openingCount === 1) {
                        shape = data.openings.data[0];
                    }

                    var backingBoardimgUrl = backingBoadrUrl;

                    if (self.isImageUrl(backingBoardimgUrl)) {
                        self.checkImageExists(backingBoardimgUrl).then(function(exists) {
                            if (exists) {
                                backingBoardimgUrl = backingBoardimgUrl;
                            } else {
                                backingBoardimgUrl = data.backingBoard.defaultImg;
                            }
                            renderFabric.registerAsyncFunction('backingboard');
                            fabric.Image.fromURL(backingBoardimgUrl, function(img) {
                                if (currentCanvasVersion !== self.currentCanvasVersion) {
                                    return;
                                }
                                if (colorOverlay) {
                                    let colorFilter = new fabric.Image.filters.BlendColor({
                                        color: colorOverlay
                                    });
                                    img.filters.push(colorFilter);
                                    img.applyFilters();
                                }

                                // Create a fabric.Image object with the loaded image
                                // var fabricImg = new fabric.Image(img);
                                graphicImg.set({
                                    fill: new fabric.Pattern({source: img.getElement(), repeat: 'repeat'}),
                                    scaleX: graphicImg.scaleX,
                                    scaleY: graphicImg.scaleY,
                                    strokeWidth: 0
                                });
                                canvas.renderAll();
                                renderFabric.markCompleted('backingboard');
                            });
                        });
                    }

                }
            }

            //Upload Image to Graphic
            if(openingCount <= 1) {
                if (data.graphicUpload.status && !self.toggleUploadImage()) {
                    let graphiImgURL = ko.isObservable(data.graphicUpload.imgUrl)
                        ?data.graphicUpload.imgUrl() :data.graphicUpload.imgUrl ;
                    let drawImg = new fabric.Image();

                    // drawImg.setSrc(graphiImgURL, function () {
                    if (graphiImgURL) {
                        var uploadShape = data;
                        if(openingCount === 1) {
                            uploadShape = data.openings.data[0];
                        }
                        fabric.Image.fromURL(graphiImgURL, (graphicUpload) => {
                            if (currentCanvasVersion !== self.currentCanvasVersion) {
                                return;
                            }
                            renderFabric.registerAsyncFunction('graphicUpload');
                            var clippingPath;
                            if (graphicShape === 'circle') {
                                var centerX = graphicImg.left + graphicImg.width / 2;
                                var centerY = graphicImg.top + graphicImg.height / 2;
                                var radiusX = graphicImg.width / 2;
                                var radiusY = graphicImg.height / 2;

                                clippingPath = new fabric.Ellipse({
                                    left: centerX,
                                    top: centerY,
                                    rx: radiusX,
                                    ry: radiusY,
                                    originX: 'center',
                                    originY: 'center',
                                    absolutePositioned: true,
                                });
                            } else {
                                var width = graphicImg.width;
                                var height = graphicImg.height;
                                clippingPath = new fabric.Rect({
                                    left: graphicImg.left + width / 2,
                                    top: graphicImg.top + height / 2,
                                    width: width,
                                    height: height,
                                    originX: 'center',
                                    originY: 'center',
                                    absolutePositioned: true,
                                });
                            }
                            graphicUpload.set({
                                fill: '#CBA887',
                                hoverCursor: 'move',
                                selection: false,
                                selectable: true,
                                lockMovementX: false,
                                lockMovementY: false,
                                hasControls: false,
                                clipPath: clippingPath
                            });

                            self.scaleImageToSlot(graphicUpload, graphicImg);
                            canvas.add(graphicUpload);
                            graphicUpload.bringToFront();
                            canvas.renderAll();
                            var boundaryLeft = graphicImg.left;
                            var boundaryRight = graphicImg.left + graphicImg.width;
                            var boundaryTop = graphicImg.top;
                            var boundaryBottom = graphicImg.top + graphicImg.height;

                            graphicUpload.on('moving', function (e) {
                                if (this.left >= boundaryLeft) {
                                    this.left = boundaryLeft;
                                }
                                if (this.left + this.width * graphicUpload.scaleX <= boundaryRight) {
                                    this.left = boundaryRight - this.width * graphicUpload.scaleX;
                                }
                                if (this.top >= boundaryTop) {
                                    this.top = boundaryTop;
                                }
                                if (this.top + this.height * graphicUpload.scaleY <= boundaryBottom) {
                                    this.top = boundaryBottom - this.height * graphicUpload.scaleY;
                                }

                            });
                            canvas.renderAll();
                            renderFabric.markCompleted('graphicUpload');
                        });
                    }
                    // }, {crossOrigin: 'anonymous'});
                }
            }

            if(data.imageZoom) {
                canvas.hoverCursor = 'zoom-in';

                var targetTransform = [1, 0, 0, 1, 0, 0]; // Default viewport transform

                canvas.on('mouse:wheel', function(opt) {
                    var delta = opt.e.deltaY;
                    var zoom = canvas.getZoom();
                    zoom *= 0.999 ** delta;


                    if (zoom > 5) zoom = 5;
                    if (zoom < 1) zoom = 1;
                    // Check if zooming out
                    if (zoom < canvas.getZoom()) {
                        // Calculate the current viewport transform
                        var currentTransform = canvas.viewportTransform;
                        // Check if the current transform is close to the target transform
                        var isClose = true;
                        for (var i = 0; i < currentTransform.length; i++) {
                            if (Math.abs(currentTransform[i] - targetTransform[i]) > 0.001) {
                                isClose = false;
                                break;
                            }
                        }

                        // If the current transform is not close to the target, gradually decrease it
                        if (!isClose) {
                            currentTransform = currentTransform.map(function(value, index) {
                                return value + (targetTransform[index] - value) * 0.25; // Adjust the transition speed here (0.1 for smooth transition)
                            });
                            canvas.setViewportTransform(currentTransform);
                        } else {
                            // Reset the viewportTransform to default values
                            canvas.setViewportTransform(targetTransform);
                        }

                    } else {

                        var zoomMultiplier = 0.999 ** delta;

                        // Calculate the new zoom level
                        var newZoom = zoom * zoomMultiplier;

                        // Calculate the offset of the viewport
                        var canvasWidth = canvas.getWidth();
                        var canvasHeight = canvas.getHeight();
                        // var offsetX = opt.e.offsetX;
                        // var offsetY = opt.e.offsetY;
                        // canvas.zoomToPoint({ x: opt.e.offsetX, y: opt.e.offsetY }, zoom);
                        var pointer = canvas.getPointer(opt.e);

                        // Zoom to the specified zoom level around the pointer position
                        canvas.zoomToPoint(pointer, zoom);

                        var viewPoint = canvas.viewportTransform;

                        canvas.setViewportTransform([
                            zoom, 0, 0,
                            zoom, viewPoint[4],
                            viewPoint[5]
                        ]);

                        canvas.requestRenderAll();
                        canvas.renderAll();
                    }

                    opt.e.preventDefault();
                    opt.e.stopPropagation();
                });
            } else {
                canvas.off('mouse:wheel');
                canvas.hoverCursor = 'pointer';
            }

            renderFabric.registerSuccessCallback(function() {
                canvas.on('mouseup', function(options) {
                    var target = canvas.findTarget(options.e);
                    if (target) {
                        target.selectable = false;
                        canvas.renderAll();
                    }
                });

                canvas.renderAll();
                self.canvasData(canvas.toDataURL());
            });

        },
        drawPreviewText: function (canvas, object, shape, data, self){
            if(self.toggleUploadImage()){
                return;
            }
            if(data.openings.data.length > 1){
                let openingIndex = object.index;
                if(!data.graphics[openingIndex].url()){
                    return;
                }

            } else {
                if(!data.graphicUpload.status){
                    return;
                }
            }
            let graphicWidth = 200;
            var previewLeft = 50;
            var previewTop = 15;
            var previewFont = 20;
            var previewBold = 'bold';
            var previewHeight = object.height;
            var previewCircle = 50;

            if(object.width < 300) {
                graphicWidth = 100;
                previewLeft = 20;
                previewTop = 20;
                previewFont = 12;
                previewBold = '';
            }

            if(object.width < 150) {
                graphicWidth = 50;
                previewLeft = 5;
                previewTop = 20;
                previewFont = 8;
                previewBold = '';
            }


            if (shape === 'circle') {
                previewHeight = previewHeight/2;
                previewCircle = previewCircle/2;
            }
            var graphicNote = new fabric.Rect({
                fill: '#c19c52',
                width: graphicWidth,
                height: 50,
                top: (previewHeight + object.top) - previewCircle,
                left: (object.width +object.left)-graphicWidth,
                selectable: false,
                controlsAboveOverlay: false
            });
            canvas.add(graphicNote);
            if (shape === 'circle') {
                graphicNote.left = object.left + (object.width - graphicNote.width) / 2;
            }
            graphicNote.bringToFront();

            var previewTextObj = fabric.util.createClass(fabric.Text, {
                type: 'previewText',
                initialize: function(text, options) {
                    options || (options = {});
                    this.callSuper('initialize', text, options);
                }
            });
            var previewText = new previewTextObj('Preview only', {
                left: graphicNote.left + previewLeft,
                top: graphicNote.top+ previewTop,
                fontSize: previewFont,
                fill: 'white',
                selection: false,
                selectable: false,

            });
            canvas.add(previewText);
            canvas.bringToFront(previewText);
        },
        getMediaType: function (fillData, imgUrl) {
            let self = this;
            return new Promise(function(resolve) {
                let finalPatternFill = false;

                if (fillData && self.isValidColorCode(fillData)) {
                    finalPatternFill = 'color';
                    resolve(finalPatternFill); // Resolve the promise here
                } else if (imgUrl && self.isImageUrl(imgUrl)) {
                    self.checkImageExists(imgUrl).then(function(exists) {
                        if (exists) {
                            finalPatternFill = 'img';
                        }
                        resolve(finalPatternFill); // Resolve the promise here
                    });
                } else {
                    resolve(finalPatternFill); // Resolve with the default value
                }
            });

        },
        isValidColorCode: function (colorCode) {
            // Regular expression patterns for valid color codes
            const hexPattern = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
            const rgbPattern = /^rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)$/;
            const rgbaPattern = /^rgba\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*(0|1|0?\.\d+)\s*\)$/;

            return hexPattern.test(colorCode) || rgbPattern.test(colorCode) || rgbaPattern.test(colorCode);
        },
        isImageUrl: function(url) {
            return(url.match(/\.(jpeg|jpg|gif|png)$/) != null);
        },
        checkImageExists: function(url) {
            return new Promise(function(resolve) {
                var img = new Image();
                img.onload = function() {
                    resolve(true);
                };
                img.onerror = function() {
                    resolve(false);
                };
                img.src = url;
            });
        },
        getObjectWidth: function (textObject){
            var tempSpan = document.createElement('span');
            tempSpan.style.fontFamily = textObject.fontFamily;
            tempSpan.style.fontSize = textObject.fontSize + 'px';
            tempSpan.style.fontWeight = textObject.fontWeight;
            tempSpan.style.fontStyle = textObject.fontStyle;
            tempSpan.style.position = 'absolute';
            tempSpan.style.visibility = 'hidden';
            if(!textObject.text) {
                return 0;
            }
            var lines = textObject.text.split(/\r?\n/);

            var maxWidth = 0;
            for (var i = 0; i < lines.length; i++) {
                tempSpan.textContent = lines[i];
                document.body.appendChild(tempSpan);
                var lineWidth = tempSpan.getBoundingClientRect().width;
                document.body.removeChild(tempSpan);

                if (lineWidth > maxWidth) {
                    maxWidth = lineWidth;
                }
            }

            var textWidth = maxWidth;
            return textWidth;

        },
        // updateMagnifier: function (event, isMagnifierVisible, magnifier, canvas){
        //     setInterval(function() {
        //         canvas.renderAll();
        //     }, 16);
        //     var pointer = canvas.getPointer(event.e);
        //     var magnifierSize = 300; // Adjust the size of the magnifier as needed
        //     var zoomFactor = 2; // Adjust the zoom factor as needed
        //     // Update the magnifier position
        //     magnifier.style.left = $(".product-media-top").width() + 'px';
        //     // magnifier.style.top = pointer.y + 'px';
        //
        //     // Calculate the zoomed region in the canvas
        //     var zoomedWidth = magnifierSize / zoomFactor;
        //     var zoomedHeight = magnifierSize / zoomFactor;
        //     var zoomedLeft = pointer.x - zoomedWidth / 2;
        //     var zoomedTop = pointer.y - zoomedHeight / 2;
        //
        //     // Render the zoomed region in the magnifier
        //     var zoomedImageData = canvas.toDataURL({
        //         left: zoomedLeft,
        //         top: zoomedTop,
        //         width: zoomedWidth,
        //         height: zoomedHeight,
        //         multiplier: zoomFactor
        //     });
        //
        //     magnifier.style.backgroundImage = 'url(' + zoomedImageData + ')';
        // },
        isMixedNumber: function(value) {
            if (typeof value !== "string") {
                return false; // Mixed numbers are typically represented as strings
            }

            // Regular expression pattern to match a mixed number
            const pattern = /^(\d+)\s+(\d+)\/(\d+)$/;

            return pattern.test(value);
        },
        parseMixedNumber: function(mixedNumber) {
            var parts = mixedNumber.split(' ');

            var whole = parseInt(parts[0], 10);

            var numerator = 0;
            var denominator = 1;

            if (parts.length > 1) {
                var fractionParts = parts[1].split('/');

                if (fractionParts.length === 2) {
                    numerator = parseInt(fractionParts[0], 10);
                    denominator = parseInt(fractionParts[1], 10);
                }
            }

            return whole + (numerator / denominator);
        },
        initialize: function() {
            this._super();
            let self = this;
            self.imagezoom.subscribe(function (value){
                self.redrawFabric();
            });
            self.toggleUploadImage.subscribe(function(value){
                self.drawImage();
            }, self);
            _.each(self.openingDataArray(),function(item) {
                item.shapeSelection.subscribe(function (value) {
                    self.redrawFabric();
                });
            });
            domObserver.get("#image-customizer",function(element){
                if ($(element).length){
                    self.canvas = new fabric.Canvas("image-customizer");
                    self.canvasLoaded = true;
                    self.redrawFabric();
                    $(window).resize(function () {
                        self.redrawFabric();
                    });
                }
            });
            if (self.headerLabelType === 'header'){
                self.addSubscription(self.headerTextDataArray());
                self.updatedHeaderImage(self.headerImageDataArray());
            }
            if (self.headerLabelType === 'label'){
                self.addSubscription(self.labelTextDataArray());
                self.updatedLabelImage(self.labelImageDataArray());
            }
            return this;
        },
        updatedImageSize:function(value){
            let width = pixelConverter.convertPixelsToInches(value.width,96);
            let height = pixelConverter.convertPixelsToInches(value.width,96);
            width = pixelConverter.convertDecimalToFraction(width);
            height = pixelConverter.convertDecimalToFraction(height);
            return width + " Wide x " + height + " High";
        },
        frameOptionSelected:function(value){
        },
        widthIntegerSelected:function(newValue){
        },
        widthFractionalSelected:function(newValue){
        },
        selectedHeightInteger:function(newValue){
        },
        selectedHeightFractional:function(newValue){
        },
        diffPercentage: function (maxVal, minVal){
            return Math.round(((maxVal - minVal)/maxVal)*100);
        },
        inchToPixelCalc: function (overallPix, overallInch, calcPixel){
            let inch = 0;
            if ($.isNumeric(calcPixel) || calcPixel.indexOf('/') === -1) {
                inch += +calcPixel;
            } else {
                inch += new Fraction(calcPixel).valueOf();
            }
            let widthPixelRate = overallPix.width/overallInch.width;
            let heightPixelRate = overallPix.height/overallInch.height;
            let calPixDim = [];
            calPixDim['width']= widthPixelRate * inch;
            calPixDim['height']= heightPixelRate * inch;
            return calPixDim;
        },
        getUpdatedCanvasSize:function (overallDim, frameWidth, frameHeight){
            let canvasWidthRatio = overallDim.width/frameWidth;
            let canvasHeightRatio = overallDim.height/frameHeight;
            let multiplicationFactor = Math.min(canvasWidthRatio, canvasHeightRatio);
            let canvasDim = [];
            canvasDim['width'] = frameWidth * multiplicationFactor;
            canvasDim['height'] = frameHeight * multiplicationFactor;
            return canvasDim;
        },
        getGraphicSize:function (frameInch, frameDim, grapicW, graphicH){
            let pixelRateWidth = frameDim.width/frameInch.width;
            let pixelRateHeight = frameDim.height/frameInch.height;
            let graphicDim = [];
            graphicDim['width'] = grapicW*pixelRateWidth;
            graphicDim['height'] = graphicH*pixelRateHeight;
            return graphicDim;
        },
        getFullNumber:function (dim, tenth){
            let inch = $.isNumeric(dim) ? dim :+dim.integer;
            //   if (typeof tenth == 'string') {
            if(typeof(tenth) === 'boolean'){
                return ;
            }
            if ($.isNumeric(tenth) || tenth.indexOf('/') === -1) {
                inch += +tenth;
            } else {
                inch += new Fraction(tenth).valueOf();
            }
            //    }
            return inch;
        },
        drawFrame: function (canvas, frameSize, frameUrl) {
            // //draw frame
            // let canvasContainer = document.getElementById('image-customizer');
            let canvasContainer = canvas;
            let width = canvasContainer.width;
            let height = canvasContainer.height;
            const cwidth = width;
            const cheight = height;
            const widthorigin = (cwidth-width)/2;
            const heightorigin = (cheight-height)/2;
            const frame_scale = frameSize;

            var frameRect = fabric.util.createClass(fabric.Rect, {

                type: 'frameRect',

                initialize: function(options) {

                    options || (options = { });
                    const image = new Image();
                    image.src = frameUrl;

                    this.callSuper('initialize', options);

                    image.onload = (function() {
                        this.width = 0.1;
                        this.height = 0.1;
                        this.image_loaded = true;
                    }).bind(this);

                    this.set('image', image);
                },


                _render: function(ctx) {
                    if (this.image_loaded) {
                        ctx.save();
                        ctx.beginPath();
                        ctx.moveTo(widthorigin,heightorigin);
                        ctx.lineTo(width+widthorigin, heightorigin);
                        ctx.lineTo(width+widthorigin-frame_scale, heightorigin+frame_scale);
                        ctx.lineTo(widthorigin+frame_scale, heightorigin+frame_scale);
                        ctx.closePath();
                        ctx.clip();
                        ctx.drawImage(this.image, 0, 0, width, frame_scale);
                        ctx.restore();

                        //rightFrame
                        ctx.save();
                        ctx.translate(widthorigin+width, 0);
                        ctx.rotate(90*(Math.PI/180));
                        ctx.beginPath();
                        ctx.moveTo(heightorigin,0);
                        ctx.lineTo(heightorigin+height, 0);
                        ctx.lineTo(height+heightorigin-frame_scale, frame_scale);
                        ctx.lineTo(heightorigin+frame_scale, frame_scale);
                        ctx.closePath();
                        ctx.clip();
                        ctx.drawImage(this.image, heightorigin, 0, height, frame_scale);
                        ctx.restore();

                        //bottomFrame
                        ctx.save();
                        ctx.translate(width+widthorigin, height+heightorigin);
                        ctx.rotate(180*(Math.PI/180));
                        ctx.beginPath();
                        ctx.moveTo(0,0);
                        ctx.lineTo(width, 0);
                        ctx.lineTo(width-frame_scale, frame_scale);
                        ctx.lineTo(frame_scale, frame_scale);
                        ctx.closePath();
                        ctx.clip();
                        ctx.drawImage(this.image, 0,0,width, frame_scale);
                        ctx.restore();

                        //leftFrame
                        ctx.save();
                        ctx.translate(widthorigin, height+heightorigin);
                        ctx.rotate(-90*(Math.PI/180));
                        ctx.beginPath();
                        ctx.moveTo(0,0);
                        ctx.lineTo(height, 0);
                        ctx.lineTo(height-frame_scale, frame_scale);
                        ctx.lineTo(frame_scale, frame_scale);
                        ctx.closePath();
                        ctx.clip();
                        ctx.drawImage(this.image, 0,0, height, frame_scale);
                        ctx.restore();
                        this.callSuper('_render', ctx);
                    }
                }
            });

            var frame = new frameRect({ name: 'frame'});
            return frame;
        },
        scaleImageToSlot:function (image, slot) {
            let ratio = Math.max(slot.width / image.width, slot.height / image.height);
            image.scaleX *= ratio;
            image.scaleY *= ratio;
            var shiftLeft = (slot.width - (image.width*image.scaleX)) / 2;
            var shiftTop = (slot.height - (image.height*image.scaleY)) / 2;
            const alignCenter = true;
            if(alignCenter) {
                image.set({left:slot.left + shiftLeft});
                image.set({top:slot.top + shiftTop});
            } else {
                if(image.left < slot.left) {
                    // align image to center.
                    image.set({left:slot.left});
                }
                if(image.top < slot.top) {
                    image.set({top:slot.top});
                }
            }

        },
        clipBySlot:function (ctx, image, slot, data) {
            let self = this;
            var scaleXTo1 = (1 / image.scaleX);
            var scaleYTo1 = (1 / image.scaleY);

            // Save context of the canvas so it can be restored after the clipping
            ctx.save();
            ctx.translate(0, 0);
            ctx.rotate(self.degToRad(image.angle * 1));
            ctx.scale(scaleXTo1, scaleYTo1);
            ctx.beginPath();

            const boundingRect = image.getBoundingRect();

            if(data.shape == 'circle') {
                var shiftLeft = (slot.width - (image.width*image.scaleX)) / 2;
                var shiftTop = (slot.height - (image.height*image.scaleY)) / 2;
                ctx.ellipse(
                    slot.left + shiftLeft - image.left,
                    slot.top + shiftTop- image.top,
                    slot.width/2,
                    slot.height/2,
                    0,
                    0,
                    2 * Math.PI
                );
            } else {
                ctx.rect(
                    slot.left - image.left - Math.floor(boundingRect.width / 2),
                    slot.top - image.top - Math.floor(boundingRect.height / 2),
                    slot.width,
                    slot.height
                );
            }

            ctx.strokeStyle = 'transparent';
            ctx.closePath();

            // Restore the original context.
            ctx.restore();
        },
        degToRad:function (degrees) {
            return degrees * (Math.PI / 180);
        }
    });
});
