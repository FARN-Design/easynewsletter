jQuery(document).ready(function($) {
    console.log('test');

    // click on "edit"-Button
    $(document).on('click','.edit_button', function() {
        var column = $(this).parent();
        // show input field, exit-button and save button
        if(!column.hasClass('edit_active')){
            column.addClass('edit_active'); 
            column.find('.edit_input input').focus();
        }
    });

    // click on "exit"-Button
    $(document).on('click','.exit_button', function() {
        // close everything and don't save
        var column = $(this).parent();
        var column_content = column.find('.column_content').html();
        column.find('.edit_input input').val(column_content);
        column.removeClass('edit_active');
    });
    
    // click on "save"-Button
    $(document).on('click','.save_button', function() {
        // get all values for ajax-save-function
        var column = $(this).parent();
        var post_id = column.parent().attr('id').replace('post-','');
        var field_name = column.find('.column_content').attr('data-field-name');
        var column_content = column.find('.edit_input input').val();
        var field_type = 'input';
        // hide input, exit-button and save-button
        column.removeClass('edit_active');
        // set column_content-html
        column.find('.column_content').html(column_content);

        // save field to database via ajax
        var ajax_data = {
            action:  'saveBackendSubscriberCustomContent',
            security:  ajax_nonce,
            content: column_content,
            post_id: post_id,
            field_name: field_name,
            field_type: field_type
        };
        jQuery.ajax({
            url : ajax_url,
            type : 'POST',
            data: ajax_data,
        })
        .done(function(data){
            console.log('Done');
        })
        .fail(function(){
            console.log('Error');
        });
    });

});