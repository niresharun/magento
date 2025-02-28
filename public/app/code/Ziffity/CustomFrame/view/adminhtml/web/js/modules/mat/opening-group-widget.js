define([
    "jquery",
    'Ziffity_CustomFrame/js/lib/fraction.min',
    'Ziffity_CustomFrame/js/lib/vue',
    'Ziffity_CustomFrame/js/lib/vue-resource',
    'underscore',
    'Ziffity_CustomFrame/js/lib/fabric.min',
    'jquery/ui'
], function($,Fraction, Vue, VueResource,_) {
    "use strict";
    Vue.use(VueResource);
    let OPENINGS_ADMIN = (function (Vue, _, Fraction) {
        var
            // module of Openings Admin
            mod  = {},
            // controls component id
            controlsId = 'opadmin-controls',
            // vue instance of the controls component
            v_controls,
            // type controls component id
            typeControlsId = 'opadmin-switcher',
            // vue instance of the type controls
            v_type,
            // vue instance of openings list
            v_list,
            // product options
            product_options,
            // how many px in 1 inch
            size_ratio = 0,
            // product size in inches, number view
            size_prod_ihn = {
                width: 0,
                height: 0
            },
            // view size in inches, number view
            size_view_ihn = {
                width: 0,
                height: 0
            },
            // inner frame size in inches, fractional view
            size_inner_frame_inch = {
                width: '0',
                height: '0'
            },
            // view size in px
            size_view = {
                width: 0,
                height: 0
            },
            // total mats size that added to opening in inches, number view
            size_mat_op_ihn,
            // total mats size that added to opening in px
            size_mat_op,
            // top mat size in inches, number view
            size_mat_top_ihn,
            // top mat size in px
            size_mat_top,
            // canvas for drawing id
            csId = 'opadmin-canvas',
            // canvas for drawing
            cs,
            // fabric instance for drawing
            fc,
            // max canvas size in px
            size_max          = 1000,
            // canvas padding size in px
            size_padding      = 40,
            // background color for canvas padding layer
            bg_padding        = '#DDDDDD',
            // minimal distance between openings in inches, number view
            min_dist_between_ihn = 1,
            // minimal distance between openings in pixels
            min_dist_between = 10,
            // maximum right line
            max_pos_right  = 0,
            // maximum bottom line
            max_pos_bottom = 0,
            // default positions for opening in inches, fractional view
            default_position_op_inch = 0,
            // default size for opening in inches, fractional view
            default_size_op_inch     = 3,
            // minimal size for opening in inches, fractional view
            size_min_op_inch   = '3',
            // minimal size for opening in inches, number view
            size_min_op_ihn   = new Fraction(size_min_op_inch).valueOf(),
            // minimal size for opening in px, not including mats
            size_min_op = 300,
            // minimal size for opening in px, not including mats
            size_max_op_clean = {
                width : size_max - size_padding * 2,
                height: size_max - size_padding * 2
            },
            // default shape for opening
            default_shape     = 'rectangle',
            // change step of values in inches, fractional view
            change_step_inch  = '1/16',
            // change step of values in inches, number view
            change_step_ihn   = new Fraction(change_step_inch).valueOf() * 1.1,
            // background color for opening
            bg_opening        = '#ffffff',
            // default options for shapes
            default_fc_sets   = {
                hasRotatingPoint: false,
                lockRotation: true,
                lockSkewingX: true,
                lockSkewingY: true,
                lockScalingFlip: true,
                minScaleLimit: 50,
                top : size_padding,
                left: size_padding,
                fill: bg_opening
            },
            // default options for rectangle shape
            default_fc_sets_rect   = _.extendOwn({}, default_fc_sets),
            // default options for circle shape
            default_fc_sets_circle = _.extendOwn({}, default_fc_sets),
            // counter for openings id, just for dev
            counter_id    = 0,
            fraction_list = [
                {
                    text: 0,
                    val: 0
                },
                {
                    text: '1/8',
                    val: new Fraction('1/8').valueOf()
                },
                {
                    text: '1/4',
                    val: new Fraction('1/4').valueOf()
                },
                {
                    text: '3/8',
                    val: new Fraction('3/8').valueOf()
                },
                {
                    text: '1/2',
                    val: new Fraction('1/2').valueOf()
                },
                {
                    text: '5/8',
                    val: new Fraction('5/8').valueOf()
                },
                {
                    text: '3/4',
                    val: new Fraction('3/4').valueOf()
                },
                {
                    text: '7/8',
                    val: new Fraction('7/8').valueOf()
                },
                {
                    text: 1,
                    val: 1
                }
            ],
            area_bd_width = 3,
            area_bd_dash  = [12, 6],
            area_bd_color = '#1F4A6B',
            mats_sizes        = null,
            mats_sizes_active = null,
            tmp_error         = null,
            tb_gap_ihn      = new Fraction('1 1/2').valueOf(),
            tb_gap_near_ihn = new Fraction('1 1/2').valueOf(),
            // gaps from the every side that added by modules with function
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
            // gaps from the every side in sum, number view
            side_gaps_ihn = {
                top: 0,
                bottom: 0,
                left: 0,
                right: 0
            },
            // gaps from the every side in sum, pixels
            side_gaps = {
                top: 0,
                bottom: 0,
                left: 0,
                right: 0
            },
            pos_total_top_ihn,
            pos_total_left_ihn,
            events = {},
            keys = {
                clip: 'user_img'
            };

        default_fc_sets_rect.width  = size_min_op;
        default_fc_sets_rect.height = size_min_op;

        default_fc_sets_circle.rx = size_min_op / 2;
        default_fc_sets_circle.ry = size_min_op / 2;

        /**
         * Create url from object
         * @param {object} object - object for converting
         * @return {string} - {@link Blob} url
         */
        var createObjectURL = function (object) {
            var urlCreator  = window.URL || window.webkitURL;

            return urlCreator.createObjectURL(object);
        };

        var calcTotalSideGapsInch = function () {
            _.each(side_gaps_fun_inch, function (mod_list, side_name) {
                var result = 0;
                _.each(mod_list, function (func) {
                    if (typeof func === 'function') {
                        result += func();
                    }
                });
                side_gaps_ihn[side_name] = result;
            });
        };

        /**
         * Round number
         * @param {number} num - number
         * @return {number} - rounded number
         */
        var roundNumber = function (num) {
            return Math.round(num * 100) / 100;
        };

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
                    result = (frac_new_f === 0 || frac_new_f === 1) ? (integer + frac_new_f) : integer + ' ' + frac_new_f;
                } else {
                    result = frac_new_f;
                }
                return result;
            }

            return frac_view;
        };

        /**
         * Update core variables with inches to actual
         */
        var updateCoreInches = function () {
            var mods = product_options.modules;

            calcTotalSideGapsInch();

            // get product size
            var option_size_w = product_options.size.width,
                option_size_h = product_options.size.height;
            /*var option_size_w = {
                    integer: 15
                }, // @dev
                option_size_h = {
                    integer: 15
                }; // @dev*/
            size_prod_ihn.width = new Fraction(
                option_size_w.integer +
                (option_size_w.tenth ? ' ' + option_size_w.tenth : '')
            ).valueOf();
            size_prod_ihn.height = new Fraction(
                option_size_h.integer +
                (option_size_h.tenth ? ' ' + option_size_h.tenth : '')
            ).valueOf();

            // get mat sizes
            var mod_mat            = mods.mat,
                m_parts            = mod_mat ? _.keys(mod_mat.active_items) : [],
                op_type            = (mod_mat && mod_mat.openings) ? mod_mat.openings.type : 'single',
                mat_sizes          = (mod_mat && mod_mat.sizes) ? mod_mat.sizes : {},
                m_size_top_inch    = mat_sizes.top || {
                    integer: 0
                },
                // m_size_top_inch    = {
                //     integer: 2
                // }, // @dev
                m_size_reveal_inch,
                m_size_reveal_ihn;

            size_mat_top_ihn = new Fraction(
                m_size_top_inch.integer +
                ((m_size_top_inch.tenth && +m_size_top_inch.tenth !== 0) ? ' ' + m_size_top_inch.tenth : '')
            ).valueOf();

            if (m_parts.length > 1) {
                m_size_reveal_inch = mat_sizes.reveal || 0;
                m_size_reveal_ihn  = new Fraction(m_size_reveal_inch).valueOf();
                size_mat_op_ihn    = m_size_reveal_ihn * (m_parts.length - 1);
            } else {
                size_mat_op_ihn = 0;
            }

            default_position_op_inch = size_mat_op_ihn;

            pos_total_top_ihn = side_gaps_ihn.top > 0 ? side_gaps_ihn.top : size_mat_top_ihn;
            var total_bottom_ihn = side_gaps_ihn.bottom > 0 ? side_gaps_ihn.bottom : size_mat_top_ihn;
            pos_total_left_ihn = side_gaps_ihn.left > 0 ? side_gaps_ihn.left : size_mat_top_ihn;
            var total_right_ihn = side_gaps_ihn.right > 0 ? side_gaps_ihn.right : size_mat_top_ihn;

            // get view area size
            if (mods.size.type === 'graphic') {
                size_view_ihn.width  = size_prod_ihn.width + ( (op_type === 'single') ? size_mat_op_ihn * 2 : 0 );
                size_view_ihn.height = size_prod_ihn.height + ( (op_type === 'single') ? size_mat_op_ihn * 2 : 0 );
                size_inner_frame_inch.width = new Fraction(
                    size_prod_ihn.width + (pos_total_left_ihn + total_right_ihn) + size_mat_op_ihn * 2
                ).toFraction(true);
                size_inner_frame_inch.height = new Fraction(
                    size_prod_ihn.height + (pos_total_top_ihn + total_bottom_ihn) + size_mat_op_ihn * 2
                ).toFraction(true);
            } else {
                size_view_ihn.width  = size_prod_ihn.width - (pos_total_left_ihn + total_right_ihn);
                size_view_ihn.height = size_prod_ihn.height - (pos_total_top_ihn + total_bottom_ihn);
                size_inner_frame_inch.width = new Fraction(size_prod_ihn.width).toFraction(true);
                size_inner_frame_inch.height = new Fraction(size_prod_ihn.height).toFraction(true);
            }
        };

        /**
         * Update core variables with px to actual
         */
        var updateCorePx = function () {
            if (size_view_ihn.width > size_view_ihn.height) {
                size_view.width  = size_max * (size_view_ihn.width / (size_view_ihn.width + size_mat_top_ihn * 2));
                size_view.height = size_view_ihn.height / size_view_ihn.width * size_view.width;
            } else {
                size_view.height = size_max * (size_view_ihn.height / (size_view_ihn.height + size_mat_top_ihn * 2));
                size_view.width  = size_view_ihn.width / size_view_ihn.height * size_view.height;
            }

            // update ratio size
            size_ratio = size_view.width / size_view_ihn.width;

            _.each(side_gaps_ihn, function (gap_val, side_name) {
                side_gaps[side_name] = gap_val * size_ratio;
            });

            // update mats size in px
            size_mat_op = size_mat_op_ihn * size_ratio;

            size_mat_top = size_mat_top_ihn * size_ratio;

            size_padding = size_mat_top;

            // update maximum opening size in px without mats
            size_max_op_clean.width  = size_view.width - size_mat_op * 2;
            size_max_op_clean.height = size_view.height - size_mat_op * 2;

            default_fc_sets.top = size_padding + size_mat_op;
            default_fc_sets.left = size_padding + size_mat_op;
            // default options for rectangle shape
            default_fc_sets_rect.top = default_fc_sets.top;
            default_fc_sets_rect.left = default_fc_sets.left;
            // default options for rectangle shape
            default_fc_sets_circle.top = default_fc_sets.top;
            default_fc_sets_circle.left = default_fc_sets.left;

            // maximum lines
            max_pos_right  = size_padding + size_view.width - size_mat_op;
            max_pos_bottom = size_padding + size_view.height - size_mat_op;

            min_dist_between = min_dist_between_ihn * size_ratio;

            // update minimal opening size in px
            size_min_op = size_min_op_ihn * size_ratio;
            if (size_min_op > default_fc_sets_rect.width) {
                default_fc_sets_rect.width = size_min_op;
                default_fc_sets_rect.height = size_min_op;

                default_fc_sets_circle.rx = size_min_op / 2;
                default_fc_sets_circle.ry = size_min_op / 2;

                default_size_op_inch = size_min_op_inch + size_mat_op_ihn * 2;
            } else {
                default_size_op_inch = new Fraction(
                    (default_fc_sets_rect.width / size_ratio) + (size_mat_op_ihn * 2)
                ).toFraction(true);
            }
        };

        /**
         * Update core variables to actual
         */
        var updateCoreVars = function () {
            product_options = window.productJson;

            var mats_mod = product_options.modules.mat;
            if (!mats_mod.openings.list) {
                mats_mod.openings.list = [
                    {
                        shape: 'rectangle'
                    }
                ];
            }

            updateCoreInches();
            updateCorePx();
        };

        /**
         * Draw gide lines
         */
        var addGuideLines = function () {
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
         * Initialize render functionality
         */
        var renderCanvas = function () {
            if (fc) {
                fc.clear();
                fc.setDimensions({
                    width : size_view.width + size_padding * 2,
                    height: size_view.height + size_padding * 2
                });
            } else {
                cs = document.getElementById(csId);
                fc = new fabric.Canvas(cs, {
                    // backgroundColor: 'red', // @dev
                    width : size_view.width + size_padding * 2,
                    height: size_view.height + size_padding * 2,
                    selection: false
                });

                addHandlesRenderEvents();
            }

            // add padding layer
            fc.add(new fabric.Rect({
                selectable: false,
                width   : fc.width,
                height  : fc.height,
                top     : 0,
                left    : 0,
                fill    : bg_padding
            }));

            addGuideLines();

            // add dotted layer
            fc.add(new fabric.Rect({
                selectable      : false,
                strokeWidth     : area_bd_width,
                stroke          : area_bd_color,
                strokeDashArray : area_bd_dash,
                evented         : false,
                width           : fc.width - size_padding * 2,
                height          : fc.height - size_padding * 2,
                top             : size_padding - 1.5,
                left            : size_padding - 1.5,
                fill            : 'transparent'
            }));
        };

        /**
         * Initialize core functionality
         */
        var initTools = function () {
            var mod_mat = product_options.modules.mat,
                prod_t  = document.querySelector('select[name*="product[size_type]"]'),
                type    = (product_options.modules.mat && product_options.modules.mat.openings)
                    ? product_options.modules.mat.openings.type : 'single';
            v_type = new Vue({
                name: 'Openings type controls',
                el: '#' + typeControlsId,
                data: {
                    type: type
                },
                methods: {
                    changeType: function (type) {
                        var mods = product_options.modules;

                        if (product_options.modules.mat) {
                            product_options.modules.mat.openings.type = type;
                        }

                        if (type === 'single') {
                            v_controls.$set('is_hidden', true);
                            tmp_error = mod.error;
                            mod.error = false;

                            v_controls.addOpeningSingle();

                        } else {
                            prod_t.querySelector('[value="1"]').selected = true;
                            mods.size.type = 'frame';

                            v_controls.$set('is_hidden', false);
                            if (tmp_error !== null) {
                                mod.error = tmp_error;
                                tmp_error = null;
                            }
                            _.each(v_list, function (opening) {
                                if (opening) {
                                    v_controls.removeOpening(opening);
                                }
                            });
                            if (v_list.length > 0) {
                                v_controls.removeOpening(v_list[0]);
                            }
                        }

                        v_controls.$set('show_lock', type === 'single');

                        triggerCustomEvent('changed_op_type');
                    }
                }
            });

            // init controls
            v_controls = new Vue({
                name: 'Multiple openings controls',
                el: '#' + controlsId,
                data: {
                    show_lock : type === 'single',
                    lock_sizes: (typeof mod_mat.sizes_lock === 'undefined') ? false : mod_mat.sizes_lock,
                    is_hidden: mod_mat ? mod_mat.openings.type === 'single' : true,
                    inner_frame_width: size_inner_frame_inch.width,
                    inner_frame_height: size_inner_frame_inch.height,
                    list: [],
                    single_shape: (mod_mat.openings.type === 'single' && mod_mat.openings.list && mod_mat.openings.list.length > 0)
                        ? mod_mat.openings.list[0].shape : 'rectangle',
                    mats_sizes: mats_sizes,
                    mats_sizes_active: mats_sizes_active,
                    reveals_there: mod_mat && (typeof mod_mat.active_items.middle !== 'undefined' ||
                        typeof mod_mat.active_items.bottom !== 'undefined')
                },
                ready: function () {
                    this.$watch(
                        'mats_sizes_active',
                        function () {
                            var mat_top_partly = this.$get('mats_sizes_active.top'),
                                mat_top = new Fraction(mat_top_partly.integer).valueOf() +
                                    (
                                        (mat_top_partly.tenth && +mat_top_partly.tenth !== 0)
                                            ? new Fraction(mat_top_partly.tenth).valueOf() : 0
                                    );

                            if (mat_top < 1) {
                                this.$set('mats_sizes_active.top.integer', '1');
                                this.$set('mats_sizes_active.top.tenth', '0');
                            }
                            if (mat_top > 12) {
                                this.$set('mats_sizes_active.top.integer', '12');
                                this.$set('mats_sizes_active.top.tenth', '0');
                            }
                            updateDataRender();
                            triggerCustomEvent('changed_mat_size');
                            mod.updateRender();
                            checkMatOverlap();
                        },
                        {
                            deep: true
                        }
                    );
                },
                compiled: function () {
                    v_list = this.$get('list');
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
                        var v_list = this.$get('list');

                        _.each(v_list, function (opening, index) {
                            opening.name_id = index + 1;
                            opening.name    = 'Opening ' + opening.name_id;
                        });
                    },
                    addOpeningSingle: function (opening_opt) {
                        var v_controls = this;

                        _.each(v_list, function (opening) {
                            if (opening) {
                                v_controls.removeOpening(opening);
                            }
                        });
                        if (v_list.length > 0) {
                            v_controls.removeOpening(v_list[0]);
                        }
                        v_controls.addOpening(false, {
                            shape      : (opening_opt && opening_opt.shape) ? opening_opt.shape : 'rectangle',
                            width_inch : size_max_op_clean.width / size_ratio,
                            height_inch: size_max_op_clean.height / size_ratio,
                            fc_options : {
                                scaleX: size_max_op_clean.width / default_fc_sets_rect.width,
                                scaleY: size_max_op_clean.height / default_fc_sets_rect.height,
                                selactable: false,
                                evented   : false
                            },
                            img: (opening_opt && opening_opt.img) ? opening_opt.img : false
                        });
                    },
                    /**
                     * Add opening
                     * @param {object} [opening] - opening options
                     * @param {object} [options] - other options
                     * @param {object} [options.shape] - opening shape
                     * @param {object} [options.width_inch] - width in inches
                     * @param {object} [options.height_inch] - height in inches
                     * @param {object} [options.fc_options]  - fabric options for opening
                     * @param {object} [options.img] - image options
                     */
                    addOpening: function (opening, options) {
                        var v_list = this.$get('list'),
                            shape        = (options && options.shape) ? options.shape : 'rectangle',
                            width_inch   = (options && options.width_inch !== undefined) ?
                                options.width_inch : default_size_op_inch,
                            height_inch  = (options && options.width_inch !== undefined) ?
                                options.width_inch : default_size_op_inch,
                            fc_sets_rect = _.extendOwn(
                                {},
                                default_fc_sets_rect,
                                ((options && options.fc_options) ? options.fc_options : {})
                            ),
                            img_options = (options && options.img) ? options.img : false,
                            new_opening;

                        new_opening = {
                            // for export
                            position: {
                                top_inch : default_position_op_inch,
                                left_inch: default_position_op_inch
                            },
                            position_dev: {
                                top_inch : 0,
                                left_inch: 0
                            },
                            size    : {
                                width_inch : width_inch,
                                height_inch: height_inch
                            },
                            shape   : shape,
                            name    : '',

                            // for develop
                            name_id : '',
                            id      : counter_id++,
                            active  : true,
                            fc      : false,
                            img     : {},
                            img_controls: false
                        };

                        if (opening && opening.size.width_inch !== undefined) {
                            new_opening = _.extend(new_opening, opening);

                            if (new_opening.shape === 'circle') {
                                new_opening.fc = new fabric.Ellipse(default_fc_sets_circle);
                            } else {
                                new_opening.fc = new fabric.Rect(default_fc_sets_rect);
                            }

                            new_opening.size.width_inch = convertToCorrectFraction(new Fraction(opening.size.width_inch));
                            new_opening.size.height_inch = convertToCorrectFraction(new Fraction(opening.size.height_inch));
                            new_opening.fc.set(
                                'scaleX',
                                ((new Fraction(opening.size.width_inch).valueOf() * size_ratio)) / new_opening.fc.width
                            );
                            new_opening.fc.set(
                                'scaleY',
                                ((new Fraction(opening.size.height_inch).valueOf() * size_ratio)) / new_opening.fc.width
                            );

                            new_opening.position.top_inch = convertToCorrectFraction(new Fraction(opening.position.top_inch));
                            new_opening.position.left_inch = convertToCorrectFraction(new Fraction(opening.position.left_inch));

                            new_opening.position_dev.top_inch = convertToCorrectFraction(
                                new Fraction(new_opening.position_dev.top_inch || default_position_op_inch)
                            );
                            new_opening.position_dev.left_inch = convertToCorrectFraction(
                                new Fraction(new_opening.position_dev.left_inch || default_position_op_inch)
                            );
                            new_opening.fc.set(
                                'top',
                                (new Fraction(new_opening.position_dev.top_inch || default_position_op_inch).valueOf() * size_ratio) + size_padding
                            );
                            new_opening.fc.set(
                                'left',
                                (new Fraction(new_opening.position_dev.left_inch || default_position_op_inch).valueOf() * size_ratio) + size_padding
                            );
                        } else {
                            if (shape === 'circle') {
                                fc_sets_rect.rx = fc_sets_rect.width / 2;
                                fc_sets_rect.ry = fc_sets_rect.height / 2;
                                new_opening.fc = new fabric.Ellipse(fc_sets_rect);
                            } else {
                                new_opening.fc = new fabric.Rect(fc_sets_rect);
                            }
                        }

                        new_opening.fc.op_id = new_opening.id;

                        v_list.$set(v_list.length, new_opening);

                        fc.add(new_opening.fc);

                        checkSize({
                            target: new_opening.fc
                        });
                        checkPosition({
                            target: new_opening.fc
                        });

                        this.updateNames();

                        // add shape for image clipping
                        this.addClipShape(new_opening);

                        if (img_options) {
                            new_opening.img = img_options;
                        }

                        if (new_opening.img.url) {
                            new_opening.img.top = new Fraction(new_opening.img.top_inch || 0).valueOf() * size_ratio;
                            new_opening.img.left = new Fraction(new_opening.img.left_inch || 0).valueOf() * size_ratio;
                            addImage(new_opening, function () {
                                fixImgOpPos(new_opening, 'top', {
                                    new_val_rel: new_opening.img.top
                                });
                                fixImgOpPos(new_opening, 'left', {
                                    new_val_rel: new_opening.img.left
                                });
                            });
                        }

                        this.selectOpening(new_opening);
                    },
                    removeOpening: function (opening) {
                        var v_list = this.$get('list');

                        if (opening.active && v_list.length > 1) {
                            this.selectOpening(v_list[v_list.length - 2]);
                        }

                        if (opening.img && opening.img.fc) {
                            fc.remove(opening.img.fc);
                            fc.remove(opening.shape_clip);
                        }
                        fc.remove(opening.fc);
                        v_list.$remove(opening);

                        this.updateNames();
                    },
                    selectOpening: function (new_opening, old_opening) {
                        if (!new_opening) {
                            return;
                        }

                        var v_list      = this.$get('list'),
                            selected_fc;

                        if (old_opening) {
                            old_opening.active = false;
                        } else {
                            old_opening = _.findWhere(v_list, {
                                active : true
                            });
                            if (old_opening) {
                                old_opening.active = false;
                            }
                        }

                        new_opening.active = true;

                        selected_fc = fc.getActiveObject();
                        if (selected_fc && selected_fc !== new_opening.fc) {
                            fc.discardActiveObject();
                        }
                        if (selected_fc !== new_opening.fc && new_opening.fc.selactable) {
                            fc.setActiveObject(new_opening.fc);
                        }
                    },
                    /**
                     * Set new position to opening options
                     * @param {object} opening - opening with options
                     * @param {string} direction - 'top' or 'left'
                     * @param {string} new_position - new position value in inches
                     */
                    setPosition: function (opening, direction, new_position) {
                        opening.position_dev[direction + '_inch'] = new_position;
                        opening.position[direction + '_inch'] = new Fraction(
                            new Fraction(new_position).valueOf()
                            + (direction === 'top' ? pos_total_top_ihn : pos_total_left_ihn)
                        ).toFraction(true);
                        fc.renderAll();
                        opening.fc.setCoords();
                    },
                    /**
                     * Set new position to opening fabric instance
                     * @param {object} opening - opening with options
                     * @param {string} direction - 'top' or 'left'
                     * @param {number} new_position - new position value in px
                     *                                for opening fabric instance
                     */
                    setPositionFc: function (opening, direction, new_position) {
                        opening.fc.set(direction, new_position);
                        // fc.renderAll();
                        checkPosition({
                            target: opening.fc
                        });
                    },
                    /**
                     * update position of the opening by {@link change_step_ihn}
                     * @param {object} opening - opening with options
                     * @param {string} direction - 'top' or 'left'
                     * @param {boolean} [increase] - if true than increase by step else decrease
                     */
                    changePositionByStep: function (opening, direction, increase) {
                        var current_val = new Fraction(opening.position_dev[direction + '_inch']).valueOf(),
                            result_ihn  = increase ? current_val + change_step_ihn : current_val - change_step_ihn,
                            result      = result_ihn * size_ratio;

                        this.selectOpening(opening);

                        this.setPositionFc(opening, direction, size_padding + result);

                        followOpPartsFc({
                            target: opening.fc
                        });
                        correctImgOpPos({
                            target: opening.fc
                        });

                        fc.renderAll();
                    },
                    increasePosition: function (opening, direction) {
                        this.changePositionByStep(opening, direction, true);
                    },
                    decreasePosition: function (opening, direction) {
                        this.changePositionByStep(opening, direction);
                    },
                    /**
                     * Set new position to opening options
                     * @param {object} opening   - opening with options
                     * @param {string} direction - 'width' or 'height'
                     * @param {string} new_size  - new position value in inches
                     */
                    setSize: function (opening, direction, new_size) {
                        opening.size[direction + '_inch'] = new_size;
                        fc.renderAll();
                    },
                    /**
                     * Set new position to opening fabric instance
                     * @param {object} opening   - opening with options
                     * @param {string} direction - 'width' or 'height'
                     * @param {number} new_size  - new position value in px
                     *                                for opening fabric instance
                     */
                    setSizeFc: function (opening, direction, new_size) {
                        var translates  = {
                                'height': 'scaleY',
                                'width' : 'scaleX'
                            },
                            real_prop   = translates[direction],
                            scale       = new_size / opening.fc[direction];

                        opening.fc.set(real_prop, scale);

                        checkSize({
                            target: opening.fc
                        });
                        checkPosition({
                            target: opening.fc
                        });
                    },
                    /**
                     * update size of the opening by {@link change_step_ihn},
                     * but do not change opening fabric instance
                     * @param {object} opening - opening with options
                     * @param {string} direction - 'width' or 'height'
                     * @param {boolean} [increase] - if true than increase by step else decrease
                     */
                    changeSizeByStep: function (opening, direction, increase) {
                        var current_val = new Fraction(opening.size[direction + '_inch']).valueOf(),
                            result_ihn  = increase ? (current_val + change_step_ihn) : (current_val - change_step_ihn),
                            result      = result_ihn * size_ratio;

                        this.selectOpening(opening);

                        this.setSizeFc(opening, direction, result);

                        followOpPartsFc({
                            target: opening.fc
                        }, true);

                        fc.renderAll();
                    },
                    increaseSize: function (opening, direction) {
                        this.changeSizeByStep(opening, direction, true);
                    },
                    decreaseSize: function (opening, direction) {
                        this.changeSizeByStep(opening, direction);
                    },
                    addClipShape: function (opening) {
                        var old_shape = opening.shape_clip;

                        opening.shape_clip = opening.fc.clone();

                        var clip_sets = {
                            selectable: false,
                            evented   : false,
                            scaleX    : 1,
                            scaleY    : 1,
                            fill      : 'transparent',
                            // fill      : 'black',
                            // opacity   : 0.5,
                            clipFor   : keys.clip + '-' + opening.id
                        };
                        if (opening.shape === 'circle') {
                            clip_sets.rx = opening.fc.get('rx') * opening.fc.get('scaleX');
                            clip_sets.ry = opening.fc.get('ry') * opening.fc.get('scaleY');
                        } else {
                            clip_sets.width = opening.fc.get('width') * opening.fc.get('scaleX');
                            clip_sets.height = opening.fc.get('height') * opening.fc.get('scaleY');
                        }
                        opening.shape_clip.set(clip_sets);
                        fc.add(opening.shape_clip);

                        if (opening.img && opening.img.fc) {
                            opening.img.fc.set('clipTo', getClipTo(opening, opening.img.fc));
                            fc.moveTo(
                                opening.img.fc,
                                _.indexOf(fc.getObjects(), opening.shape_clip)
                            );
                        }

                        if (old_shape) {
                            fc.remove(old_shape);
                        }
                    },
                    changeShape: function (opening, shape) {
                        var opening_fc_sets = {
                            top       : opening.fc.top,
                            left      : opening.fc.left,
                            scaleX    : opening.fc.scaleX,
                            scaleY    : opening.fc.scaleY,
                            op_id     : opening.fc.op_id,
                            selactable: opening.fc.selactable,
                            evented   : opening.fc.evented
                        };

                        if (shape === 'circle') {
                            opening_fc_sets = _.extendOwn({}, default_fc_sets_circle, opening_fc_sets);

                            opening_fc_sets.rx = opening.fc.width / 2;
                            opening_fc_sets.ry = opening.fc.height / 2;

                            fc.remove(opening.fc);

                            opening.fc = new fabric.Ellipse(opening_fc_sets);
                        } else {
                            opening_fc_sets = _.extendOwn({}, default_fc_sets_rect, opening_fc_sets);

                            opening_fc_sets.width  = opening.fc.width;
                            opening_fc_sets.height = opening.fc.height;

                            fc.remove(opening.fc);

                            opening.fc = new fabric.Rect(opening_fc_sets);
                        }

                        fc.add(opening.fc);

                        if (opening.fc.selactable) {
                            fc.setActiveObject(opening.fc);
                        }

                        this.addClipShape(opening);
                    },
                    exportList: function () {
                        console.log(mod.exportList());
                    },
                    updateRender: function () {
                        mod.updateRender();
                    },
                    changeSingleShape: function (shape) {
                        if (v_list.length) {
                            this.$set('list[0].shape', shape);
                        }
                    },
                    changeFile: function (e, index) {
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
                                if (!v_list[index].img) {
                                    v_controls.$set('list[' + index + '].img', {});
                                }
                                v_controls.$set('list[' + index + '].img.url', e.target.result);
                                v_controls.$set('list[' + index + '].img.top', 0);
                                v_controls.$set('list[' + index + '].img.left', 0);
                                v_controls.$set('list[' + index + '].img.base64', true);
                                addImage(v_list[index]);
                            };
                            reader.readAsDataURL(input.files[0]);
                        });
                        tmp_img.src = createObjectURL(input.files[0]);
                    },
                    removeImage: function (opening, index) {
                        fc.remove(opening.img.fc);

                        v_controls.$set('list[' + index + '].img', {});
                    },
                    decreasePositionImg: function (opening, direction) {
                        this.changePositionByStepImg(opening, direction);
                    },
                    increasePositionImg: function (opening, direction) {
                        this.changePositionByStepImg(opening, direction, true);
                    },
                    changePositionByStepImg: function (opening, direction, increase) {
                        var change_step = change_step_ihn * size_ratio,
                            cur_val     = +opening.img[direction],
                            result_rel  = increase ? cur_val + change_step : cur_val - change_step;

                        fixImgOpPos(opening, direction, {
                            new_val_rel: result_rel
                        });

                        fc.renderAll();
                    },
                    changeGrab: function (opening) {
                        if (!opening.img && !opening.img.fc) {
                            return;
                        }
                        if (opening.img_controls) {
                            if (v_type.$get('type') !== 'single') {
                                opening.fc.set({
                                    evented: false
                                });
                            }
                            opening.img.fc.set({
                                selectable: true,
                                evented: true
                            });
                        } else {
                            if (v_type.$get('type') !== 'single') {
                                opening.fc.set({
                                    evented: true
                                });
                            }
                            opening.img.fc.set({
                                selectable: false,
                                evented: false
                            });
                        }
                    }
                }
            });

            v_controls.$nextTick(function () {
                renderOpenings();
                checkMatOverlap();
            });
        };

        /**
         * Get image dimensions relative to opening
         * @param {object} opening - opening shape for cliping
         * @param {fabric.Image} img_instance - opening image
         */
        var getImageOpDimensions = function (opening, img_instance) {
            var img_w_orig      = img_instance.get('width'),
                img_h_orig      = img_instance.get('height'),
                shape_opening   = opening.shape_clip,
                op_t            = shape_opening.get('top'),
                op_l            = shape_opening.get('left'),
                op_w, op_h,
                img_w, img_h, img_t, img_l, img_t_rel, img_l_rel;

            if (opening.shape === 'circle') {
                op_w = shape_opening.get('rx') * shape_opening.get('scaleX') * 2;
                op_h = shape_opening.get('ry') * shape_opening.get('scaleY') * 2;
            } else {
                op_w = shape_opening.get('width') * shape_opening.get('scaleX');
                op_h = shape_opening.get('height') * shape_opening.get('scaleY');
            }

            // set image size
            img_h = op_h;
            img_w = img_h / img_h_orig * img_w_orig;
            if (img_w < op_w) {
                img_w = op_w;
                img_h = img_w / img_w_orig * img_h_orig;
            }

            // set image position
            if (opening.img.top) {
                img_t_rel = +opening.img.top;
                img_t = op_t + img_t_rel;
            } else {
                img_t_rel = 0;
                img_t = op_t;
            }
            if (opening.img.left) {
                img_l_rel = +opening.img.left;
                img_l = op_l + img_l_rel;
            } else {
                img_l_rel = 0;
                img_l = op_l;
            }

            return {
                img_w: img_w,
                img_h: img_h,
                img_t: img_t,
                img_l: img_l,
                img_t_rel: img_t_rel,
                img_l_rel: img_l_rel,
                op_w: op_w,
                op_h: op_h
            };
        };

        /**
         * Returns function for opening content clip
         * @param {object} opening - opening object
         * @param {fabric.Image} img_instance - opening image (fabric instance)
         * @return {function} - function for clip
         */
        var getClipTo = function (opening, img_instance) {
            return function (ctx) {
                if (opening.shape === 'circle') {
                    return _.bind(clipByName, img_instance)(
                        ctx,
                        fc,
                        {
                            shape: 'ellipse'
                        }
                    );
                }
                return _.bind(clipByName, img_instance)(
                    ctx,
                    fc
                );
            };
        };

        /**
         * Add opening to an opening
         * @param {object} opening           - opening
         * @param {object} opening.img       - image settings for opening
         * @param {string} opening.img.url   - image url
         * @param {function} [callback]      - function that will be fired after image drawing
         */
        var addImage = function (opening, callback) {
            var img_url  = opening.img.url,
                op_index = _.indexOf(v_list, opening);

            fabric.Image.fromURL(img_url, function (img_instance) {
                var img_dimensions = getImageOpDimensions(opening, img_instance),
                    op_w = img_dimensions.op_w,
                    op_h = img_dimensions.op_h,
                    img_w = img_dimensions.img_w,
                    img_h = img_dimensions.img_h,
                    img_t = img_dimensions.img_t,
                    img_l = img_dimensions.img_l,
                    img_t_rel = img_dimensions.img_t_rel,
                    img_l_rel = img_dimensions.img_l_rel,
                    shape_opening = opening.shape_clip,
                    clipName  = keys.clip + '-' + opening.id;

                // img_instance.setCrossOrigin('');

                // set image settings for drawing
                img_instance.set({
                    top         : img_t,
                    left        : img_l,
                    width       : img_w,
                    height      : img_h,
                    hasRotatingPoint: false,
                    hasControls : false,
                    lockMovementX: img_w < op_w,
                    lockMovementY: img_h < op_h,
                    lockScalingX: true,
                    lockScalingY: true,
                    lockSkewingX: true,
                    lockSkewingY: true,
                    lockUniScaling: true,
                    clipName    : clipName,
                    selectable  : false,
                    evented     : false,
                    clipTo: getClipTo(opening, img_instance)
                });

                // remove old image from the canvas
                if (opening.img.fc) {
                    fc.remove(opening.img.fc);
                }
                _.each(fc.getObjects('image'), function (inst) {
                    if (inst.clipName === clipName) {
                        fc.remove(inst);
                    }
                });

                // render image in the view canvas
                fc.add(img_instance);

                // @dev
                /*var temp_c = document.createElement('canvas');
                 temp_c.className = 'render_tmp';
                 $(temp_c).insertBefore($('.pcb-customizer-tabs-holder')); // @dev;
                 var temp_fc = new fabric.Canvas(temp_c, {
                 // backgroundColor: 'red',
                 width : img_w,
                 height: img_h
                 });
                 temp_fc.add(img_instance);*/

                img_instance.op_id = opening.id;

                opening.img = {
                    url : img_url,
                    fc  : img_instance,
                    top : img_t_rel,
                    left: img_l_rel,
                    clip_name: clipName,
                    base64   : opening.img.base64
                };

                fc.moveTo(
                    img_instance,
                    _.indexOf(fc.getObjects(), shape_opening)
                );

                if (typeof callback === 'function') {
                    callback();
                }

                fc.renderAll();
            });
        };

        /**
         * Clip object by clipName for fabricJS objects
         * @param {fabric} fc_instance - canvas context
         * @param {object} ctx - canvas context
         * @param {object} [options] - other options
         * @param {string} [options.shape] - other options (rect, ellipse), rect - default
         * @this fabric.Object instance
         */
        var clipByName = function (ctx, fc_instance, options) {
            var shape = 'rect',
                clip_shape, scaleXTo1, scaleYTo1, ctxLeft, ctxTop, start_left, start_top;

            if (options) {
                shape = options.shape || shape;
            }

            this.setCoords();
            clip_shape = findByClipName(fc_instance, this.clipName);
            if (!clip_shape) {
                return;
            }
            start_left = clip_shape.group ? 0 : this.width;
            start_top  = clip_shape.group ? 0 : this.height;
            scaleXTo1  = (1 / this.scaleX);
            scaleYTo1  = (1 / this.scaleY);
            ctx.save();

            if (shape === 'ellipse') {
                ctxLeft = -(this.width - clip_shape.width) / 2 + clip_shape.strokeWidth;
                ctxTop = -(this.height - clip_shape.height) / 2 + clip_shape.strokeWidth;
            } else {
                ctxLeft = -( this.width / 2 ) + clip_shape.strokeWidth;
                ctxTop = -( this.height / 2 ) + clip_shape.strokeWidth;
            }

            ctx.translate(ctxLeft, ctxTop);

            ctx.rotate(degToRad(this.angle * -1));
            ctx.scale(scaleXTo1, scaleYTo1);
            ctx.beginPath();
            if (shape === 'ellipse') {
                ctx.ellipse(
                    clip_shape.left - this.oCoords.tl.x - 2, // 2 = magic canvas border
                    clip_shape.top - this.oCoords.tl.y - 2, // 2 = magic canvas border

                    clip_shape.width/2,
                    clip_shape.height/2,

                    0,

                    0,
                    2 * Math.PI,

                    true
                );
            } else {
                ctx.rect(
                    clip_shape.left - this.oCoords.tl.x - 2, // 2 = magic canvas border
                    clip_shape.top - this.oCoords.tl.y - 2, // 2 = magic canvas border
                    clip_shape.width,
                    clip_shape.height
                );
            }
            ctx.closePath();
            ctx.restore();
        };

        /**
         * Find fabric object instance by clipName
         * @param {fabric} fc_instance - fabric instance
         * @param {string} name - clipName of the object
         */
        var findByClipName = function (fc_instance, name) {
            var objects     = fc_instance.getObjects(),
                ob_simple   = _.filter(objects, function (obj) {
                    return obj.type !== 'group';
                }),
                ob_group    = _.filter(objects, function (obj) {
                    return obj.type === 'group';
                }),
                ob_result;

            if (ob_simple && ob_simple.length) {
                ob_result = _(ob_simple).where({
                    clipFor: name
                }).first();

                if (ob_result) {
                    return ob_result;
                }
            }

            if (ob_group && ob_group.length) {
                _.each(ob_group, function (group) {
                    if (!ob_result) {
                        ob_result = _(group.getObjects()).where({
                            clipFor: name
                        }).first();
                    }
                });

                if (ob_result) {
                    return ob_result;
                }
            }
        };

        /**
         * Convert degrees to radiance
         * @param {number} degrees - degrees value
         */
        var degToRad = function (degrees) {
            return degrees * (Math.PI / 180);
        };

        /**
         * to do so opening become with a mistake
         */
        var makeOpWrong = function (opening) {
            opening.fc.setShadow({
                color: 'rgba(255, 0, 0, 0.8)',
                blur: 10
            });
            // opening.fc.set('fill', 'red'); // @dev
            opening.error = true;
        };

        /**
         * to do so opening become without a mistake
         */
        var makeOpCorrect = function (opening) {
            opening.fc.setShadow({
                color: 'rgba(0,0,0,0)'
            });
            // opening.fc.set('fill', bg_opening); // @dev
            opening.error = false;
        };

        // var test_ops_pos; // @dev
        /**
         * Check intersection of openings
         * @param {object} e
         * @param {object} e.target - opening fabric instance
         */
        var checkIntersection = function (e) {
            var fc_op = e.target,
                error_in_list;

            if (fc_op.op_id === undefined || v_list.length < 2) {
                return;
            }

            _.each(v_list, function (opening) {
                var fc_op = opening.fc,
                    // opening for customizer including mats
                    tmp_op,
                    // real width considering scale
                    real_width,
                    // real height considering scale
                    real_height,
                    // if openings has errors with intersections
                    has_errors = false;

                fc_op.setCoords();

                real_width  = fc_op.width * fc_op.scaleX;
                real_height = fc_op.height * fc_op.scaleY;

                tmp_op = new fabric.Rect({
                    // fill    : 'green', // @dev
                    top     : fc_op.top - size_mat_op - min_dist_between,
                    left    : fc_op.left - size_mat_op - min_dist_between,
                    width   : real_width + size_mat_op * 2 + min_dist_between * 2,
                    height  : real_height + size_mat_op * 2 + min_dist_between * 2
                });
                fc.add(tmp_op);
                // tmp_op.moveTo(1); // @dev
                // // @dev
                // _.each(test_ops_pos, function (tmp) {
                //     fc.remove(tmp);
                // });
                // test_ops_pos = []; // @dev
                // test_ops_pos.push(tmp_op); // @dev

                _.each(v_list, function (opening_l) {
                    if (opening_l.fc === fc_op) {
                        return;
                    }
                    var
                        // fabric instance of the opening from list
                        fc_op_l = opening_l.fc,
                        // opening from list for customizer including mats
                        tmp_op_l,
                        // real width considering scale
                        op_real_width  = fc_op_l.width * fc_op_l.scaleX,
                        // real height considering scale
                        op_real_height = fc_op_l.height * fc_op_l.scaleY;

                    tmp_op_l = new fabric.Rect({
                        // fill    : 'green', // @dev
                        top     : fc_op_l.top - size_mat_op,
                        left    : fc_op_l.left - size_mat_op,
                        width   : op_real_width + size_mat_op * 2,
                        height  : op_real_height + size_mat_op * 2
                    });
                    fc.add(tmp_op_l);
                    // tmp_op_l.moveTo(1); // @dev
                    // test_ops_pos.push(tmp_op_l); // @dev

                    if (
                        tmp_op.intersectsWithObject(tmp_op_l) ||
                        tmp_op.isContainedWithinObject(tmp_op_l) ||
                        tmp_op_l.isContainedWithinObject(tmp_op)
                    ) {
                        has_errors = true;
                    }
                    fc.remove(tmp_op_l);
                });

                if (has_errors) {
                    makeOpWrong(opening);
                } else {
                    makeOpCorrect(opening);
                }

                fc.remove(tmp_op);
            });

            error_in_list = _.findWhere(v_list, {
                error: true
            });
            if (error_in_list) {
                mod.error = true;
                return;
            }
            mod.error = false;
        };

        /**
         * Check & set width & height restrictions
         * @param {object} e
         * @param {object} e.target - opening fabric instance
         */
        var checkSize = function (e) {
            var fc_obj      = e.target,
                cur_scaleX  = fc_obj.scaleX,
                cur_scaleY  = fc_obj.scaleY,
                // real width considering scale
                real_width  = fc_obj.width * cur_scaleX,
                // real height considering scale
                real_height = fc_obj.height * fc_obj.scaleY,
                opening,
                total_w_inch,
                total_w_ihn,
                scaleX,
                total_h_inch,
                total_h_ihn,
                scaleY;

            // width
            if (real_width <= size_min_op) {
                // fc_obj.set('scaleX', size_min_op / fc_obj.width);
                cur_scaleX = size_min_op / fc_obj.width;
            }
            if (real_width >= size_max_op_clean.width) {
                // fc_obj.set('scaleX', size_max_op_clean.width / fc_obj.width);
                cur_scaleX = size_max_op_clean.width / fc_obj.width;
            }

            // height
            if (real_height <= size_min_op) {
                // fc_obj.set('scaleY', size_min_op / fc_obj.height);
                cur_scaleY = size_min_op / fc_obj.height;
            }
            if (real_height >= size_max_op_clean.height) {
                // fc_obj.set('scaleY', size_max_op_clean.height / fc_obj.height);
                cur_scaleY = size_max_op_clean.height / fc_obj.height;
            }

            opening = _(v_list).findWhere({
                id: fc_obj.op_id
            });

            // width
            total_w_inch = convertToCorrectFraction(
                new Fraction(
                    roundNumber((fc_obj.width * cur_scaleX/* + size_mat_op * 2*/) / size_ratio)
                )
            );
            total_w_ihn = new Fraction(total_w_inch).valueOf();
            scaleX = ((total_w_ihn * size_ratio)/* - size_mat_op * 2*/) / fc_obj.width;
            fc_obj.set('scaleX', scaleX);
            v_controls.setSize(
                opening,
                'width',
                total_w_inch
            );

            // height
            total_h_inch = convertToCorrectFraction(
                new Fraction(
                    roundNumber((fc_obj.height * cur_scaleY/* + size_mat_op * 2*/) / size_ratio)
                )
            );
            total_h_ihn = new Fraction(total_h_inch).valueOf();
            scaleY = ((total_h_ihn * size_ratio)/* - size_mat_op * 2*/) / fc_obj.width;
            fc_obj.set('scaleY', scaleY);

            v_controls.setSize(
                opening,
                'height',
                total_h_inch
            );

            if (size_max_op_clean.width < size_min_op || size_max_op_clean.height < size_min_op) {
                makeOpWrong(opening);
            } else {
                makeOpCorrect(opening);
            }

            if (
                _.findWhere(v_list, {
                    error: true
                })
            ) {
                mod.error = true;
            } else {
                mod.error = false;
            }

            fc_obj.setCoords();
        };

        /**
         * Check & set horizontal & vertical restrictions
         * @param {object} e
         * @param {object} e.target - opening fabric instance
         */
        var checkPosition = function (e) {
            var fc_obj      = e.target,
                // real width considering scale
                real_width  = fc_obj.width * fc_obj.scaleX,
                // real height considering scale
                real_height = fc_obj.height * fc_obj.scaleY,
                opening,
                cur_left = fc_obj.left,
                cur_top  = fc_obj.top,
                total_left_inch,
                total_left_ihn,
                left,
                total_top_inch,
                total_top_ihn,
                top;

            // horizontal restrictions
            if (cur_left <= default_fc_sets.left) {
                // fc_obj.set('left', default_fc_sets.left);
                cur_left = default_fc_sets.left;
            }
            if (cur_left + real_width >= max_pos_right) {
                // fc_obj.set('left', max_pos_right - real_width);
                cur_left = max_pos_right - real_width;
            }
            // vertical restrictions
            if (cur_top <= default_fc_sets.top) {
                // fc_obj.set('top', default_fc_sets.top);
                cur_top = default_fc_sets.top;
            }
            if (cur_top + real_height >= max_pos_bottom) {
                // fc_obj.set('top', max_pos_bottom - real_height);
                cur_top = max_pos_bottom - real_height;
            }

            opening = _(v_list).findWhere({
                id: fc_obj.op_id
            });

            // horizontal restrictions
            total_left_inch = convertToCorrectFraction(
                new Fraction(roundNumber(
                    (cur_left - size_padding) / size_ratio)
                )
            );
            total_left_ihn = new Fraction(total_left_inch).valueOf();
            left = (total_left_ihn * size_ratio) + size_padding;
            fc_obj.set('left', left);
            v_controls.setPosition(
                opening,
                'left',
                total_left_inch
            );

            // vertical restrictions
            total_top_inch = convertToCorrectFraction(
                new Fraction(roundNumber(
                    (cur_top - size_padding) / size_ratio
                ))
            );
            total_top_ihn = new Fraction(total_top_inch).valueOf();
            top = (total_top_ihn * size_ratio) + size_padding;
            fc_obj.set('top', top);
            v_controls.setPosition(
                opening,
                'top',
                total_top_inch
            );

            checkIntersection(e);
        };

        /**
         * Duplicate opening settings for other parts
         * @param {object} e
         * @param {object} e.target - opening fabric instance
         * @param {boolean} [correct_img] - if needed correct image opening
         */
        var followOpPartsFc = function (e, correct_img) {
            e.target.setCoords();
            var fc_obj      = e.target,
                opening = _(v_list).findWhere({
                    id: fc_obj.op_id
                }),
                real_width  = fc_obj.width * fc_obj.scaleX,
                real_height = fc_obj.height * fc_obj.scaleY,
                cur_left    = fc_obj.get('left'),
                cur_top     = fc_obj.get('top');

            opening.shape_clip.set('top', cur_top);
            opening.shape_clip.set('left', cur_left);
            if (opening.shape === 'circle') {
                opening.shape_clip.set('rx', real_width / 2);
                opening.shape_clip.set('ry', real_height / 2);
            } else {
                opening.shape_clip.set('width', real_width);
                opening.shape_clip.set('height', real_height);
            }
            opening.shape_clip.setCoords();

            if (correct_img) {
                correctImgOpDimes(e);
            }
        };

        /**
         * Fix image opening position if it go beyond the bounds of what is permissible
         * @param {object} opening   - opening object
         * @param {string} direction - 'top' or 'left'
         * @param {object} [options] - other options
         * @param {object} [options.new_val_rel] - new relative value
         * @param {object} [options.new_val] - new value
         */
        var fixImgOpPos = function (opening, direction, options) {
            var shape_opening = opening.shape_clip,
                cur_val       = +opening.img[direction],
                new_val_rel   = options ? options.new_val_rel : false,
                result_rel    = (new_val_rel || new_val_rel === 0) ? new_val_rel : cur_val,
                op_d          = shape_opening.get(direction),
                is_top        = (direction === 'top'),
                size_d        = is_top ? 'height' : 'width',
                scale_d       = is_top ? 'scaleY' : 'scaleX',
                sircle_d      = is_top ? 'ry' : 'rx',
                img_size_d    = opening.img.fc.get(size_d),
                op_size_d;

            if (opening.shape === 'circle') {
                op_size_d = shape_opening.get(sircle_d) * shape_opening.get(scale_d) * 2;
            } else {
                op_size_d = shape_opening.get(size_d) * shape_opening.get(scale_d);
            }

            if (result_rel >= (op_size_d - img_size_d) && result_rel <= 0) {
                opening.img.fc.set(direction, op_d + result_rel);
                opening.img[direction] = result_rel;
            } else {
                if (result_rel < (op_size_d - img_size_d)) {
                    opening.img.fc.set(direction, op_d + (op_size_d - img_size_d));
                    opening.img[direction] = op_size_d - img_size_d;
                } else {
                    opening.img.fc.set(direction, op_d);
                    opening.img[direction] = 0;
                }
            }
        };

        /**
         * Correct image opening dimensions
         * @param {object} e
         * @param {object} e.target - opening fabric instance
         */
        var correctImgOpDimes = function (e) {
            var fc_obj  = e.target,
                opening = _(v_list).findWhere({
                    id: fc_obj.op_id
                }),
                img_instance,
                img_dimensions;

            if (!opening.img || !opening.img.fc) {
                return false;
            }

            img_instance   = opening.img.fc;
            img_dimensions = getImageOpDimensions(opening, img_instance);

            correctImgOpSize(e, {
                fc_obj : fc_obj,
                opening: opening,
                img_instance    : img_instance,
                img_dimensions  : img_dimensions
            });
            correctImgOpPos(e, {
                fc_obj : fc_obj,
                opening: opening,
                img_instance    : img_instance,
                img_dimensions  : img_dimensions
            });
        };

        /**
         * Correct image opening size
         * @param {object} e
         * @param {object} e.target - opening fabric instance
         * @param {object} [options] - other options
         */
        var correctImgOpSize = function (e, options) {
            var fc_obj  = (options && options.fc_obj) ? options.fc_obj : e.target,
                opening = (options && options.opening) ? options.opening : _(v_list).findWhere({
                    id: fc_obj.op_id
                }),
                img_instance   = options ? options.img_instance : false,
                img_dimensions = options ? options.img_dimensions : false,
                img_w,
                img_h;

            if (!opening.img || !opening.img.fc) {
                return false;
            }

            img_instance   = img_instance || opening.img.fc;
            img_dimensions = img_dimensions || getImageOpDimensions(opening, img_instance);
            img_w = img_dimensions.img_w;
            img_h = img_dimensions.img_h;

            img_instance.set({
                width : img_w,
                height: img_h
            });
        };

        /**
         * Correct image opening position
         * @param {object} e
         * @param {object} e.target - opening fabric instance
         * @param {object} [options] - other options
         */
        var correctImgOpPos = function (e, options) {
            var fc_obj  = (options && options.fc_obj) ? options.fc_obj : e.target,
                opening = (options && options.opening) ? options.opening : _(v_list).findWhere({
                    id: fc_obj.op_id
                }),
                img_instance   = options ? options.img_instance : false,
                img_dimensions = options ? options.img_dimensions : false;

            if (!opening.img || !opening.img.fc) {
                return false;
            }

            img_instance   = img_instance || opening.img.fc;
            img_dimensions = img_dimensions || getImageOpDimensions(opening, img_instance);

            fixImgOpPos(opening, 'top', {
                new_val_rel: img_dimensions.img_t_rel
            });
            fixImgOpPos(opening, 'left', {
                new_val_rel: img_dimensions.img_l_rel
            });
        };

        /**
         * Add handles for render events
         */
        var addHandlesRenderEvents = function () {
            var block_handler = false;
            fc.on({
                'object:added': checkIntersection,
                'object:selected': function (e) {
                    var fc_obj     = e.target,
                        opening_id = fc_obj.op_id,
                        v_list, opening;

                    if (opening_id === undefined) {
                        return;
                    }

                    v_list = v_controls.$get('list');
                    opening = _.findWhere(v_list, {
                        id: opening_id
                    });
                    if (opening && !opening.active) {
                        v_controls.selectOpening(opening);
                    }
                },
                'object:scaling': function (e) {
                    if (block_handler || e.target.op_id === undefined) {
                        return;
                    }
                    block_handler = true;

                    checkSize(e);

                    checkPosition(e);

                    followOpPartsFc(e, true);

                    block_handler = false;
                },
                'object:moving': function (e) {
                    var _target = e.target;
                    if (block_handler || _target.op_id === undefined) {
                        return;
                    }
                    block_handler = true;

                    if (_target.type === 'image') {
                        var opening = _(v_list).findWhere({
                                id: _target.op_id
                            }),
                            shape_opening   = opening.shape_clip,
                            op_t = shape_opening.get('top'),
                            op_l = shape_opening.get('left');

                        fixImgOpPos(opening, 'top', {
                            new_val_rel: _target.get('top') - op_t
                        });
                        fixImgOpPos(opening, 'left', {
                            new_val_rel: _target.get('left') - op_l
                        });
                    } else {
                        checkPosition(e);

                        followOpPartsFc(e);
                        correctImgOpPos(e);
                    }

                    block_handler = false;
                },
                'selection:cleared': function () {
                    var opening = _.findWhere(v_controls.$get('list'), {
                        active : true
                    });
                    if (opening) {
                        opening.active = false;
                    }
                }
            });
        };

        /**
         * renider openings from the global settings
         */
        var renderOpenings = function () {
            var mats_mod = product_options.modules.mat;
            if (!mats_mod || mats_mod.openings.type === 'single') {
                v_controls.addOpeningSingle(mats_mod.openings.list[0]);
                return;
            }

            // @dev
            /*var test = [{
                "position": {"top_inch": "9", "left_inch": "9"},
                "size": {"width_inch": "5", "height_inch": "5"},
                "shape": "rectangle",
                "name": "Opening 1"
            }, {
                "position": {"top_inch": "9", "left_inch": "0"},
                "size": {"width_inch": "5", "height_inch": "5"},
                "shape": "circle",
                "name": "Opening 2"
            }, {
                "position": {"top_inch": "0", "left_inch": "0"},
                "size": {"width_inch": "14", "height_inch": "5"},
                "shape": "rectangle",
                "name": "Opening 3"
            }];
            mats_mod.openings = test;*/
            _.each(mats_mod.openings.list, function (openings) {
                v_controls.addOpening(openings);
            });
        };

        /**
         * get mats sizes list
         */
        var getMatSizes = function () {
            product_options = window.productJson;

            var mat_mod = product_options.modules.mat;
            if (!mat_mod) {
                return;
            }
            var url_size = mat_mod.url.sizes;
            mats_sizes_active = mat_mod.sizes;

            Vue.http
                .get(url_size)
                .then(
                    // success Callback
                    function (response) {
                        if (response.status === 200) {
                            mats_sizes = JSON.parse(response.body);

                            mats_sizes.reveal = mats_sizes.reveal.map(function(num) {
                                return ''+ num;
                            });

                            mats_sizes.top.integer = mats_sizes.top.integer.map(function(num) {
                                return ''+ num;
                            });

                            mats_sizes.top.tenth = mats_sizes.top.tenth.map(function(num) {
                                return ''+ num;
                            });

                            if (mats_sizes.reveal.indexOf('' + mats_sizes_active.reveal) === -1 && mats_sizes_active.reveal) {
                                mats_sizes.reveal.unshift('' + mats_sizes_active.reveal);
                            }
                            if (mats_sizes.top.integer.indexOf('' + mats_sizes_active.top.integer) === -1 && mats_sizes_active.top.integer) {
                                mats_sizes.top.integer.unshift('' + mats_sizes_active.top.integer);
                            }
                            var zero_pos = mats_sizes.top.integer.indexOf('0');
                            if (zero_pos > -1) {
                                mats_sizes.top.integer.splice(zero_pos, 1);
                            }
                            if (mats_sizes.top.tenth.indexOf('' + mats_sizes_active.top.tenth) === -1) {
                                mats_sizes.top.tenth.unshift('' + mats_sizes_active.top.tenth);
                            }
                            if (mats_sizes.top.tenth.indexOf('0') === -1) {
                                mats_sizes.top.tenth.unshift('0');
                            }

                            if (v_controls) {
                                v_controls.$set('mats_sizes', mats_sizes);
                                v_controls.$set('mats_sizes_active', mats_sizes_active);
                            }
                        }
                    },
                    // error Callback
                    function () {
                    }
                );
        };

        /**
         * add events that can affect the drawing
         */
        var addAdminTabsEvents = function () {
            var prod_t     = document.querySelector('select[name*="product[size_type]"]'),
                inp_width  = document.querySelector('select[name*="product[dimension_1]"]'),//available width
                inp_height = document.querySelector('select[name*="product[dimension_2]"]'),//available height
                inp_headers_h = document.querySelector('select[name*="headers[size][height]"]'),
                inp_labels_h  = document.querySelector('select[name*="labels[size][height]"]'),
                mat_over      = document.querySelector('select[name*="product[matboard_overlap]"]'),
                d_width  = document.querySelector('select[name*="product[dimension_1_default]"]'),//default width
                d_height = document.querySelector('select[name*="product[dimension_2_default]"]'),//default height
                size_type  = {
                    'type_1': 'frame',
                    'type_2': 'graphic'
                };
            if (prod_t) {
                prod_t.addEventListener('change', function () {
                    product_options.modules.size.type = size_type['type_' + prod_t.value];
                    mod.updateRender();
                }, false);
            }
            if (inp_width) {
                inp_width.addEventListener('change', function () {
                    product_options.size.width.integer = +inp_width.value;
                    product_options.size.width.tenth   = 0;
                    mod.updateRender();
                }, false);
            }
            if (inp_height) {
                inp_height.addEventListener('change', function () {
                    product_options.size.height.integer = +inp_height.value;
                    product_options.size.height.tenth   = 0;
                    mod.updateRender();
                }, false);
            }

            if (d_width) {
                d_width.addEventListener('change', function () {
                    product_options.size.width.integer = +d_width.value;
                    product_options.size.width.tenth   = 0;
                    mod.updateRender();
                }, false);
            }
            if (d_height) {
                d_height.addEventListener('change', function () {
                    product_options.size.height.integer = +d_height.value;
                    product_options.size.height.tenth   = 0;
                    mod.updateRender();
                }, false);
            }

            if (typeof DRAW_TOP_BOT !== 'undefined') {
                DRAW_TOP_BOT.addListener('changed_height', function () {
                    mod.updateRender();
                });
            } else {
                if (inp_headers_h) {
                    inp_headers_h.addEventListener('change', function () {
                        product_options.modules.headers.size.height = this.value;
                        mod.updateRender();
                    }, false);
                }
            }
            if (typeof DRAW_TOP_BOT !== 'undefined') {
                DRAW_TOP_BOT.addListener('changed_height', function () {
                    mod.updateRender();
                });
            } else {
                if (inp_labels_h) {
                    inp_labels_h.addEventListener('change', function () {
                        product_options.modules.labels.size.height = this.value;
                        mod.updateRender();
                    }, false);
                }
            }

            if (mat_over) {
                mat_over.addEventListener('change', function () {
                    checkMatOverlap();
                }, false);
            }
        };

        var checkMatOverlap = function () {
            var mat_over = document.querySelector('select[name*="product[matboard_overlap]"]');
            if (!mat_over) {
                return;
            }

            var mat_over_val = +mat_over.value;

            if (mat_over_val > size_mat_op_ihn + size_mat_top_ihn) {
                alert('Matboard Overlap is wrong. It will be changed to to the correct value.');
                var temp_op = mat_over.querySelector('option:first-child');
                mat_over.querySelectorAll('option').forEach(function (option) {
                    option.selected = false;
                    if (+option.value > size_mat_op_ihn + size_mat_top_ihn) {
                        return;
                    }
                    temp_op = option;
                });
                temp_op.selected = true;
            }
        };

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

        /**
         * initialize module
         */
        mod.init = function () {
            if (typeof window.productJson === 'undefined') {
                return;
            }

            // @dev
            /*window.productJson.modules.labels = {
                code: 'labels',
                url: {
                    data: '/skin/frontend/adg/default/js/customizer/modules/labels/sample-data.json',
                    html: '/skin/frontend/adg/default/customizer/_labels.php'
                },
                for_drawing: '1',
                order: 90,
                position: 'top',
                size: {
                    height: '2 1/2',
                    gap: '1 1/2'
                },
                fonts: [
                    'Gloria Hallelujah',
                    'Roboto',
                    'Indie Flower',
                    'Nova Script'
                ],
                font_conf: {
                    size_min_inch: '1/8',
                    size_step_inch: '1/8',
                    size_def_inch: '2 1/8'
                },
                text_colors: [
                    'red',
                    'blue',
                    'green',
                    '#ffc000'
                ],
                texts: [],
                images: []
            };

            product_options = window.productJson;*/
            mod.error = false;

            addAdminTabsEvents();

            getMatSizes();

            updateCoreVars();

            renderCanvas();

            initTools();

            this.inited = true;

            console.log(this);
        };

        /**
         * Export openings list
         */
        mod.exportList = function () {
            if (typeof window.productJson === 'undefined') {
                return;
            }

            product_options.modules.mat.sizes_lock = v_controls.$get('lock_sizes');

            if (product_options.modules.mat.openings.type === 'single') {
                var opening_single = v_controls.$get('list[0]') || window.productJson.modules.mat.openings.list[0],
                    opening_export = {
                        shape: opening_single.shape || 'rectangle'
                    },
                    result_list = [];
                if (opening_single.img && opening_single.img.url) {
                    opening_export.img = _.pick(
                        opening_single.img,
                        'url',
                        'top',
                        'left'
                    );
                    if (!opening_single.img.base64) {
                        opening_export.img.url = opening_single.img.url;
                    }
                    opening_export.img.top_inch  = new Fraction(opening_single.img.top / size_ratio).toFraction(true);
                    opening_export.img.left_inch = new Fraction(opening_single.img.left / size_ratio).toFraction(true);
                    opening_export.img.preview   = true;
                }

                result_list.push(opening_export);
                return result_list;
            }

            return _(v_list).map(function (opening) {
                var res_opening = {};

                if (opening.position) {
                    res_opening.position = {
                        top_inch : opening.position.top_inch,
                        left_inch: opening.position.left_inch
                    }
                }
                if (opening.position_dev) {
                    res_opening.position_dev = {
                        top_inch : opening.position_dev.top_inch,
                        left_inch: opening.position_dev.left_inch
                    }
                }

                if (opening.size) {
                    res_opening.size = {
                        width_inch : opening.size.width_inch,
                        height_inch: opening.size.height_inch
                    }
                }
                res_opening.shape = opening.shape;
                res_opening.name = opening.name;

                if (opening.img && opening.img.url) {
                    res_opening.img = _.pick(
                        opening.img,
                        // 'url',
                        'top',
                        'left'
                    );
                    if (!res_opening.img.base64) {
                        res_opening.img.url = opening.img.url;
                    }
                    res_opening.img.top_inch  = new Fraction(res_opening.img.top / size_ratio).toFraction(true);
                    res_opening.img.left_inch = new Fraction(res_opening.img.left / size_ratio).toFraction(true);
                    res_opening.img.preview   = true;
                }

                return res_opening;
            });
        };

        /**
         *  Update data after values changes
         */
        var updateDataRender = function () {
            mod.error = false;

            product_options.modules.mat.openings.list = mod.exportList();

            updateCoreVars();

            if (v_controls) {
                v_controls.$set('inner_frame_width', size_inner_frame_inch.width);
                v_controls.$set('inner_frame_height', size_inner_frame_inch.height);
            }
        };

        /**
         * Update render of the tab
         */
        mod.updateRender = function () {
            if (typeof window.productJson === 'undefined') {
                return;
            }

            updateDataRender();

            renderCanvas();

            v_controls.$set('list', []);
            v_list = v_controls.$get('list');
            renderOpenings();
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
    })(Vue, _, Fraction)
    var CONTROL_MIN_OP_SIZE = function () {
        if (OPENINGS_ADMIN.inited) {
            return;
        }
        var inp_width  = document.querySelector('select[name*="product[dimension_1]"]'),
            inp_height = document.querySelector('select[name*="product[dimension_2]"]'),
            prod_t     = document.querySelector('select[name*="product[size_type]"]'),
            size_type  = {
                'type_1': 'frame',
                'type_2': 'graphic'
            },
            min_size = 3,
            overlap  = 0.25;
        var checkMinSize = function () {
            if (size_type['type_' + prod_t.value] !== 'graphic') {
                return;
            }
            var width  = +inp_width.value,
                height = +inp_height.value,
                temp_op = document.createElement('option');

            if (width - overlap < min_size) {
                alert('Width is wrong. It will be changed to the correct.');
                [].slice.call(inp_width.querySelectorAll('option'), 0)
                    .some(function (option) {
                        if (+option.value < min_size + overlap && !option.selected) {
                            return false;
                        }
                        if (+option.value < min_size + overlap) {
                            option.selected = false;
                            return false;
                        }
                        temp_op = option;
                        return true;
                    });
                if (!inp_width.value) {
                    temp_op.selected = true;
                }
            }

            if (height - overlap < min_size) {
                alert('Height is wrong. It will be changed to the correct.');
                [].slice.call(inp_height.querySelectorAll('option'))
                    .some(function (option) {
                        if (+option.value < min_size + overlap && !option.selected) {
                            return false;
                        }
                        if (+option.value < min_size + overlap) {
                            option.selected = false;
                            return false;
                        }
                        temp_op = option;
                        return true;
                    });
                if (!inp_height.value) {
                    temp_op.selected = true;
                }
            }
        };

        checkMinSize();

        /**
         * Add events for product controls
         */
        if (prod_t) {
            prod_t.addEventListener('change', function () {
                CONTROL_MIN_OP_SIZE();
            }, false);
        }
        if (inp_width) {
            inp_width.addEventListener('change', function () {
                CONTROL_MIN_OP_SIZE();
            }, false);
        }
        if (inp_height) {
            inp_height.addEventListener('change', function () {
                CONTROL_MIN_OP_SIZE();
            }, false);
        }
    };
    //creating jquery widget
    $.widget('custom.openingVue', {
        _create: function() {
            OPENINGS_ADMIN.init();
            window.OpeningObject = OPENINGS_ADMIN;
            CONTROL_MIN_OP_SIZE();
        },
    });
    return $.custom.openingVue;
});
