define([
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'uiRegistry',
    "jquery",
    'Ziffity_CustomFrame/js/lib/fraction.min',
    'Ziffity_CustomFrame/js/lib/vue',
    'Ziffity_CustomFrame/js/lib/vue-resource',
    'underscore',
    'Ziffity_CustomFrame/js/lib/fabric.min',
    'jquery/ui',
    "webfont"
], function($t,uiAlert,registry,$,Fraction, Vue, VueResource,_) {
    "use strict";
    Vue.use(VueResource);
    if (window.Element && !Element.prototype.closest) {
        Element.prototype.closest =
            function(s) {
                var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                    i,
                    el = this;
                do {
                    i = matches.length;
                    while (--i >= 0 && matches.item(i) !== el) {};
                } while ((i < 0) && (el = el.parentElement));
                return el;
            };
    }
    (function (arr) {
        arr.forEach(function (item) {
            if (item.hasOwnProperty('remove')) {
                return;
            }
            Object.defineProperty(item, 'remove', {
                configurable: true,
                enumerable: true,
                writable: true,
                value: function remove() {
                    this.parentNode.removeChild(this);
                }
            });
        });
    })([Element.prototype, CharacterData.prototype, DocumentType.prototype]);
    var DRAW_TOP_BOT = (function () {
        var mod      = {},
            insts    = [],
            size_ihn = {
                min_op   : 3,
                side_gaps: {
                    top: 0,
                    bottom: 0,
                    left: 0,
                    right: 0
                }
            },
            size_px  = {
                cs_max: 1000,
                bd_length: 15,
                bd_width : 3
            },
            product_options,
            tb_gap_ihn      = new Fraction('1 1/2').valueOf(),
            tb_gap_near_ihn = new Fraction('1 1/2').valueOf(),
            side_gaps_fun_inch = {
                top: {
                    labels: function () {
                        if (!product_options.modules.labels || product_options.modules.labels.position !== 'top') {
                            return 0;
                        }

                        return tb_gap_ihn + tb_gap_near_ihn +
                            new Fraction(product_options.modules.labels.size.height).valueOf();
                    },
                    crheader: function () {
                        if (!product_options.modules.crheader || product_options.modules.crheader.position !== 'top') {
                            return 0;
                        }

                        return tb_gap_ihn + tb_gap_near_ihn +
                            new Fraction(product_options.modules.crheader.size.height).valueOf();
                    }
                },
                bottom: {
                    labels: function () {
                        if (!product_options.modules.labels || product_options.modules.labels.position !== 'bottom') {
                            return 0;
                        }

                        return tb_gap_ihn + tb_gap_near_ihn +
                            new Fraction(product_options.modules.labels.size.height).valueOf();
                    },
                    crheader: function () {
                        if (!product_options.modules.crheader || product_options.modules.crheader.position !== 'bottom') {
                            return 0;
                        }

                        return tb_gap_ihn + tb_gap_near_ihn +
                            new Fraction(product_options.modules.crheader.size.height).valueOf();
                    }
                },
                left: {},
                right: {}
            },
            view_opts = {
                bg: '#DDD',
                op_bg: '#fff',
                bd_clr: '#000'
            },
            fraction_list_step_ihn = new Fraction('1/16').valueOf(),
            fraction_list = [],
            events = {},
            point_val = 72,
            mess_list_id = 'messages',
            mess_list_el;

        /**
         * Add available fraction parts
         */
        (function () {
            for (var fr_i = 0; fr_i <= 1; fr_i += fraction_list_step_ihn) {
                fraction_list.push({
                    text: new Fraction(fr_i).toFraction(true),
                    val: fr_i
                });
            }
        })();

        /**
         * Make number with fraction from the {@link fraction_list}
         * @param {object} fraction - fraction object
         */
        var convertToCorrectFraction = function (fraction) {
            var frac_view   = fraction.toFraction(true),
                frac_arr    = frac_view.split(' '),
                integer     = +frac_arr[0],
                has_int     = fraction.n > fraction.d,
                frac_part_f = has_int ? frac_arr[1] : frac_arr[0],
                frac_part,
                frac_diff,
                frac_new_f,
                result;

            if (frac_part_f && frac_part_f !== '0') {
                frac_part = new Fraction(frac_part_f).valueOf();
                frac_diff  = 1;
                _(fraction_list).each(function (frac) {
                    if (frac.val > frac_part) {
                        if (frac.val - frac_part < frac_diff) {
                            frac_diff = frac.val - frac_part;
                            frac_new_f = frac.text;
                        }
                    } else {
                        if (frac_part - frac.val < frac_diff) {
                            frac_diff = frac_part - frac.val;
                            frac_new_f = frac.text;
                        }
                    }
                });

                if (has_int) {
                    result = (frac_new_f === 0 || frac_new_f === 1 || +frac_new_f === 0 || +frac_new_f === 1) ?
                        (integer + +frac_new_f) : integer + ' ' + frac_new_f;
                } else {
                    result = frac_new_f;
                }
                return result;
            }

            return frac_view;
        };

        /**
         * Create url from object
         * @param {object} object - object for converting
         * @return {string} - {@link Blob} url
         */
        var createObjectURL = function (object) {
            var urlCreator  = window.URL || window.webkitURL;

            return urlCreator.createObjectURL(object);
        };

        /**
         * @param {HTMLElement} canvas - canvas element
         */
        function TopBot(canvas) {
            if (!product_options) {
                return;
            }

            this.cs = canvas;
            this.name = canvas.getAttribute('data-mod');
            this.els = {};
            this.size_ihn = {
                font: {}
            };
            this.size_px  = {
                font: {}
            };
            this.view     = {};

            var mod_data = product_options.modules[this.name];

            this.list_dev = {
                text: mod_data.texts || [],
                images: mod_data.images || []
            };

            this.init();

            console.log(this);

            // $('body').trigger('processStop');
        }

        /**
         * Initialize functionality
         */
        TopBot.prototype.init = function () {
            this.setModEls();
            this.setElsEvents();//call , button-click
            this.updateVars();
            this.renderView();
            this.initControls();
            triggerCustomEvent('changed_height');
            //initializing
            updateCoreVars();
            this.update();
        };

        /**
         * Initialize controls
         */
        TopBot.prototype.initControls = function () {
            var _inst    = this,
                mod_data = product_options.modules[this.name];

            this.v_controls = new Vue({
                name: this.name + ' controls',
                el: '#tool-controls-' + this.name,
                data: {
                    mod_name    : this.name,
                    fonts       : mod_data.fonts,
                    text_colors : mod_data.text_colors,
                    list_text   : this.list_dev.text,
                    list_images : this.list_dev.images,
                    list_bg_colors: mod_data.bg_colors,
                    bg_color    : this.view.bg_active
                },
                methods: {
                    reverseList: function(list) {
                        return list
                            .map(function (item) {
                                return item;
                            })
                            .reverse();
                    },
                    updateNames: function () {
                        var v_list = this.$get('list_text'),
                            inst_name = (_inst.name === 'crheader') ? 'Header' : 'Label';

                        _.each(v_list, function (layer, index) {
                            layer.name_id = index + 1;
                            layer.name    = 'Text ' + inst_name + ' ' + layer.name_id;
                        });

                        v_list = this.$get('list_images');
                        _.each(v_list, function (layer, index) {
                            layer.name_id = index + 1;
                            layer.name    = 'Image ' + inst_name + ' ' + layer.name_id;
                        });
                    },
                    addLayerText : this.addLayerText.bind(this),
                    addLayerImage: this.addLayerImage.bind(this),
                    selectLayer  : function (list_name, layer, index, from_canvas) {
                        var v_list_t = this.$get('list_text'),
                            v_list_i = this.$get('list_images'),
                            old_active_t = _.findWhere(v_list_t, {
                                active: true
                            }),
                            old_active_i = _.findWhere(v_list_i, {
                                active: true
                            }),
                            new_active = this.$get('list_' + list_name + '[' + index + ']');

                        if (old_active_t) {
                            old_active_t.active = false;
                        }
                        if (old_active_i) {
                            old_active_i.active = false;
                        }

                        this.$set('list_' + list_name + '[' + index + '].active', true);

                        if (!from_canvas) {
                            _inst.fc.deactivateAll();
                            if (layer.fc) {
                                _inst.selectLayerFc(layer.fc);
                            }
                        }
                    },
                    selectTextLayer : function (layer, index, from_canvas) {
                        this.selectLayer('text', layer, index, from_canvas);
                    },
                    selectImageLayer: function (layer, index, from_canvas) {
                        this.selectLayer('images', layer, index, from_canvas);
                    },
                    removeLayer  : this.removeLayer.bind(this),
                    changeText   : function (layer) {
                        layer.fc.setText(layer.text);
                        _inst.checkSizePos(layer.fc);
                        _inst.fc.renderAll();
                        _inst.setSizeToProps(layer);
                    },
                    changeFontFam: function (layer) {
                        layer.fc.setFontFamily(layer.font);
                        layer.fc.setText(layer.text);
                        _inst.checkSizePos(layer.fc);
                        _inst.fc.renderAll();
                        _inst.setSizeToProps(layer);
                    },
                    setTextColor : function (layer) {
                        layer.fc.setFill(layer.text_color);
                        _inst.fc.renderAll();
                    },
                    setFontWeight: function (layer) {
                        layer.fc.setFontWeight(layer.font_style.bold ? 700 : 400);
                        layer.fc.setText(layer.text);
                        _inst.checkSizePos(layer.fc);
                        _inst.fc.renderAll();
                        _inst.setSizeToProps(layer);
                    },
                    setFontStyle : function (layer) {
                        layer.fc.setFontStyle(layer.font_style.italic ? 'italic' : 'normal');
                        layer.fc.setText(layer.text);
                        _inst.checkSizePos(layer.fc);
                        _inst.fc.renderAll();
                    },
                    setFontDecoration: function (layer) {
                        layer.fc.set('textDecoration', layer.font_style.underline ? 'underline' : '');
                        _inst.fc.renderAll();
                    },
                    increaseFont : function (layer, index) {
                        this.changeFontSize(layer, index, true);
                    },
                    decreaseFont : function (layer, index) {
                        this.changeFontSize(layer, index);
                    },
                    changeFontSize: function (layer, index, to_increase) {
                        var current_size_inch = this.$get('list_text[' + index + '].font_size_inch'),
                            current_size_ihn  = new Fraction(current_size_inch).valueOf(),
                            step_ihn    = _inst.size_ihn.font.step,
                            min_ihn     = _inst.size_ihn.font.min,
                            ratio       = _inst.size_px.ratio,
                            result_ihn  = to_increase ? current_size_ihn + step_ihn : current_size_ihn - step_ihn,
                            result_size = false;

                        if (result_ihn < min_ihn) {
                            result_ihn = min_ihn;
                        }

                        layer.fc.setText(layer.text);
                        layer.fc.setFontSize(result_ihn * ratio);
                        result_size = _inst.checkSizePos(layer.fc);
                        if (result_size) {
                            result_ihn = convertToCorrectFraction(new Fraction(result_size / ratio));
                        }

                        _inst.fc.renderAll();

                        this.$set('list_text[' + index + '].font_size_inch', new Fraction(result_ihn).toFraction(true));
                        this.$set('list_text[' + index + '].font_size_points', result_ihn * point_val);

                        _inst.setSizeToProps(layer);
                    },
                    setTextAlign : function (layer) {
                        layer.fc.setTextAlign(layer.text_align);
                        _inst.fc.renderAll();
                    },
                    changeLayerImage: function (e, layer, id) {
                        var input  = e.target,
                            reader = new FileReader(),
                            _validFileExtensions = [".jpg", ".jpeg", ".bmp", ".gif", ".png"],
                            size_ratio  = 1000000,
                            max_size    = 2 * size_ratio,
                            max_side_size = 1900;

                        if (!input.files || !input.files[0]) {
                            return;
                        }

                        if (input.files[0].size > max_size) {
                            alert("Sorry, max file size is " + (max_size / size_ratio) + "Mb");
                            return;
                        }

                        // check file type
                        var sFileName = input.value;
                        if (sFileName.length > 0) {
                            var blnValid = false;
                            for (var j = 0; j < _validFileExtensions.length; j++) {
                                var sCurExtension = _validFileExtensions[j];
                                if (
                                    sFileName.substr(sFileName.length - sCurExtension.length, sCurExtension.length).toLowerCase()
                                    == sCurExtension.toLowerCase()
                                ) {
                                    blnValid = true;
                                    break;
                                }
                            }

                            if (!blnValid) {
                                var startIndex = (sFileName.indexOf('\\') >= 0 ? sFileName.lastIndexOf('\\') : sFileName.lastIndexOf('/'));
                                var filename = sFileName.substring(startIndex);
                                if (filename.indexOf('\\') === 0 || filename.indexOf('/') === 0) {
                                    filename = filename.substring(1);
                                }

                                alert("Sorry, " + filename + " is invalid, allowed extensions are: " + _validFileExtensions.join(", "));
                                return false;
                            }
                        }

                        var tmp_img = new Image();
                        tmp_img.addEventListener('load', function () {
                            if (this.width > max_side_size || this.height > max_side_size) {
                                alert("Sorry, max image side size is " + max_side_size + "Px");
                                return;
                            }

                            reader.onload = function (e) {
                                layer.url = e.target.result;

                                fabric.Image.fromURL(layer.url, function(img_fc) {
                                    layer.fc = img_fc;

                                    layer.width  = img_fc.width;
                                    layer.height = img_fc.height;

                                    _inst.setSizeToProps(layer);
                                    _inst.setPosToProps(layer);

                                    _inst.addLayerImageFc(layer, id);

                                    _inst.fc.renderAll();

                                    input.value = '';
                                });
                            };
                            reader.readAsDataURL(input.files[0]);
                        });
                        tmp_img.src = createObjectURL(input.files[0]);
                    },
                    setBgColor: function (color) {
                        _inst.view.bg_active = color;
                        _inst.view.bg_fc.setFill(color);
                        _inst.fc.renderAll();
                    }
                }
            });

            this.els.tool_wr.className = this.els.tool_wr.className.replace('hidden-wr', '');
        };

        /**
         * Reorganize layers Id
         */
        TopBot.prototype.reorganizeLayersId = function () {
            var v_controls = this.v_controls;

            _.each(v_controls.list_text, function (layer, id) {
                layer.fc.inst_id = id;
            });
            _.each(v_controls.list_images, function (layer, id) {
                if (layer.fc) {
                    layer.fc.inst_id = id;
                }
            });
        };

        /**
         * Remove layer
         * @param {object} layer - layer that needed remove
         * @param {text} section_name - section name
         */
        TopBot.prototype.removeLayer = function (layer, section_name) {
            this.fc.remove(layer.fc);
            this.fc.renderAll();

            this.v_controls['list_' + section_name].$remove(layer);

            this.reorganizeLayersId();

            this.v_controls.updateNames();
        };

        /**
         * Check layer position & correct it if wrong
         * @param {fabric.Object} obj - fabric js instance of the layer
         */
        TopBot.prototype.checkLayerPosition = function (obj) {
            var left        = obj.left,
                top         = obj.top,
                width       = obj.width * obj.scaleX,
                height      = obj.height * obj.scaleY,
                ext_points  = this.size_px.pos;

            if (left + width >= ext_points.right) {
                obj.set('left', ext_points.right - width);
            }
            if (left <= ext_points.left) {
                obj.set('left', ext_points.left);
            }
            if (top + height >= ext_points.bottom) {
                obj.set('top', ext_points.bottom - height);
            }
            if (top <= ext_points.top) {
                obj.set('top', ext_points.top);
            }
            obj.setCoords();
        };

        /**
         * Check text width
         */
        TopBot.prototype.checkTextWidth = function (obj) {
            var _self          = this,
                orig_text      = obj.getText(),
                tmp_text_arr   = orig_text.replace('\n', ' ').replace('\r', ' ').split(' '),
                tmp_text_lines = [],
                result         = '',
                max_width      = _self.size_px.width;

            _.each(tmp_text_arr, function (text_part) {
                // check word length
                obj.setText(text_part);
                obj._initDimensions();
                if (obj.width > max_width) {
                    var tmp_part = '',
                        stop_check = false;
                    _.each(text_part.split(''), function (letter) {
                        if (stop_check) {
                            return;
                        }
                        var prev_text = tmp_part;
                        tmp_part += letter;
                        obj.setText(tmp_part);
                        obj._initDimensions();
                        if (obj.width > max_width) {
                            text_part = prev_text;
                            stop_check = true;
                        }
                    });
                }

                if (tmp_text_lines.length) {
                    obj.setText(tmp_text_lines[tmp_text_lines.length - 1] + ' ' + text_part);
                } else {
                    obj.setText(text_part);
                }
                obj._initDimensions();
                if (obj.width > max_width|| tmp_text_lines.length === 0) {
                    tmp_text_lines.push(text_part);
                } else {
                    tmp_text_lines[tmp_text_lines.length - 1] += ' ' + text_part;
                }
            });

            result = tmp_text_lines.join('\n');
            obj.setText(result);
            obj._initDimensions();
        };

        /**
         * Check text height
         */
        TopBot.prototype.checkTextHeight = function (obj) {
            var _self          = this,
                orig_text      = obj.getText(),
                tmp_text_arr   = orig_text.split('\n'),
                stop_check     = false,
                tmp_lines      = '',
                result_size    = false,
                max_height     = _self.size_px.height;

            _.each(tmp_text_arr, function (line, id) {
                if (stop_check) {
                    return;
                }
                var prev_lines = tmp_lines;
                tmp_lines += ((tmp_lines === '') ? '' : '\n') + line.trim();
                obj.setText(tmp_lines);
                obj._initDimensions();
                if (obj.height > max_height) {
                    if (id === 0) {
                        result_size = max_height / obj.height * obj.getFontSize();
                        obj.setFontSize(result_size);
                    } else {
                        tmp_lines = prev_lines;
                    }
                    stop_check = true;
                }
            });

            obj.setText(tmp_lines);
            obj._initDimensions();

            if (result_size !== false) {
                return result_size;
            }
        };

        /**
         * Check layer size & correct it if wrong
         * @param {fabric.Object} obj - fabric js instance of the layer
         */
        TopBot.prototype.checkLayerSize = function (obj) {
            if (!obj.inst_id && obj.inst_id !== 0) {
                return;
            }

            var id          = obj.inst_id,
                is_text     = obj.type === 'text',
                result_size = false,
                is_error    = false,
                width_orig  = obj.width * obj.scaleX,
                height_orig = obj.height * obj.scaleY,
                max_width   = this.size_px.width,
                max_height  = this.size_px.height;

            if (obj.width * obj.scaleX > max_width) {
                if (is_text) {
                    this.checkTextWidth(obj);
                } else {
                    var layer_ratio_w = max_width / obj.width;
                    obj.set('scaleX', layer_ratio_w);
                    obj.set('scaleY', layer_ratio_w);
                }
                is_error = true;
            }
            if (obj.height * obj.scaleY > max_height) {
                if (is_text) {
                    result_size = this.checkTextHeight(obj);
                } else {
                    var layer_ratio_h = max_height / obj.height;
                    obj.set('scaleY', layer_ratio_h);
                    obj.set('scaleX', layer_ratio_h);
                }
                is_error = true;
            }

            if (is_error && !is_text && this.v_controls) {
                this.setSizeToProps(
                    this.v_controls.$get('list_images[' + id + ']')
                );
            }

            if (result_size !== false) {
                return result_size;
            }
        };

        /**
         * Check layer size & position then correct it if wrong
         * @param {fabric.Object} obj - fabric js instance of the layer
         */
        TopBot.prototype.checkSizePos = function (obj) {
            var result_size = false;

            if (typeof obj._initDimensions === 'function') {
                obj._initDimensions();
            }

            result_size = this.checkLayerSize(obj);

            this.checkLayerPosition(obj);

            if (result_size !== false) {
                return result_size;
            }
        };

        /**
         * Set current layer (in canvas) size to dev data properties
         * @param {object} layer - layer item
         */
        TopBot.prototype.setSizeToProps = function (layer) {
            var id         = layer.fc.inst_id,
                list_name  = (layer.fc.type === 'text') ? 'text' : 'images',
                v_controls = this.v_controls,
                ratio      = this.size_px.ratio;

            if (v_controls) {
                v_controls.$set(
                    'list_' + list_name + '[' + id + '].width_inch',
                    convertToCorrectFraction(new Fraction(layer.fc.width * layer.fc.scaleX / ratio))
                );
                v_controls.$set(
                    'list_' + list_name + '[' + id + '].height_inch',
                    convertToCorrectFraction(new Fraction(layer.fc.height * layer.fc.scaleY / ratio))
                );
            }
        };

        /**
         * Set current layer (in canvas) size to dev data properties
         * @param {object} layer - layer item
         */
        TopBot.prototype.setPosToProps = function (layer) {
            var id              = layer.fc.inst_id,
                ratio           = this.size_px.ratio,
                extreme_points  = this.size_px.pos,
                list_name       = (layer.fc.type === 'text') ? 'text' : 'images';

            if (this.v_controls) {
                this.v_controls.$set(
                    'list_' + list_name + '[' + id + '].dev_left_inch',
                    convertToCorrectFraction(new Fraction((layer.fc.left - extreme_points.left) / ratio))
                );
                this.v_controls.$set(
                    'list_' + list_name + '[' + id + '].dev_top_inch',
                    convertToCorrectFraction(new Fraction((layer.fc.top - extreme_points.top) / ratio))
                );
            }
        };

        /**
         * Add text layer
         */
        TopBot.prototype.addLayerText = function () {
            var v_controls  = this.v_controls,
                v_list_text = v_controls.$get('list_text'),
                id          = v_list_text.length,
                new_layer   = {
                    width_inch : '0',
                    height_inch: '0',
                    dev_left_inch: '0',
                    dev_top_inch: '0',
                    left: this.size_px.pos.left,
                    top: this.size_px.pos.top,
                    text: '',
                    font: v_controls.$get('fonts[0]'),
                    font_size_inch: +this.els.font_size_def.value,
                    font_size_points: +this.els.font_size_def.value * point_val,
                    text_color: v_controls.$get('text_colors[0]'),
                    text_align: 'left',
                    font_style: {
                        bold: false,
                        italic: false,
                        underline: false
                    }
                };

            v_list_text.$set(id, new_layer);

            this.addLayerTextFc(new_layer, id);

            v_controls.selectTextLayer(new_layer, id);

            v_controls.updateNames();
        };

        /**
         * Add image layer
         */
        TopBot.prototype.addLayerImage = function () {
            var id         = this.list_dev.images.length,
                v_controls = this.v_controls,
                new_layer  = {
                    url: false,
                    width_inch: '0',
                    height_inch: '0',
                    dev_top_inch: '0',
                    dev_left_inch: '0'
                };

            this.updateLayerToPxPos(new_layer);
            this.updateLayerToPxSize(new_layer);

            v_controls.list_images.$set(id, new_layer);

            v_controls.selectImageLayer(new_layer, id, true);

            v_controls.updateNames();
        };

        /**
         * Add text fabric layer
         */
        TopBot.prototype.addLayerTextFc = function (layer, id) {
            var result_size, result_ihn;

            layer.fc = new fabric.Text(layer.text, {
                hasControls : false,
                inst_id     : id,
                fontFamily  : layer.font,
                fill        : layer.text_color,
                fontSize    : layer.font_size_inch ?
                    (new Fraction(layer.font_size_inch).valueOf() * this.size_px.ratio) :
                    this.size_px.font.def,
                fontWeight  : layer.font_style.bold ? 700 : 400,
                fontStyle   : layer.font_style.italic ? 'italic' : 'normal',
                textDecoration: layer.font_style.underline ? 'underline' : '',
                textAlign   : layer.text_align,
                top         : layer.top || this.size_px.pos.top,
                left        : layer.left || this.size_px.pos.left
            });

            this.fc.add(layer.fc);

            result_size = this.checkSizePos(layer.fc);
            if (result_size) {
                result_ihn = result_size / this.size_px.ratio;
                if (this.v_controls) {
                    this.v_controls.$set('list_text[' + id + '].font_size_inch', new Fraction(result_ihn).toFraction(true));
                    this.v_controls.$set('list_text[' + id + '].font_size_points', result_ihn * point_val);
                }
            }
            this.checkLayerPosition(layer.fc);
            this.setPosToProps(layer);
        };

        /**
         * Add fabric layer instance
         * @param {object} layer - layer item
         * @param {number|string} id - layer id
         */
        TopBot.prototype.addLayerImageFc = function (layer, id) {
            var cur_left_inch = layer.dev_left_inch,
                cur_top_inch  = layer.dev_top_inch;

            layer.fc.set({
                hasRotatingPoint: false,
                lockRotation    : true,
                lockSkewingX    : true,
                lockSkewingY    : true,
                lockScalingFlip : true,
                inst_id : id,
                width   : layer.width,
                height  : layer.height,
                top     : layer.top,
                left    : layer.left
            });

            layer.fc.setControlsVisibility({
                mb: false,
                mt: false,
                ml: false,
                mr: false
            });

            this.fc.add(layer.fc);
            this.checkSizePos(layer.fc);
            this.setSizeToProps(layer);
            this.setPosToProps(layer);
        };

        /**
         * Select layer in canvas
         * @param {fabric.Object} layer - fabric layer instance
         */
        TopBot.prototype.selectLayerFc = function (layer) {
            this.fc.setActiveObject(layer);
        };

        /**
         * Set module elements to properties
         */
        TopBot.prototype.setModEls = function () {
            var tab_wrap = document.querySelector('[data-index="header_group"]'),
                mod_name = this.name.indexOf('header') === -1 ? this.name : 'header';

            this.els.tool_wr  = tab_wrap.querySelector('.admin-topbot-wr');
            this.els.width    = document.querySelector('[name*="header_width"]');//header_width
            this.els.height   = document.querySelector('[name*="header_height"]');//header_height
            this.els.position = document.querySelector('[name*="header_position"]');//header_position
            this.els.font_size_def  = document.querySelector('[name*="font_size_default"]');//header font size default
            this.els.font_size_min  = document.querySelector('[name*="font_size_min"]');//header font size minimal
            this.els.font_size_step = document.querySelector('[name*="font_size_step"]');//header font size step
            this.els.tab_link       = document.getElementById('product_info_tabs_' + mod_name);
            this.els.prod_t         = document.querySelector('select[name*="product[size_type]"]');//from size group - size_type attribute
            this.els.prod_w         = document.querySelector('select[name*="product[dimension_1]"]');//from size group - available width
            this.els.prod_h         = document.querySelector('select[name*="product[dimension_2]"]');//from size group - available height
            this.els.mat_top_int    = document.querySelector('select[name*="openings-mat-size-top-integer"]');//from opening group - mat size top integer
            this.els.mat_top_tenth  = document.querySelector('select[name*="openings-mat-size-top-tenth"]');//from opening group - mat size top tenth
            this.els.mat_rev        = document.querySelector('select[name*="product[openings-mat-size-reveal]"]')//from opening group - mat size top reveal
            this.els.mat_over       = document.querySelector('select[name*="product[matboard_overlap]"]');//from size group - matboard_overlap field
            this.els.op_type        = document.querySelectorAll('input[name*="opadmin-type"]');//from size group - openings type
        };

        /**
         * Set module elements to properties
         */
        TopBot.prototype.setElsEvents = function () {
            let self = this;
            var was_active = true,
                size_type  = {
                    'type_1': 'frame',
                    'type_2': 'graphic'
                };

            if (this.els.width) {
                this.els.width.addEventListener('change', function () {
                    this.update();
                }.bind(this));
            }

            $('button[data-index="header_modal_button"]').on('click',function(){
                updateCoreVars();
                self.update();
            });

            this.els.height.addEventListener('change', function () {
                this.updateVars();
                triggerCustomEvent('changed_height');
                this.update();
            }.bind(this));
            this.els.position.addEventListener('change', this.update.bind(this));

            this.els.font_size_min.addEventListener('change', this.update.bind(this));
            this.els.font_size_def.addEventListener('change', function () {
                this.size_ihn.font.def = +this.els.font_size_step.value;
                this.size_px.font.def  = this.size_ihn.font.step * ratio;
            }.bind(this));
            this.els.font_size_step.addEventListener('change', function () {
                this.size_ihn.font.step = +this.els.font_size_step.value;
                this.size_px.font.step  = this.size_ihn.font.step * ratio;
            }.bind(this));

            this.els.prod_t.addEventListener('change', function () {
                product_options.modules.size.type = size_type['type_' + this.els.prod_t.value];
                updateCoreVars();
                this.update();
            }.bind(this));
            this.els.prod_w.addEventListener('change', function () {
                product_options.size.width.integer = +this.els.prod_w.value;
                product_options.size.width.tenth   = 0;
                this.update();
            }.bind(this));
            this.els.prod_h.addEventListener('change', function () {
                product_options.size.height.integer = +this.els.prod_h.value;
                product_options.size.height.tenth   = 0;
                this.update();
            }.bind(this));
            if (typeof window.OpeningObject !== 'undefined') {
                window.OpeningObject.addListener('changed_mat_size', function () {
                    updateCoreVars();
                    this.update();
                }.bind(this));
                window.OpeningObject.addListener('changed_op_type', function () {
                    updateCoreVars();
                    this.update();
                }.bind(this));
            } else {
                if (this.els.mat_top_int) {
                    this.els.mat_top_int.addEventListener('change', function () {
                        updateCoreVars();
                        this.update();
                    }.bind(this));
                    this.els.mat_top_tenth.addEventListener('change', function () {
                        updateCoreVars();
                        this.update();
                    }.bind(this));
                }
                if (this.els.mat_rev) {
                    this.els.mat_rev.addEventListener('change', function () {
                        updateCoreVars();
                        this.update();
                    }.bind(this));
                }
                // if (this.els.op_type) {
                //     this.els.op_type.addEventListener('change', function () {
                //         updateCoreVars();
                //         this.update();
                //     }.bind(this));
                // }
            }

            this.els.mat_over.addEventListener('change', function () {
                updateCoreVars();
                this.updateVars();
                this.update();
            }.bind(this));
        };

        /**
         * Render view
         */
        TopBot.prototype.renderView = function () {
            var _self = this;

            if (this.fc) {
                this.fc.clear();
                this.fc.setDimensions({
                    width : this.size_px.cs_w,
                    height: this.size_px.cs_h
                });
            } else {
                this.fc = new fabric.Canvas(this.cs, {
                    // backgroundColor: 'red', // @dev
                    width : this.size_px.cs_w,
                    height: this.size_px.cs_h,
                    selection: false
                });

                this.fc.on('object:moving', function (options) {
                    if (!options.target.inst_id && +options.target.inst_id !== 0) {
                        return;
                    }

                    _self.checkLayerSize(options.target);
                    _self.checkLayerPosition(options.target);

                    if (options.target.type === 'text') {
                        _self.setPosToProps(
                            _self.v_controls.$get('list_text[' + options.target.inst_id + ']')
                        );
                    } else {
                        _self.setPosToProps(
                            _self.v_controls.$get('list_images[' + options.target.inst_id + ']')
                        );
                    }
                });
                this.fc.on('object:scaling', function (options) {
                    if (!options.target.inst_id && +options.target.inst_id !== 0) {
                        return;
                    }
                    var v_controls = _self.v_controls;

                    _self.checkLayerSize(options.target);
                    _self.checkLayerPosition(options.target);

                    if (options.target.type === 'text') {
                        _self.setSizeToProps(
                            v_controls.$get('list_text[' + options.target.inst_id + ']')
                        );
                        _self.setPosToProps(
                            v_controls.$get('list_text[' + options.target.inst_id + ']')
                        );
                    } else {
                        _self.setSizeToProps(
                            v_controls.$get('list_images[' + options.target.inst_id + ']')
                        );
                        _self.setPosToProps(
                            v_controls.$get('list_images[' + options.target.inst_id + ']')
                        );
                    }
                });
                this.fc.on('object:selected', function (options) {
                    if (!options.target.inst_id && +options.target.inst_id !== 0) {
                        return;
                    }

                    var v_controls = _self.v_controls;
                    if (options.target.type === 'text') {
                        v_controls.selectTextLayer(
                            v_controls.$get('list_text[' + options.target.inst_id + ']'),
                            options.target.inst_id,
                            true
                        );
                    } else {
                        v_controls.selectImageLayer(
                            v_controls.$get('list_images[' + options.target.inst_id + ']'),
                            options.target.inst_id,
                            true
                        );
                    }
                });
            }

            // add padding layer
            this.fc.add(new fabric.Rect({
                selectable: false,
                width     : this.fc.width,
                height    : this.fc.height,
                top       : 0,
                left      : 0,
                fill      : view_opts.bg
            }));

            this.renderOpenings();

            addGuideLines(this.fc, this.size_px.ratio);

            if (this.name.indexOf('header') !== -1) {
                this.view.bg_fc = new fabric.Rect({
                    selectable: false,
                    width     : this.size_px.width,
                    height    : this.size_px.height,
                    top       : this.size_px.pos.top,
                    left      : this.size_px.pos.left,
                    fill      : this.view.bg_active
                });
                this.fc.add(this.view.bg_fc);
            }

            this.renderCorners();

            this.renderLayers();
        };

        /**
         * Render zone corners
         */
        TopBot.prototype.renderCorners = function () {
            var depth           = size_px.bd_width,
                bd_length       = size_px.bd_length,
                color           = view_opts.bd_clr,
                extreme_points  = this.size_px.pos,
                bd_options      = {
                    fill: 'transparent',
                    stroke: color,
                    strokeWidth: depth,
                    // hasControls: false
                    selectable: false,
                    evented : false
                };

            var shape_corn_view_lt_start_left = extreme_points.left;
            var shape_corn_view_lt_start_top  = extreme_points.top;
            this.view.shape_corn_view_lt = new fabric.Polyline(
                [
                    {
                        x: shape_corn_view_lt_start_left - depth,
                        y: shape_corn_view_lt_start_top - bd_length - depth
                    },
                    {
                        x: shape_corn_view_lt_start_left - depth,
                        y: shape_corn_view_lt_start_top - depth
                    },
                    {
                        x: shape_corn_view_lt_start_left - bd_length - depth,
                        y: shape_corn_view_lt_start_top - depth
                    }
                ],
                bd_options
            );
            var shape_corn_view_rt_start_left = extreme_points.right;
            var shape_corn_view_rt_start_top = extreme_points.top;
            this.view.shape_corn_view_rt = new fabric.Polyline(
                [
                    {
                        x: shape_corn_view_rt_start_left,
                        y: shape_corn_view_rt_start_top - bd_length - depth
                    },
                    {
                        x: shape_corn_view_rt_start_left,
                        y: shape_corn_view_rt_start_top - depth
                    },
                    {
                        x: shape_corn_view_rt_start_left + bd_length,
                        y: shape_corn_view_rt_start_top - depth
                    }
                ],
                bd_options
            );
            var shape_corn_view_rb_start_left = extreme_points.right;
            var shape_corn_view_rb_start_top = extreme_points.bottom;
            this.view.shape_corn_view_rb = new fabric.Polyline(
                [
                    {
                        x: shape_corn_view_rb_start_left,
                        y: shape_corn_view_rb_start_top + bd_length
                    },
                    {
                        x: shape_corn_view_rb_start_left,
                        y: shape_corn_view_rb_start_top
                    },
                    {
                        x: shape_corn_view_rb_start_left + bd_length,
                        y: shape_corn_view_rb_start_top
                    }
                ],
                bd_options
            );
            var shape_corn_view_lb_start_left = extreme_points.left;
            var shape_corn_view_lb_start_top = extreme_points.bottom;
            this.view.shape_corn_view_lb = new fabric.Polyline(
                [
                    {
                        x: shape_corn_view_lb_start_left - depth,
                        y: shape_corn_view_lb_start_top + bd_length
                    },
                    {
                        x: shape_corn_view_lb_start_left - depth,
                        y: shape_corn_view_lb_start_top
                    },
                    {
                        x: shape_corn_view_lb_start_left - bd_length - depth,
                        y: shape_corn_view_lb_start_top
                    }
                ],
                bd_options
            );
            this.fc.add(this.view.shape_corn_view_lt);
            this.fc.add(this.view.shape_corn_view_rt);
            this.fc.add(this.view.shape_corn_view_rb);
            this.fc.add(this.view.shape_corn_view_lb);
        };

        /**
         * Render openings
         */
        TopBot.prototype.renderOpenings = function () {
            var mods    = product_options.modules,
                mod_mat = mods.mat;

            if (!mod_mat) {
                return;
            }

            var openings = mod_mat.openings,
                type     = openings ? openings.type : 'single',
                list     = openings ? openings.list : [];

            if (type === 'single' || list.length < 1) {
                this.fc.add(new fabric.Rect({
                    selectable: false,
                    fill      : view_opts.op_bg,
                    width     : this.fc.width - (this.size_px.op_view.left + this.size_px.op_view.right) - this.size_px.mat.total_rev * 2,
                    height    : this.fc.height - (this.size_px.op_view.top + this.size_px.op_view.bottom) - this.size_px.mat.total_rev * 2,
                    top       : this.size_px.op_view.top + this.size_px.mat.total_rev,
                    left      : this.size_px.op_view.left + this.size_px.mat.total_rev
                }));
            } else {
                this.renderOpeningsMult(list);
            }
        };

        /**
         * Render multiple openings
         */
        TopBot.prototype.renderOpeningsMult = function (op_list) {
            var list  = (typeof window.OpeningObject === 'undefined') ? op_list : window.OpeningObject.exportList(),
                ratio = this.size_px.ratio;

            list.forEach(function (opening) {
                var params = {
                        selectable: false,
                        fill      : view_opts.op_bg,
                        top       : this.size_px.op_view.top
                            + (new Fraction(opening.position_dev.top_inch).valueOf() * ratio),
                        left      : this.size_px.op_view.left
                            + (new Fraction(opening.position_dev.left_inch).valueOf() * ratio)
                    },
                    op_width  = new Fraction(opening.size.width_inch).valueOf() * ratio,
                    op_height = new Fraction(opening.size.height_inch).valueOf() * ratio;

                op_width  = (op_width < 0.1) ? 0.1 : op_width;
                op_height = (op_height < 0.1) ? 0.1 : op_height;

                if (opening.shape === 'circle') {
                    this.fc.add(new fabric.Ellipse(
                        Object.assign({}, params, {
                            rx: op_width / 2,
                            ry: op_height / 2
                        })
                    ));
                } else {
                    this.fc.add(new fabric.Rect(
                        Object.assign({}, params, {
                            width     : op_width,
                            height    : op_height
                        })
                    ));
                }
            }.bind(this));
        };

        /**
         * Update list layers size to px from inches
         * @param {object} layer - layer item
         */
        TopBot.prototype.updateLayerToPxSize = function (layer) {
            var ratio = this.size_px.ratio;

            if (layer.width_inch) {
                layer.width = (new Fraction(layer.width_inch).valueOf() * ratio);
            } else {
                layer.width = 1;
            }
            if (layer.height_inch) {
                layer.height = (new Fraction(layer.height_inch).valueOf() * ratio);
            } else {
                layer.height = 1;
            }
        };

        /**
         * Update list layers position to px from inches
         * @param {object} layer - layer item
         */
        TopBot.prototype.updateLayerToPxPos = function (layer) {
            var ratio          = this.size_px.ratio,
                extreme_points = this.size_px.pos;

            if (layer.dev_top_inch) {
                layer.top = (new Fraction(layer.dev_top_inch).valueOf() * ratio) + extreme_points.top;
            } else {
                layer.top = extreme_points.top;
            }
            if (layer.dev_left_inch) {
                layer.left = (new Fraction(layer.dev_left_inch).valueOf() * ratio) + extreme_points.left;
            } else {
                layer.left = extreme_points.left;
            }
        };

        /**
         * Update list layers values to px from inches
         */
        TopBot.prototype.updateListToPx = function () {
            if (!this.size_px.ratio) {
                return;
            }

            _.each(this.list_dev.images, this.updateLayerToPxSize.bind(this));

            if (!this.size_px.pos) {
                return;
            }

            _.each(this.list_dev.text, function (layer) {
                var layer_font = new Fraction(layer.font_size_inch).valueOf();
                if (layer_font < this.size_ihn.font.min) {
                    layer.font_size_inch = new Fraction(this.size_ihn.font.min).toFraction(true);
                    layer.font_size_points = this.size_ihn.font.min * point_val;
                }
                this.updateLayerToPxPos.call(this, layer);
            }.bind(this));

            _.each(this.list_dev.images, this.updateLayerToPxPos.bind(this));
        };

        /**
         * Render text list layers
         */
        TopBot.prototype.renderTextLayers = function () {
            _.each(this.list_dev.text, this.addLayerTextFc.bind(this));
            this.fc.renderAll();
        };

        /**
         * Render images list layers
         * @param {function} [callback] - callback that will be fire after render all layers
         */
        TopBot.prototype.renderImageLayers = function (callback) {
            var _inst = this,
                img_counter = 0;

            _.each(this.list_dev.images, function (layer, id) {
                if (layer.url) {
                    fabric.Image.fromURL(layer.url, function(img_fc) {
                        layer.fc = img_fc;

                        _inst.addLayerImageFc(layer, id);

                        if (typeof callback === 'function' && ++img_counter >= _inst.list_dev.images.length) {
                            callback();
                        }
                    });
                    return;
                }

                if (typeof callback === 'function' && ++img_counter >= _inst.list_dev.images.length) {
                    callback();
                }
            });
            this.fc.renderAll();
        };

        /**
         * Render layers list
         */
        TopBot.prototype.renderLayers = function () {
            this.updateListToPx();
            this.renderTextLayers();
            this.renderImageLayers();
        };

        /**
         * Set data
         */
        TopBot.prototype.setData = function () {
            var mod_data = product_options.modules[this.name];

            mod_data.size.width       = new Fraction(this.size_ihn.width).toFraction(true);
            mod_data.size.height      = new Fraction(this.size_ihn.height).toFraction(true);
            mod_data.size.height_orig = new Fraction(this.size_ihn.height_orig).toFraction(true);
            mod_data.position         = this.position;
        };

        /**
         * Update own vars
         */
        TopBot.prototype.updateVars = (function () {
            var
                // canvas width in inches
                cs_w_ihn = 1,
                // canvas height in inches
                cs_h_ihn = 1,
                op_view_top_ihn,
                op_view_left_ihn,
                op_view_bottom_ihn,
                op_view_right_ihn,
                mod_height_new_ihn,
                mod_pos,
                op_zone_h,
                op_zone_w,
                call_stack_counter = 0;

            /**
             * Update own vars in inches
             * @param {number} [m_height] - new module height
             */
            var updateVarsInches = function (m_height) {
                var mod_data          = product_options.modules[this.name],
                    mod_pos_old       = mod_data.position,
                    has_width         = this.els.width,
                    width_val         = has_width ? +this.els.width.value : 0,
                    height_ihn_el     = +this.els.height.value,
                    mod_height_ihn    = m_height || height_ihn_el,
                    mod_gap_ihn       = tb_gap_ihn,
                    mod_gap_near_ihn  = tb_gap_near_ihn,
                    mod_gap_total_ihn = mod_gap_ihn + mod_gap_near_ihn,
                    mod_h_total_ihn   = mod_height_ihn + mod_gap_total_ihn,
                    mods     = product_options.modules,
                    mod_mat  = mods.mat,
                    mat_over = +this.els.mat_over.value,
                    op_type  = (mod_mat && mod_mat.openings) ? mod_mat.openings.type : false;

                mod_pos = this.els.position.value;

                mod_height_new_ihn = mod_height_ihn;

                // check mod height
                if (mod_h_total_ihn < size_ihn.mat_top) {
                    size_ihn.side_gaps[mod_pos_old] -= side_gaps_fun_inch[mod_pos_old][this.name]();
                    mod_height_new_ihn = size_ihn.mat_top - mod_gap_total_ihn;
                    size_ihn.side_gaps[mod_pos] += mod_height_new_ihn + mod_gap_total_ihn;
                }

                // set size & position to the data
                this.size_ihn.height      = mod_height_new_ihn;
                this.size_ihn.height_orig = height_ihn_el;
                this.position             = mod_pos;
                this.setData();
                calcTotalSideGapsInch();

                if (has_width) {
                    if (op_type !== 'multiple') {
                        registry.get('index=header_width').visible(false);
                    } else {
                        registry.get('index=header_width').visible(true);
                    }
                }

                // get opening view position points
                op_view_top_ihn    = size_ihn.side_gaps.top > 0 ? size_ihn.side_gaps.top : size_ihn.mat_top;
                op_view_bottom_ihn = size_ihn.side_gaps.bottom > 0 ? size_ihn.side_gaps.bottom : size_ihn.mat_top;
                op_view_left_ihn   = size_ihn.side_gaps.left > 0 ? size_ihn.side_gaps.left : size_ihn.mat_top;
                op_view_right_ihn  = size_ihn.side_gaps.right > 0 ? size_ihn.side_gaps.right : size_ihn.mat_top;

                // get inner frame size
                var prod_w_ihn = getWhollyNum(product_options.size.width),
                    prod_h_ihn = getWhollyNum(product_options.size.height);
                if (size_ihn.size_type === 'frame') {
                    cs_w_ihn = prod_w_ihn;
                    cs_h_ihn = prod_h_ihn;
                } else {
                    cs_w_ihn = prod_w_ihn + ((op_view_left_ihn + op_view_right_ihn) + size_ihn.mat_op * 2);// - mat_over*2;
                    cs_h_ihn = prod_h_ihn + ((op_view_top_ihn + op_view_bottom_ihn) + size_ihn.mat_op * 2);// - mat_over*2;
                }

                // check opening zone size
                op_zone_h = cs_h_ihn - (op_view_top_ihn + op_view_bottom_ihn) - size_ihn.mat_op * 2;
                if (op_zone_h < size_ihn.min_op) {
                    this.showError('prod-height', 'Make height bigger. The view of rendering is not correct');
                    if (++call_stack_counter > 1) {
                        return;
                    }
                    updateVarsInches.call(this, mod_height_new_ihn - (size_ihn.min_op - op_zone_h));
                } else {
                    if (typeof m_height === 'undefined') {
                        this.hideError('prod-height');
                    }
                }
                op_zone_w = cs_w_ihn - (op_view_left_ihn + op_view_right_ihn) - size_ihn.mat_op * 2;
                if (op_zone_w < size_ihn.min_op) {
                    this.showError('prod-width', 'Make width bigger. The view of rendering is not correct');
                } else {
                    this.hideError('prod-width');
                }

                this.size_ihn.side_gaps = tb_gap_ihn;
                // check mod width
                if (has_width && size_ihn.op_type === 'single') {
                    width_val = op_zone_w;
                } else {
                    if (width_val > cs_w_ihn - tb_gap_ihn*2) {
                        width_val = cs_w_ihn - tb_gap_ihn*2;
                    }
                }
                this.size_ihn.width     = has_width ? width_val : cs_w_ihn - tb_gap_ihn*2;
                this.setData();
                var left_gap            = has_width ? (cs_w_ihn - width_val)/2 : this.size_ihn.side_gaps;
                this.size_ihn.pos       = {
                    left : left_gap,
                    right: left_gap + this.size_ihn.width
                };
                if (mod_pos === 'top') {
                    this.size_ihn.pos.top = this.size_ihn.side_gaps;
                } else {
                    this.size_ihn.pos.top = cs_h_ihn - (this.size_ihn.side_gaps + this.size_ihn.height);
                }
                this.size_ihn.pos.bottom  = this.size_ihn.pos.top + this.size_ihn.height;

                this.size_ihn.font.def  = +this.els.font_size_def.value;
                this.size_ihn.font.min  = +this.els.font_size_min.value;
                this.size_ihn.font.step = +this.els.font_size_step.value;
            };

            /**
             * Update own vars in pixels
             */
            var updateVarsPx = function () {
                var ratio = this.size_px.ratio = size_px.cs_max / cs_w_ihn;

                this.size_px.height    = mod_height_new_ihn * ratio;
                this.size_px.width     = this.size_ihn.width * ratio;
                this.size_px.side_gaps = tb_gap_ihn * ratio;
                this.size_px.pos       = {
                    left  : this.size_ihn.pos.left * ratio,
                    right : this.size_ihn.pos.right * ratio,
                    top   : this.size_ihn.pos.top * ratio,
                    bottom: this.size_ihn.pos.bottom * ratio
                };

                this.size_px.cs_w = size_px.cs_max;
                this.size_px.cs_h = cs_h_ihn * ratio;

                this.size_px.mat = {
                    top      : size_ihn.mat_top * ratio,
                    total_rev: size_ihn.mat_op * ratio
                };

                this.size_px.op_view = {
                    top     : op_view_top_ihn * ratio,
                    left    : op_view_left_ihn * ratio,
                    bottom  : op_view_bottom_ihn * ratio,
                    right   : op_view_right_ihn * ratio
                };

                this.size_px.font.def  = this.size_ihn.font.def * ratio;
                this.size_px.font.min  = this.size_ihn.font.min * ratio;
                this.size_px.font.step = this.size_ihn.font.step * ratio;
            };

            return function () {
                var mod_data = product_options.modules[this.name];
                call_stack_counter = 0;
                if (this.name.indexOf('header') === -1) {
                    this.view.bg_active = '#fff';
                } else {
                    this.view.bg_active = mod_data.bg_color_active || mod_data.bg_colors[0] || '#fff';
                }
                updateVarsInches.call(this);
                updateVarsPx.call(this);
            };
        })();

        /**
         * Update render and data
         */
        TopBot.prototype.exportData = function () {
            var mod_data = product_options.modules[this.name];
            mod_data.texts  = _.map(this.v_controls.$get('list_text'), function (layer) {
                layer.text = layer.fc.getText();

                return _.pick(
                    layer,
                    'width_inch',
                    'height_inch',
                    'dev_left_inch',
                    'dev_top_inch',
                    'text',
                    'font',
                    'font_size_inch',
                    'font_size_points',
                    'text_color',
                    'text_align',
                    'font_style'
                );
            });
            mod_data.images = [];
            _.each(this.v_controls.$get('list_images'), function (layer) {
                if (!layer.url) {
                    return;
                }
                mod_data.images.push(
                    _.pick(
                        layer,
                        'url',
                        'width_inch',
                        'height_inch',
                        'dev_top_inch',
                        'dev_left_inch'
                    )
                );
            });
            if (this.name.indexOf('header') !== -1) {
                mod_data.bg_color_active = this.view.bg_active;
            }
        };

        /**
         * Update render and data
         */
        TopBot.prototype.update = function () {
            this.exportData();
            this.updateVars();
            this.renderView();
            var active_text = _.findWhere(this.list_dev.text, {active: true});
            if (active_text) {
                this.selectLayerFc(active_text.fc);
            }
            var active_image = _.findWhere(this.list_dev.images, {active: true});
            if (active_image) {
                this.selectLayerFc(active_image.fc);
            }
            setTimeout(function () {
                this.fc.renderAll();
            }.bind(this), 30);
        };

        /**
         * Show system message
         * @param {string} id - message id
         * @return {boolean}  - returns false if no same messages else false
         */
        TopBot.prototype.checkError = function (id) {
            var list = this.mess.list;

            for (var key in list) {
                if (!list.hasOwnProperty(key)) {
                    continue;
                }
                if (id === key) {
                    return true;
                }
            }

            return false;
        };

        /**
         * Show system message
         * @param {string} id      - message id
         * @param {string} message - text of the message
         */
        TopBot.prototype.showError = function (id, message) {
            if (!mess_list_el) {
                mess_list_el = document.getElementById(mess_list_id);
            }
            if (!this.mess) {
                this.mess = {
                    els: {
                        list: document.createElement('ul')
                    },
                    list: {}
                };

            }

            var there_is_mess = this.checkError(id);

            if (there_is_mess) {
                return;
            }


            var li   = document.createElement('li'),
                text = document.createTextNode(message);

            this.mess.list[id] = li;

            li.appendChild(text);
            this.mess.els.list.appendChild(li);
            li.className = 'error-msg';
            this.mess.els.list.className = 'messages-' + this.name + ' messages';
            // mess_list_el.appendChild(this.mess.els.list);
            uiAlert({
                content: $t(message)
            });

            if (!mod.error) {
                mod.error = true;
            }
        };

        /**
         * Show system message
         * @param {string} id - message id
         */
        TopBot.prototype.hideError = function (id) {
            if (this.mess && this.mess.list[id]) {
                this.mess.list[id].remove();
                delete this.mess.list[id];
            }
            if (mod.error && !_.keys(this.mess.list).length) {
                mod.error = false;
            }
        };

        /**
         * Draw gide lines
         */
        var addGuideLines = function (fc, size_ratio) {
            var step_ihn = 1,
                step     = size_ratio * step_ihn,
                line_w   = 2,
                lines_count_w = Math.round(fc.width / step),
                lines_count_h = Math.round(fc.height / step),
                lines_count_i = 1,
                line_options  = {
                    selectable: false,
                    fill      : 'red',
                    opacity   : 0.1
                },
                text_options = {
                    selectable: false,
                    fontSize  : 10,
                    fontFamily: 'Arial',
                    fill      : '#8B8B8B'
                };

            // draw vertical lines
            for (; lines_count_i <= lines_count_w; lines_count_i += 1) {
                fc.add(new fabric.Rect(
                    Object.assign({}, line_options, {
                        width : line_w,
                        height: fc.height,
                        top   : 0,
                        left  : lines_count_i * step
                    })
                ));
                fc.add(new fabric.Text(
                    '' + (lines_count_i - 1),
                    Object.assign({}, text_options, {
                        top : 1,
                        left: (lines_count_i - 1) * step + 2
                    })
                ));
            }
            // draw horizontal lines
            for (lines_count_i = 1; lines_count_i <= lines_count_h; lines_count_i += 1) {
                fc.add(new fabric.Rect(
                    Object.assign({}, line_options, {
                        width   : fc.width,
                        height  : line_w,
                        top     : lines_count_i * step,
                        left    : 0
                    })
                ));
                if (lines_count_i === 1) {
                    continue;
                }
                fc.add(new fabric.Text(
                    '' + (lines_count_i - 1),
                    Object.assign({}, text_options, {
                        top : (lines_count_i - 1) * step + 1,
                        left: 2
                    })
                ));
            }
        };

        /**
         * Get whole number from object
         * @param {object} num_sep - number object
         * @param {number|string} num_sep.integer - integer part
         * @param {number|string} [num_sep.tenth] - fractional part
         */
        var getWhollyNum = function (num_sep) {
            return new Fraction(
                num_sep.integer +
                ((num_sep.tenth && +num_sep.tenth !== 0) ? ' ' + num_sep.tenth : '')
            ).valueOf();
        };

        var calcTotalSideGapsInch = function () {
            _.each(side_gaps_fun_inch, function (mod_list, side_name) {
                var result = 0;
                _.each(mod_list, function (func) {
                    if (typeof func === 'function') {
                        result += func();
                    }
                });
                size_ihn.side_gaps[side_name] = result;
            });
        };

        /**
         * Load fonts
         * @param {NodeList} elements - elements with attribute data-mod
         * @param {function} [onLoad] - callback when fonts are loaded
         */
        var loadFontList = function (elements, onLoad) {
            var mod_name    = elements[0].getAttribute('data-mod'),
                font_list   = product_options.modules[mod_name].fonts,
                list_length = font_list.length,
                counter     = 0;
            // $('body').trigger('processStart');
            _.each(font_list, function (font_name) {
                loadFont(font_name, function() {
                    if (++counter >= list_length && typeof onLoad === 'function') {
                        // $('body').trigger('processStart');
                        onLoad();
                    }
                });
            }.bind(this));
            // $('body').trigger('processStop');
        };

        /**
         * @param {string} font_name  - callback when fonts are loaded
         * @param {function} [onLoad] - callback when fonts are loaded
         */
        var loadFont = function (font_name, onLoad) {
            var font_name_ent = font_name + ':400,400i,700,700i';

            WebFont.load({
                google: {
                    families: [font_name_ent]
                },
                active: function() {
                    if (typeof onLoad === 'function') {
                        onLoad();
                    }
                },
                inactive: function () {
                    if (typeof onLoad === 'function') {
                        onLoad();
                    }
                }
            });
        };

        /**
         * Update core variables
         */
        var updateCoreVars = (function () {
            /**
             * Update core variables with inches to actual
             */
            var updateCoreInches = function () {
                var mods = product_options.modules;

                size_ihn.size_type = mods.size ? mods.size.type : 'frame';

                calcTotalSideGapsInch();

                // get mat sizes
                var mod_mat   = mods.mat,
                    m_parts   = mod_mat ? _.keys(mod_mat.active_items) : [],
                    mat_sizes_inch  = (mod_mat && mod_mat.sizes) ? mod_mat.sizes : {},
                    m_size_top_inch = mat_sizes_inch.top || {
                        integer: 0
                    },
                    m_size_reveal_inch,
                    m_size_reveal_ihn,
                    openings = mod_mat ? mod_mat.openings : false;

                size_ihn.op_type = openings ? openings.type : 'single';

                size_ihn.mat_top = getWhollyNum(m_size_top_inch);

                if (m_parts.length > 1) {
                    m_size_reveal_inch = mat_sizes_inch.reveal || 0;
                    m_size_reveal_ihn  = new Fraction(m_size_reveal_inch).valueOf();
                    size_ihn.mat_op    = m_size_reveal_ihn * (m_parts.length - 1);
                } else {
                    size_ihn.mat_op = 0;
                }
            };

            return function () {
                updateCoreInches();
            };
        }());

        /**
         * Trigger custom event
         */
        var triggerCustomEvent = function (name) {
            if (!events[name]) {
                return;
            }
            events[name].forEach(function (func) {
                func();
            });
        };

        mod.init = function () {
            var cs_elements = document.querySelectorAll('.topbot-render');

            if (!cs_elements) {
                return;
            }

            product_options = productJson;

            updateCoreVars();
            // $('body').trigger('processStart');
            loadFontList(cs_elements, function () {
                [].forEach.call(cs_elements, function (canvas) {
                    insts.push(new TopBot(canvas));
                });
            });
            // $('body').trigger('processStop');
        };

        /**
         * Export data to the product JSON
         */
        mod.exportData = function () {
            insts.forEach(function (module_inst) {
                module_inst.exportData();
            });
        };

        /**
         * Add listener
         * @param {string} name - event name
         * @param {function} func - function listener
         */
        mod.addListener = function (name, func) {
            if (!events[name]) {
                events[name] = [];
            }

            events[name].push(func);
        };

        return mod;
    })();
    $.widget('custom.headerVue', {
        _create: function() {
            this.setHeaderUiData();
            DRAW_TOP_BOT.init();
            window.headerObjectData = DRAW_TOP_BOT;
        },
        setHeaderUiData:function(){
            let UiData = window.productJson.modules.crheader.UiData;
            registry.get('index=header_position').value(UiData.position);
            registry.get('index=header_height').value(UiData.height);
            if (UiData.width!==undefined) {
                registry.get('index=header_width').value(UiData.width);
            }
            registry.get('index=font_size_min').value(UiData.minimal_font_size);
            registry.get('index=font_size_step').value(UiData.font_size_step);
            registry.get('index=font_size_default').value(UiData.default_font_size);
            return;
        }
    });
    return $.custom.headerVue;
});
