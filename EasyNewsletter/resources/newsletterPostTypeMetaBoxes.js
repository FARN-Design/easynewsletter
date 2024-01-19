
//Custom HTML Injection BOX
(function ($, window, document) {
    'use strict';
    $(document).ready(function () {

        // Save Button
        $(document).on('click', '.en_save_custom_html_injection_box', function () {

            let customKey = $(".en_customHtmlInjectionKeyInput").val();
            let metaField = $(".en_customHtmlInjectionMetaFieldInput").val();

            if (metaField === "" || customKey === ""){
                return;
            }

            const ajax_data = {
                action: 'en_htmlInjectionBoxSave',
                security: ajax_nonce,
                customKey: customKey,
                metaField: metaField,
                post_ID: jQuery('#post_ID').val()
            };
            jQuery.ajax({
                url : ajax_url,
                type : 'POST',
                data: ajax_data,
            })
                .done(function(){
                    $(".en_customHtmlInjectionHolder").append("" +
                        "<div>" +
                        "<input type='text' class='en_customHtmlInjectionKey' value='"+customKey+"' disabled>" +
                        " => " +
                        "<input type='text' class='en_customHtmlInjectionMetaField' value='"+metaField+"' disabled>" +
                        "<button class='button en_delete_custom_html_injection'>Remove</button>" +
                        "</div>")

                    $(".en_customHtmlInjectionKeyInput").val("");
                    $(".en_customHtmlInjectionMetaFieldInput").val("");

                })
                .fail(function(){
                    console.log("Error: While processing custom html injection.")
                });
        })

        // Delete Button
        $(document).on('click', '.en_delete_custom_html_injection', function () {

            let customKey = $(this).siblings(".en_customHtmlInjectionKey").val()
            let metaField = $(this).siblings(".en_customHtmlInjectionMetaField").val()
            let parent = $(this).parent()

            const ajax_data = {
                action: 'en_htmlInjectionBoxDeleteElement',
                security: ajax_nonce,
                customKey: customKey,
                metaField: metaField,
                post_ID: jQuery('#post_ID').val()
            };
            jQuery.ajax({
                url : ajax_url,
                type : 'POST',
                data: ajax_data,
            })
                .done(function(){
                    parent.remove();
                })
                .fail(function(){
                    console.error("Error while deleting");
                });
        })
    });
}(jQuery, window, document));


//Custom MetaFields BOX
(function ($, window, document) {
    'use strict';
    $(document).ready(function () {

        //AND Button
        $(document).on('click', '.en_AND_custom_metaField_condition_box', function () {

            let metaField = $(this).siblings(".en_customMetaFieldKey").val();
            let condition = $(this).siblings(".en_customMetaConditionKey").val();
            let value = $(this).siblings(".en_customMetaValueKey").val();
            let containerKey = $(this).parent().siblings(".en_containerKey").val()

            if (metaField === "" || condition === "" || value === ""){
                return;
            }

            const ajax_data = {
                action: 'en_metaFieldsBoxAND',
                security: ajax_nonce,
                metaField: metaField,
                condition: condition,
                value: value,
                post_ID: jQuery('#post_ID').val(),
                containerKey: containerKey
            };
            jQuery.ajax({
                url : ajax_url,
                type : 'POST',
                data: ajax_data,
            })
                .done(function(data){
                   console.log(data)
                    window.setTimeout('location.reload()');
                })
                .fail(function(){
                    console.log("Error something went wrong while processing meta boxes")
                });
        })

        //OR Button
        $(document).on('click', '.en_OR_custom_metaField_condition_box', function () {

            let metaField = $(this).siblings(".en_customMetaFieldKey").val();
            let condition = $(this).siblings(".en_customMetaConditionKey").val();
            let value = $(this).siblings(".en_customMetaValueKey").val();

            if (metaField === "" || condition === "" || value === ""){
                return;
            }

            const ajax_data = {
                action: 'en_metaFieldsBoxOR',
                security: ajax_nonce,
                metaField: metaField,
                condition: condition,
                value: value,
                post_ID: jQuery('#post_ID').val()
            };
            jQuery.ajax({
                url : ajax_url,
                type : 'POST',
                data: ajax_data,
            })
                .done(function(data){
                    console.log(data)
                    window.setTimeout('location.reload()');
                })
                .fail(function(data){
                    console.log(data)
                });
        })

        // Delete Button
        $(document).on('click', '.en_delete_custom_metaFieldCondition', function () {

            let metaField = $(this).siblings(".en_customMetaField").val()
            let condition = $(this).siblings(".customMetaCondition").val()
            let value = $(this).siblings(".customMetaConditionValue").val()
            let ruleKey = $(this).siblings(".en_ruleKey").val()
            let containerKey = $(this).parent().siblings(".en_containerKey").val()
            let parent = $(this).parent()

            const ajax_data = {
                action: 'en_metaFieldsBoxDeleteElement',
                security: ajax_nonce,
                metaField: metaField,
                condition: condition,
                value: value,
                post_ID: jQuery('#post_ID').val(),
                containerKey: containerKey,
                ruleKey: ruleKey
            };
            jQuery.ajax({
                url : ajax_url,
                type : 'POST',
                data: ajax_data,
            })
                .done(function(data){
                    console.log(data)
                    parent.remove();
                })
                .fail(function(){
                    console.error("Error while deleting");
                });
        })
    });
}(jQuery, window, document));


//Newsletter Attachments BOX
(function ($, window, document) {
    'use strict';
    $(document).ready(function () {

        //save Button
        $(document).on('click', '.en_save_newsletter_attachment_box', function () {

            let attachmentURL = $(".en_newsletterAttachmentURL_input").val();
            if (attachmentURL === ""){
                return;
            }

            const ajax_data = {
                action: 'en_newsletterAttachmentBoxSave',
                security: ajax_nonce,
                attachmentURL: attachmentURL,
                post_ID: jQuery('#post_ID').val()
            };
            jQuery.ajax({
                url : ajax_url,
                type : 'POST',
                data: ajax_data,
            })
                .done(function(){
                    $(".en_newsletterAttachmentsHolder").append("<div>" +
                        "<input disabled type='text' class='en_newsletterAttachmentURL' value='"+attachmentURL+"'>" +
                        "<button class='button en_delete_attachment'>Remove</button>" +
                        "</div>")

                    $(".en_newsletterAttachmentURL_input").val("")
                })
                .fail(function(){
                    console.log("Error: Something went wrong in the attachment box")
                });
        })

        // Delete Button
        $(document).on('click', '.en_delete_attachment', function () {

            let attachmentURL = $(this).siblings(".en_newsletterAttachmentURL").val()
            let parent = $(this).parent()

            const ajax_data = {
                action: 'en_newsletterAttachmentBoxDeleteElement',
                security: ajax_nonce,
                attachmentURL: attachmentURL,
                post_ID: jQuery('#post_ID').val()
            };
            jQuery.ajax({
                url : ajax_url,
                type : 'POST',
                data: ajax_data,
            })
                .done(function(data){
                    console.log(data)
                    parent.remove();
                })
                .fail(function(){
                    console.error("Error while deleting");
                });
        })
    });
}(jQuery, window, document));