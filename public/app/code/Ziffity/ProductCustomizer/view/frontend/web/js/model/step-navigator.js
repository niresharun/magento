define([
    'jquery',
    'ko',

], function ($, ko) {
    'use strict';

    let steps = ko.observableArray();
    let currentStep = ko.observable({ index: 0 });
    let isFirstStep = true;
    let isLastStep = false;
    let stepsProcessed = {};
    let allStepCodes = {};

    return {
        steps: steps,
        stepCodes: [],
        validCodes: [],
        currentStep: currentStep,
        stepsProcessed: stepsProcessed,
        allStepCodes:allStepCodes,
        /**
         * @return {Array}
         */
        // createSteps: function(options) {
        //
        //     var self = this, prevStep = null, nextStep = null;
        //     if($.isArray(options)) {
        //         options.forEach(function(element, index) {
        //             prevStep = (typeof options[index-1] != 'undefined') ? index-1: null;
        //             nextStep = (typeof options[index+1] != 'undefined')? index+1: null;
        //             isLastStep = (typeof options[index+1] == 'undefined');
        //             self.registerStep(
        //                 element.title,
        //                 element.default_title,
        //                 nextStep, prevStep,
        //                 index+1,
        //                 element.position,
        //                 element.sku,
        //                 element.option_id,
        //                 element.is_visible,
        //                 element.is_completed,
        //                 isFirstStep,
        //                 isLastStep,
        //             );
        //             console.log(element, index);
        //             isFirstStep = false;
        //         });
        //
        //         steps().sort(this.sortItems).forEach(function (element) {
        //             if (element.isFirstStep) {
        //                 self.currentStep(element);
        //                 element.isVisible(true);
        //             } else {
        //                 element.isVisible(false);
        //             }
        //         });
        //     }
        // },

        /**
         * @param {String} code
         * @param {*} alias
         * @param {*} nextStep
         * @param {Function} isVisible
         * @param {*} navigate
         * @param {*} sortOrder
         */
        // registerStep: function (code, alias, nextStep, prevstep, index, sortOrder, optionId, isVisible, isCompleted, isFirstStep, isLastStep) {
        //     var hash, active;
        //
        //     if ($.inArray(code, this.validCodes) !== -1) {
        //         throw new DOMException('Step code [' + code + '] already registered in step navigator');
        //     }
        //
        //     if (alias != null) {
        //         if ($.inArray(alias, this.validCodes) !== -1) {
        //             throw new DOMException('Step code [' + alias + '] already registered in step navigator');
        //         }
        //         this.validCodes.push(alias);
        //     }
        //     this.validCodes.push(code);
        //     steps.push({
        //         code: code,
        //         alias: alias != null ? alias : code,
        //        // title: title,
        //         // nextStep: nextStep,
        //         // prevStep: prevstep,
        //         index: index,
        //         sortOrder: sortOrder,
        //         optionId:optionId,
        //         isVisible: ko.observable(isVisible),
        //         isCompleted: ko.observable(isCompleted),
        //         isFirstStep: isFirstStep,
        //         isLastStep: isLastStep,
        //     });
        //     this.stepCodes.push(code);
        // },

        registerStep: function (title, code, isActive,isVisible, position,index,isFirstStep,isLastStep, info = null) {
            this.validCodes.push(code);
            const step = {
                title: title,
                code: code,
                isActive: isActive,
                isCompleted: ko.observable(false),
                position: position,
                index:index,
                isVisible:isVisible,
                isFirstStep: isFirstStep,
                isLastStep: isLastStep,
                info: info
            };
            steps.push(step);
            steps().sort(this.sortItems).forEach(function(element, index){
                element.isLastStep = false;
                element.index = index;
                if(steps()[index+1]== undefined) {
                    element.isLastStep = true;
                }
            });
            if (isActive()) {
                this.currentStep(step);
            }

        },

        resetFirstStep: function() {
            var self = this;
            let sortedItems = steps().sort(this.sortItems);
            sortedItems.forEach(function (element) {
                if (element.isFirstStep) { //eslint-disable-line eqeqeq
                    element.isActive(true);
                    self.currentStep(element);
                } else {
                    element.isActive(false);
                }

            }, this);
        },

        setAllProcessed: function() {
            let sortedItems = steps().sort(this.sortItems);
            sortedItems.forEach(function (element, index) {
                element.isCompleted(true);
            }, self);
        },
        resetAllProcessed: function() {
            let sortedItems = steps().sort(this.sortItems);
            sortedItems.forEach(function (element, index) {
                element.isCompleted(false);
            }, self);
        },
        processedSteps: function() {
            this.stepsProcessed = {};
            var self = this;
            let sortedItems = steps().sort(this.sortItems);
            sortedItems.forEach(function (element, index) {
                if(index <= this.getActiveItemIndex()){
                    this.stepsProcessed[element.title]= element.code;
                }
            }, self);
            return this.stepsProcessed;
        },

        getAllStepCodes: function(){
            this.allStepCodes = {};
            var self = this;
            let sortedItems = steps().sort(this.sortItems);
            sortedItems.forEach(function (element, index) {
                this.allStepCodes[element.title]= element.code;
            }, self);
            return this.allStepCodes;
        },

        /**
         * Sets currentStep.
         *
         * @param {String} stepIndex
         */
        setStep: function (stepIndex) {
            let self = this;
            let elt;
            steps().forEach(function (element, index){
                if(index == stepIndex) {
                    elt = element;
                }
            })
            this.currentStep(elt);
        },

        /**
         * @param {Object} itemOne
         * @param {Object} itemTwo
         * @return {Number}
         */
         sortItems: function (itemOne, itemTwo) {
            return itemOne.position > itemTwo.position ? 1 : -1;
        },

        /**
         * @return {Number}
         */
        getActiveItemIndex: function () {
            let activeIndex = 0;

            steps().sort(this.sortItems).some(function (element, index) {
                if (element.isActive()) {
                    activeIndex = index;

                    return true;
                }

                return false;
            });

            return activeIndex;
        },

         /**
         * @param {*} code
         * @return {Boolean}
         */
          isProcessed: function (code) {
            let processed = false;
            let activeItemIndex = this.getActiveItemIndex(),
                sortedItems = steps().sort(this.sortItems),
                requestedItemIndex = -1;

            sortedItems.forEach(function (element, index) {
                if (element.code == code && element.isCompleted()) {
                   // requestedItemIndex = index;
                    processed =  true;

                }
            });
            return processed;
            //return activeItemIndex > requestedItemIndex;
        },

        /**
         * @param {*} code
         */
         navigateTo: function (code, checkProcessed) {
            let sortedItems = steps().sort(this.sortItems);
            let self = this;

            if(checkProcessed) {
                if (!this.isProcessed(code)) {
                    return;
                }
            }

            sortedItems.forEach(function (element) {
                if (element.code == code) { //eslint-disable-line eqeqeq
                    element.isActive(true);
                    self.currentStep(element);
                } else {
                    element.isActive(false);
                }

            });
            self.resetCompleted();
        },

        /**
         * Next step.
         */
         next: function () {
            let activeIndex = 0,
                elementIndex = 0,
                code,self = this,nextElement = false;
            steps().sort(this.sortItems).forEach(function (element, index) {
                if (element.isActive() && !nextElement) {
                    element.isActive(false);
                    // activeIndex = index;
                    elementIndex = element.index + 1;
                    steps().forEach(function(i,index){
                        if (i.index == elementIndex){
                            steps()[index-1].isCompleted(true);
                            steps()[index].isActive(true);
                            self.currentStep(steps()[index]);
                            nextElement = true;
                        }
                    });
                }
            });
            self.resetCompleted();

            // if (steps().length > activeIndex + 1) {
            //     code = steps()[elementIndex].code;
            //     steps()[elementIndex].isVisible(true);
            //     this.currentStep(steps()[elementIndex]);
            // }
            // if (steps().length > activeIndex) {
            //     code = steps()[activeIndex].code;
            //     steps()[activeIndex].isVisible(true);
            //     this.currentStep(steps()[activeIndex]);
            // }
        },


        prev : function () {
            let activeIndex = 0,
                elementIndex = 0,
                code,self = this,nextElement = false;;

            steps().sort(this.sortItems).forEach(function (element, index) {
                if (element.isActive() && !nextElement) {
                    element.isActive(false);
                    // activeIndex = index;
                    elementIndex = element.index - 1;
                    steps().forEach(function(i,index){
                        if (i.index == elementIndex){
                            steps()[index].isActive(true);
                            self.currentStep(steps()[index]);
                            nextElement = true;
                        }
                    });
                    // code = steps()[elementIndex];
                    // if (code!==undefined) {
                    //     steps()[elementIndex].isVisible(true);
                    //     this.currentStep(steps()[elementIndex]);
                    // }
                }
            });
            self.resetCompleted();

            // if ((activeIndex) > 0) {
            //     code = steps()[elementIndex].code;
            //     steps()[elementIndex].isVisible(true);
            //     this.currentStep(steps()[elementIndex]);
            // }
            // if ((activeIndex) > 0) {
            //     code = steps()[activeIndex].code;
            //     steps()[activeIndex].isVisible(true);
            //     this.currentStep(steps()[activeIndex]);
            // }
        },
        resetCompleted: function(){
            let sortedItems = steps().sort(this.sortItems);
            sortedItems.forEach(function (element, index) {
                if(index < this.getActiveItemIndex()){
                    element.isCompleted(true);
                } else {
                    element.isCompleted(false);
                }
            }, this);
        }

    };
});
