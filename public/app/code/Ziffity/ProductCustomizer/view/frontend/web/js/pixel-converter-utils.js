define([
    'underscore',
    'Fraction'
], function (_,Fraction) {
    'use strict';
    return {
        convertPixelsToInches:function(pixels, pixelsPerInch){
            return pixels / pixelsPerInch;
        },
        convertDecimalToFraction:function(decimal){
            const fraction = new Fraction(decimal);
            const simplifiedFraction = fraction.simplify();
            return simplifiedFraction.toFraction(true);
        }
    };
});
