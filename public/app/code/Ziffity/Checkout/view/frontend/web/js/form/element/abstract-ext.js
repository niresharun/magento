define(['uiElement','jquery'], function(uiElement,$) {
    'use strict';

    return function (uiElement) {
        return uiElement.extend({

            addClassToParent: function(element,value){
                if(value === "" || value == null) {
                    $(element).closest('.field').removeClass('focused');
                }
                else{
                    $(element).closest('.field').addClass('focused');
                }
            }
        });
    };
});
