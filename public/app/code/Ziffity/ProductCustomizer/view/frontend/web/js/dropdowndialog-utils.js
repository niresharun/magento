define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    return {
        renderDropdownDialog:function(element, options = null){
            if (element.length) {
                if (element.dropdownDialog('instance') === undefined) {
                    element.dropdownDialog(options);
                }else{
                    element.dropdownDialog('destroy');
                    element.dropdownDialog();
                }
            }
        }
    };
});
