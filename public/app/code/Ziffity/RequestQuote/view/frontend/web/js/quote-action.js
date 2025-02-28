/**
 * Copyright © Ziffity, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery', 'mage/url', 'Magento_Ui/js/modal/modal', "mage/mage"], function($, url, modal){
    "use strict";
    return function(config) {
        // Download PDF Modal
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'How do you want to download quote PDF?',
            buttons: [{
                text: $.mage.__('Close'),
                class: 'modal-close',
                click: function (){
                    this.closeModal();
                }
            }]
        };

        modal(options, $('#pdf-modal-content'));

        $("#modal-pdf").on('click',function(){
            $("#pdf-modal-content").modal("openModal");
        });

        $("#quote-action").on('click',function(){
            $("#action-listing").toggle(200);
        });

        $("#with-header, #without-header").on('click',function(){
            $("#pdf-modal-content").modal("closeModal");
        });

        // Archive Modal
        var archiveOptions = {
            type: 'popup',
            responsive: true,
            title: 'Are you sure want to archive quote?',
            buttons: [{
                text: $.mage.__('Close'),
                class: '',
                click: function () {
                    this.closeModal();
                }
            }]
        };

        var popup = modal(archiveOptions, $('#archive-modal'));

        $(".archive-quote").click(function(event) {
            var archiveUrl = $(this).children(':input').val();

            $('#archive-modal').modal('openModal');
            $('#archive-confirm').attr("data-post",archiveUrl);
        });

        $("#archive-cancel").click(function(event) {
            $('#archive-modal').modal('closeModal');
        });

        // Share PDF Modal

        var sharePdf = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Share the quote PDF',
                closeText: 'Close',
                buttons: []
            };
        var sharePfdModal = modal(sharePdf, $('#share-pdf-modal'));
        $("#generate-pdf").on("click",function(){
            $('#share-pdf-modal').modal('openModal');
        });

        // Cancel PDF Modal

        var cancelPdf = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Are you sure want to cancel the quote?',
                closeText: 'Close',
                buttons: []
            };
        var cancelPfdModal = modal(cancelPdf, $('#cancel-modal'));
        $("#cancel-pdf").on("click",function(){
            $('#cancel-modal').modal('openModal');
        });

        $("#quote-cancel").click(function(event) {
            $('#cancel-modal').modal('closeModal');
        });
    };
});
