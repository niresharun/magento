define([
    'jquery',
    'underscore',
    'mage/loader',
    'domReady!'
], function ($, _,loader) {
    'use strict';
    return {
        findFrontendLabel:function(needle,haystack){
           if (_.has(haystack,needle)){
               return haystack[needle][0].frontend_label;
           }
           return "";
        },
        applySearch: function (value) {
            let self = this;
            value = value || self.searchQuery();
            if (value!==null) {
                self.keywordUpdated = self.searchValue !== value;
                self.searchValue = value.trim();
                self.searchQuery(value.trim());
                if (self.keywordUpdated) {
                    self.filters = false;
                    self.loadProductListIntoObservable(self);
                }
            }
            return self;
        }
    };
});
