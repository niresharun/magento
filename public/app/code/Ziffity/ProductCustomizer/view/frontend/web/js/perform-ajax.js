define([
    'jquery',
    'underscore',
    'mage/url',
    'mage/loader',
    'domReady!'
], function ($, _,urlBuilder,loader) {
    'use strict';

    return {
        performAjaxOperation:function(url,type,data){
            return $.ajax({
                url:urlBuilder.build(url),
                // showLoader:true,
                data:{data},
                type:type,
                cache:true,
                beforeSend:function(){
                    $('body').trigger('processStart'); // start loader
                }
            });
        },
        performNonAsyncAjaxOperation:function(url,type,data){
            return $.ajax({
                url:urlBuilder.build(url),
                data:{data},
                type:type,
                cache:true,
                async:false,
                beforeSend:function(){
                    $('body').trigger('processStart'); // start loader
                }
            });
        },
        showStopLoader:function(display){
            $('body').loader(display);
        },
    };
});
