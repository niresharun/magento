define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/url',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'jquery/ui',
], function($, uiConfirm, url, modal, alert, $t){
    $.widget('mage.saveDesign', {
        /**
         * Widget initialization
         * @private
         */
        _create: function() {
            this._super();
            this.toggle();
            this.deleteDesign();
            this.duplicateDesign();
            this.editDesign();
            this.titleUpdate();
            this.titleCancel();
            this.copyLink();
            return this;
        },
        deleteDesign:function(){
            let self = this;
            $(document).on("click", ".delete.design", function (e) {
                let attrId = $(this).attr('id');
                let designId = attrId.replace('delete-design-', '');
                uiConfirm({
                    content: $t('Are you sure you want to delete this design?'),
                    actions: {
                        /** @inheritdoc */
                        confirm: function () {
                            self.ajaxCall(designId, 'delete');
                        }
                    }
                });
            });
        },
        duplicateDesign:function(){
            let self = this;
            $(document).on("click", ".duplicate.design", function (e) {
                let attrId = $(this).attr('id');
                let designId = attrId.replace('duplicate-design-', '');
                self.ajaxCall(designId, 'duplicate');
            });
        },
        editDesign:function(){
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
        },
        titleUpdate:function(){
            let self = this;
            $(document).on("click", ".title-update", function (e) {
                let attrId = $(this).parent().parent().attr('id');
                let designId=attrId.replace('title-popup-', '');
                let updatedTitle = $('#input-title-'+designId).val();
                let params = {'id': designId,'title': updatedTitle};
                self.ajaxCall(params, 'title');
            });
        },
        titleCancel:function(){
            $(document).on("click", ".title-cancel", function (e) {
                let attrId = $(this).parent().parent().attr('id');
                let designId=attrId.replace('title-popup-', '');
                let updatedTitle = $('#title-popup-'+designId).siblings('.design.label').text();
                $('#input-title-'+designId).val(updatedTitle);
                $(this).parent().parent().hide();
            });
        },
        toggle:function(){
            $(document).on("click", ".design.action .action-link", function (e) {
                $(this).siblings('.action-items').toggle();
            });
        },
        ajaxCall:function(params,action){
            let postUrl = url.build('saveddesigns/lists/'+action+'/');
            let title = '';
            let id = '';
            id = params;
            if(action == 'title') {
                id = params.id;
                title = params.title;
            }
            $.ajax({
                url: postUrl,
                type: 'POST',
                data: {ajax: true, id: id, title: title},
                showLoader: true,
                success: function() {
                    location.reload();
                },
                error: function() {
                    location.reload();
                }
            });
        },
        copyLink:function(){
                $(document).on("click", "#copy-link", function () {
                    alert({
                        title: $.mage.__('Saved Designs'),
                        content: $.mage.__('Link Copied Successfully'),
                    });
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
        }
    });
    return $.mage.saveDesign;
});
