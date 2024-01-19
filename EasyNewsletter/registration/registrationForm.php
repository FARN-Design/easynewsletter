<?php

namespace easyNewsletter;

if (isset($_REQUEST['submit'])){

    $metaFields = array();


    foreach ($_REQUEST as $field => $input){
        if ($field != 'submit'){
            $metaFields[$field] = sanitize_text_field($input);
        }
    }

    if ( subscriberHandler::instance()->addNewSubscriber( $metaFields) ){
        //Linkt auf Success Page
        echo get_the_content("", false, databaseConnector::instance()->getSettingFromDB("registrationSuccessPageID"));
        return;
    }
    else{
        echo "<h3>".__("Email already in use!", "easynewsletter")."</h3>";
    }
}


$content = get_the_content("", false, databaseConnector::instance()->getSettingFromDB("registrationFormPageID"));

ob_start();
include ("registrationFormContent.php");
$formContent = ob_get_contents();
ob_end_clean();

$content = str_replace("{{registrationForm}}", $formContent , $content);

echo $content;
?>
