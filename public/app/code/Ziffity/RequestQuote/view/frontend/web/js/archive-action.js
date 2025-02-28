define(['jquery', 'mage/url', 'Magento_Ui/js/modal/modal'], function($, url, modal){
    "use strict";
    return function(config) {

        var options = {
            type: 'popup',
            responsive: true,
            title: 'Are you sure want to archive the quote?',
            buttons: [{
                text: $.mage.__('Close'),
                class: '',
                click: function () {
                    this.closeModal();
                }
            }]
        };

        var popup = modal(options, $('#archive-modal'));

        $(".archive-quote").click(function(event) {
            var archiveUrl = $(this).children(':input').val();

            $('#archive-modal').modal('openModal');
            $('#archive-confirm').attr("data-post",archiveUrl);
        });

        $("#archive-cancel").click(function(event) {
        	$('#archive-modal').modal('closeModal');
        });
    };
});
