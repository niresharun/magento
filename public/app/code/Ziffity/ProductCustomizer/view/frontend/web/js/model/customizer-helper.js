
/**
 * Customizer adapter for loading options
 *
 * @api
 */
 define([
    'jquery',
    'ko',
    'Fraction'
], function ($, ko, fraction) {
    'use strict';

    return {
        getFullNumber : function (number) {
        var inch = $.isNumeric(number) ? number :+number.integer;

        if (typeof number.tenth !== 'undefined') {
            if ($.isNumeric(number.tenth) || number.tenth.indexOf('/') === -1) {
                inch += +number.tenth;
            } else {
                inch += new fraction(number.tenth).valueOf();
            }
        }

        return inch;
        },

        mixedNumberStringToFloat: function(mixedNumberString) {
             const parts = mixedNumberString.split(' ');

             if (parts.length === 1) {
                 // If there's no space, it's just a regular number, parse and return it.
                 return parseFloat(parts[0]);
             } else if (parts.length === 2) {
                 // If there's a space, it's a mixed number.
                 const wholeNumber = parseFloat(parts[0]);
                 const fraction = this.fractionStringToFloat(parts[1]);

                 return wholeNumber + fraction;
             } else {
                 throw new Error('Invalid mixed number format.');
             }
        },

        fractionStringToFloat: function(fractionString) {
             const parts = fractionString.split('/');

             if (parts.length === 2) {
                 const numerator = parseFloat(parts[0]);
                 const denominator = parseFloat(parts[1]);

                 if (denominator !== 0) {
                     return numerator / denominator;
                 } else {
                     throw new Error('Denominator cannot be zero.');
                 }
             } else if (parts.length === 1) {
                 return parseFloat(parts[0]);
             } else {
                 throw new Error('Invalid fraction format.');
             }
        },

        floatToMixedNumberString: function(floatValue) {
             if (isNaN(floatValue)) {
                 throw new Error('Invalid input: Not a number.');
             }

             // Extract the whole number part and the fractional part.
             const wholeNumber = Math.floor(Math.abs(floatValue));
             const fractionalPart = Math.abs(floatValue) - wholeNumber;

             // Find the greatest common divisor (GCD) of the numerator and denominator
             // to simplify the fractional part.
             const gcd = this.findGCD(Math.round(fractionalPart * 1000000), 1000000);

             // Calculate the numerator and denominator for the simplified fraction.
             const numerator = Math.round(fractionalPart * 1000000 / gcd);
             const denominator = 1000000 / gcd;

             // Create the mixed number string.
             const sign = floatValue < 0 ? '-' : '';
             const fractionString = numerator === 0 ? '' : ` ${numerator}/${denominator}`;

             return `${sign}${wholeNumber}${fractionString}`;
         },

// Helper function to find the greatest common divisor (GCD) using Euclidean algorithm.
        findGCD: function(a, b) {
             if (b === 0) {
                 return a;
             } else {
                 return this.findGCD(b, a % b);
             }
        },
        isMixedNumber: function(value) {
            if (typeof value !== "string") {
                return false; // Mixed numbers are typically represented as strings
            }

            // Regular expression pattern to match a mixed number
            const pattern = /^(\d+)\s+(\d+)\/(\d+)$/;

            return pattern.test(value);
        },
    };
});
