/**
 * Copyright © Ziffity, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
//TODO: This file is not being used anywhere but have to remove this in near future , using as ref
define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/url',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
    ], function($, uiConfirm, url, modal, $t){
    "use strict";
    $(document).ready(function(){

        $(document).on("click", ".delete.design", function (e) {
            var attrId = $(this).attr('id');
            var designId = attrId.replace('delete-design-', '');
            uiConfirm({
                content: $t('Are you sure you want to delete this design?'),
                actions: {
                    /** @inheritdoc */
                    confirm: function () {
                        ajaxCall(designId, 'delete');
                    }
                }
            });
        });

        $(document).on("click", ".duplicate.design", function (e) {
            var attrId = $(this).attr('id');
            var designId = attrId.replace('duplicate-design-', '');
            ajaxCall(designId, 'duplicate');
        });

        $(document).on("click", ".edit.design", function (e) {
            let attrId = $(this).attr('id');
            let designId=attrId.replace('edit-label-', '');
            let title = $('#input-title-'+designId).val();
            $('#title-popup-'+designId).show();
            $('#input-title-'+designId).keyup(function(){
                if($(this).val()==title || $(this).val() == '') {
                    $('#title-popup-'+designId+' .title-action').hide();
                } else {
                    $('#title-popup-'+designId+' .title-action').show();
                }
            });
        });
        $(document).on("click", ".title-update", function (e) {
            var attrId = $(this).parent().parent().attr('id');
            var designId=attrId.replace('title-popup-', '');
            var updatedTitle = $('#input-title-'+designId).val();
            var params = {'id': designId,'title': updatedTitle};
            ajaxCall(params, 'title');
        });

        $(document).on("click", ".title-cancel", function (e) {
            var attrId = $(this).parent().parent().attr('id');
            var designId=attrId.replace('title-popup-', '');
            var updatedTitle = $('#title-popup-'+designId).siblings('.design.label').text();
            $('#input-title-'+designId).val(updatedTitle);
            $(this).parent().parent().hide();
        });

        $(document).on("click", ".design.action .action-link", function (e) {
            $(this).siblings('.action-items').toggle();
        });


        function ajaxCall(params, action) {
            var postUrl = url.build('saveddesigns/lists/'+action+'/');
            var title = '';
            var id = '';
            if(action == 'title') {
                id = params.id;
                title = params.title;
            } else {
                id = params;
            }

            $.ajax({
               url: postUrl,
               type: 'POST',
               data: {ajax: true, id: id, title: title},
               showLoader: true,
               success: function(result) {
                   location.reload();
               },
               error: function(result) {
                   location.reload();
               }
           });
        }

        var options = {
            type: 'popup',
            responsive: true,
            title: $t('Share Design'),
            buttons: []
        };

        var popup = modal(options, $('#share-design-modal'));
        $(document).on("click", ".share-design",function() {
            $('#share-design-modal .share-url').html($(this).attr('data-url'))
            $('#share-design-modal').modal('openModal');
        });
        $(document).on("click", "#copy-link",function() {
            const textToCopy = $(this).data('url');
            // Create a temporary textarea element
            const textarea = document.createElement('textarea');
            textarea.value = textToCopy;
            document.body.appendChild(textarea);
            // Select the text
            textarea.select();
            textarea.setSelectionRange(0, textarea.value.length);
            // Copy the selected text to the clipboard
            document.execCommand('copy');
            // Clean up - remove the textarea element
            document.body.removeChild(textarea);
        });
    });
});
