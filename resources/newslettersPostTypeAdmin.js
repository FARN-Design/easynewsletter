jQuery(document).ready(function ($){

    let postLockStatus;

    // add send-buttons and basic popup to html
    setTimeout(function (){
        // sendNewsletter-button
        $(".edit-post-header__toolbar").append("<button class='components-button editor-post-switch-to-draft is-tertiary en_wantToSendNewsletter'>Send Newsletter Now</button>");
        // testMail-button
        $(".edit-post-header__toolbar").append("<button class='components-button editor-post-switch-to-draft is-tertiary en_TestmailButton'>Send Test Mail</button>");
        // newsletterPopup
        $("body").append('<div class="en_newsletterAdminPopup hidden"><div class="en_newsletterAdminPopupBackground"></div><div class="en_newsletterAdminPopupContent"></div></div>');
    }, 200);


    // click on newsletter popup background - hide popup
    $(document).on('click','.en_newsletterAdminPopupBackground', function() {
        $('.en_newsletterAdminPopup').addClass('hidden');
    });
    // click on dontSendNewsletter-Button - hide popup
    $(document).on('click','.en_closePopup', function() {
        $('.en_newsletterAdminPopup').addClass('hidden');
    });


    // click on wantToSendNewsletter-Button - get number of subscribers and show popup
    $(document).on('click','.en_wantToSendNewsletter', function() {
        var post_id_val = $("#metaboxes #post_ID").val();
        // send data to php via ajax
        var post_id_val = $("#metaboxes #post_ID").val();
        var ajax_data = {
            action:  'en_wantToSendNewsletter',
            security:  ajax_nonce,
            post_id: post_id_val
        };
        jQuery.ajax({
            url : ajax_url,
            type : 'POST',
            data: ajax_data,
        })
            .done(function(data){
                $('.en_newsletterAdminPopup').removeClass('hidden');
                if(data == 0) {
                    $('.en_newsletterAdminPopupContent').html('<span>There are no subscribers matching your current settings.<br>Please be sure to save the post, before trying again.</span><button class="en_closePopup is-secondary components-button">Close</button>');
                } else {
                    $('.en_newsletterAdminPopupContent').html('<span>Do you really want to send this newsletter to <strong>' + data + '</strong> subscribers?<br>Please be sure to save the post, before doing this action.</span><button class="en_sendNewsletter is-primary components-button">Yes</button><button class="en_closePopup is-secondary components-button">No</button>');
                }
                
                
            })
            .fail(function(data){
                console.log("Error: " + JSON.stringify(data));
            });
    });   

    // click on sendNewsletter-Button - send Mails
    $(document).on('click','.en_sendNewsletter', function() {

        // send data to php via ajax
        var post_id_val = $("#metaboxes #post_ID").val();
        var ajax_data = {
            action:  'en_sendNewsletter',
            security:  ajax_nonce,
            post_id: post_id_val
        };
        jQuery.ajax({
            url : ajax_url,
            type : 'POST',
            data: ajax_data,
        })
            .done(function(data){
                console.log("done: " + data);
                if (data.includes("sendingInProgress")){
                    $('.en_newsletterAdminPopup').removeClass('hidden');
                    $('.en_newsletterAdminPopupContent').html('<span>Could not send Newsletter. Current sending still in Progress</span><button class="en_closePopup is-secondary components-button">Ok</button>');
                }
                else {
                    $('.en_newsletterAdminPopup').removeClass('hidden');
                    $('.en_newsletterAdminPopupContent').html('<span>Newsletter sending Process started.'+ data +'</span><button class="en_closePopup is-secondary components-button">Ok</button>');
                }
            })
            .fail(function(data){
                console.log("Error: " + data);
                $('.en_newsletterAdminPopup').removeClass('hidden');
                $('.en_newsletterAdminPopupContent').html('<span>Could not send mail! ' + data + '</span><button class="en_closePopup is-secondary components-button">Ok</button>');
            });
    });

     // click on Testmail-Button - send test mail
    $(document).on('click','.en_TestmailButton', function() {

        // send data to php via ajax
        var post_id_val = $("#metaboxes #post_ID").val();
        var ajax_data = {
            action:  'en_sendNewsletterTestMail',
            security:  ajax_nonce,
            post_id: post_id_val
        };
        jQuery.ajax({
            url : ajax_url,
            type : 'POST',
            data: ajax_data,
        })
            .done(function(data){
                console.log("done: " + data);
                $('.en_newsletterAdminPopup').removeClass('hidden');
                $('.en_newsletterAdminPopupContent').html('<span>Test Mail send. ' + data + '</span><button class="en_closePopup is-secondary components-button">Ok</button>');
            })
            .fail(function(data){
                console.log("Error: " + data);
                $('.en_newsletterAdminPopup').removeClass('hidden');
                $('.en_newsletterAdminPopupContent').html('<span>Could not send test mail! ' + data + '</span><button class="en_closePopup is-secondary components-button">Ok</button>');
            });
    });
});