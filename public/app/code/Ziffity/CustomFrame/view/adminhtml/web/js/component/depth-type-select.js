define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry',
    'jquery',
    'uiComponent',
    'Magento_Ui/js/lib/view/utils/dom-observer'
], function (Select, registry,$,Component,domObserver) {
    'use strict';
    return Select.extend({
        defaults: {
            depthType:'',
            imports: {
               depthType: 'product_form.product_form_data_source:data.product.depth_type'
            },
        },
        initObservable:function() {
            this._super().observe(
                'depthType'
            );
            return this;
        },
        /**
         * @inheritdoc
         */
        initialize:function(){
            this._super();
            this.initSubscribers();
           return this;
        },
        initSubscribers: function () {
            var self = this;
            let boxThickness = registry.get('index=box_thickness');
            self.depthType.subscribe(function (depthType){
                if (depthType == "insert_thickness"){
                    if (registry.get('index=graphic_thickness_interior_depth')!==undefined){
                        registry.get('index=graphic_thickness_interior_depth').visible(true);
                    }
                }
                if (depthType == "interior_depth"){
                    if (boxThickness!==undefined){
                        boxThickness.visible(true);
                    }
                    if (registry.get('index=graphic_thickness_interior_depth')!==undefined){
                        registry.get('index=graphic_thickness_interior_depth').visible(true);
                    }
                }
                if (depthType == "none"){
                    if (boxThickness!==undefined){
                        boxThickness.visible(false);
                    }
                    if (registry.get('index=graphic_thickness_interior_depth')!==undefined){
                        registry.get('index=graphic_thickness_interior_depth').visible(false);
                    }
                }
            });
        },
        setInitialValue: function () {
            this._super();
            let graphicThicknessElement = "select[name='product[graphic_thickness_interior_depth]']";
            let boxThickness = registry.get('index=box_thickness');
            let boxThicknessElement = "select[name='product[box_thickness]']";
            if (this.value() === "none") {
                domObserver.get(graphicThicknessElement,function(element){
                    if (element.length){
                        if (registry.get('index=graphic_thickness_interior_depth')!==undefined){
                            registry.get('index=graphic_thickness_interior_depth').visible(false);
                        }
                    }
                });
                domObserver.get(boxThicknessElement,function(element){
                    if (element.length){
                        if (boxThickness!==undefined) {
                            boxThickness.visible(false);
                    }
                }
                });
            }
            return this;
        },
    });
});
