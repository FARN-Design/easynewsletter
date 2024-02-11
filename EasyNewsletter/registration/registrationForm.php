<?php

namespace easyNewsletter;

if (!empty(sanitize_text_field($_REQUEST['submit'] ?? ""))){

    $metaFields = array();


    foreach ($_REQUEST as $field => $input){
        if ($field != 'submit'){
            $metaFields[$field] = sanitize_text_field($input);
        }
    }

    if ( subscriberHandler::instance()->addNewSubscriber( $metaFields) ){
	    //No escape because we want to get the raw value
		//Linkt auf Success Page
        echo get_the_content("", false, databaseConnector::instance()->getSettingFromDB("registrationSuccessPageID"));
        return;
    }
    else{
        echo "<h3>".esc_attr__("Email already in use!", "easynewsletter")."</h3>";
    }
}


$content = get_the_content("", false, databaseConnector::instance()->getSettingFromDB("registrationFormPageID"));

ob_start();
include ("registrationFormContent.php");
$formContent = ob_get_contents();
ob_end_clean();

$content = str_replace("{{registrationForm}}", $formContent , $content);

//No escape because we want to get the raw content
echo $content;
