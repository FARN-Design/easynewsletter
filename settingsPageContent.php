<?php

namespace easyNewsletter;

//handles the HTTP POST request from the settings page form.
if (isset($_REQUEST['submit'])){
    $settingsValueMap = $_REQUEST;
    unset($settingsValueMap['page']);
    unset($settingsValueMap['submit']);

    //For each value in the request stores the input into the database
    foreach ($settingsValueMap as $setting => $value){
        // if value is an array - serialize it before saving it in the database

        if(is_array($value)){
            $value = serialize($value);
        }

        if ($setting == "subscriberCategory"){
            $targetGroups = unserialize(databaseConnector::instance()->getSettingFromDB("subscriberCategory"));
            if($value != ""){
                $targetGroups[] = $value;
                databaseConnector::instance()->saveSettingInDB($setting, serialize($targetGroups));           
            }
            continue;        
        }
        if ($setting == "subscriberRole"){
            $targetGroups = unserialize(databaseConnector::instance()->getSettingFromDB("subscriberRole"));
            if($value != ""){
                $targetGroups[] = $value;
                databaseConnector::instance()->saveSettingInDB($setting, serialize($targetGroups));           
            }
            continue;
        }
        // save the value to the database
        databaseConnector::instance()->saveSettingInDB($setting, $value);
    }

    easyNewsletter::updateFarnCronService();

    //Page Reload in case of that the subscriber mode changed
	//echo '<script>console.log("relaod!"); location.reload();</script>';
}

if (isset($_REQUEST["stopNewsletterSending"])){
    mailManager::instance()->stopNewsletterSending("Stopped");
}

if (isset($_POST["removeCategory"])){
	$targetGroups = unserialize(databaseConnector::instance()->getSettingFromDB("subscriberCategory"));
    $index = array_search($_POST["category"], $targetGroups);
    unset($targetGroups[$index]);
	databaseConnector::instance()->saveSettingInDB("subscriberCategory", serialize($targetGroups));
}

// create the checkboxes for signupFormFields
function createCheckboxes($signupFormFieldsValue): string {
    // array of all available checkboxes
    $checkboxesValues = subscriberPostType::$metaFieldsOptional;
    // values from database/settings-page
    $checkboxesActive = unserialize($signupFormFieldsValue);
    // create all checkboxes
    $checkboxesFrontendHtml = "";
    foreach($checkboxesValues as $checkboxVar => $checkboxValue){
        $checkboxesFrontendHtml .= "<input type='checkbox' name='signupFormFields[]' value='" . $checkboxVar ."'";
        // check, if a specific signupFormFields-checkbox should be checked
        if($checkboxesActive !== false && in_array($checkboxVar,$checkboxesActive)){
            $checkboxesFrontendHtml .=  "checked='checked'"; 
        }
        match ($checkboxValue){
            "Salutation" => $checkboxesFrontendHtml .= ">" . __("Salutation", "easynewsletter") ."<br>",
            "Gender" => $checkboxesFrontendHtml .= ">" . __("Gender", "easynewsletter") ."<br>",
            "First Name" => $checkboxesFrontendHtml .= ">" . __("First Name", "easynewsletter") ."<br>",
            "Last Name" => $checkboxesFrontendHtml .= ">" . __("Last Name", "easynewsletter") ."<br>",
            "Telephone Number" => $checkboxesFrontendHtml .= ">" . __("Telephone Number", "easynewsletter") ."<br>",
            default => $checkboxesFrontendHtml .= ">" . $checkboxValue ."<br>"
        };

    }
    // add eMailAddress-checkbox, which is required and can not be unchecked (has disabled parameter) 
    // notice, that there is a hidden input field. That is needed, because disabled checkboxes dont return their values
    $checkboxesFrontendHtml .= "<input type='checkbox' name='signupFormFields[]' value='en_eMailAddress' checked='checked' disabled>".__("Email Address","easynewsletter")."<br><input type='hidden' name='signupFormFields[]' value='en_eMailAddress' checked='checked'>";

	return $checkboxesFrontendHtml;
}

function generateModeSelection(): string{
    $returnString = '<select form="easyNewsletterSettings" name="subscriberMode" id="subscriberMode">';
    $selectedMode = databaseConnector::instance()->getSettingFromDB("subscriberMode");
    $returnString .= '<option value="default" selected>default</option></select>';
    return $returnString;
}

function generateTargetGroupsCategory(): string{
    $targetGroupsCategory = unserialize(databaseConnector::instance()->getSettingFromDB("subscriberCategory"));
    $outputString = "<ul>";
    foreach ($targetGroupsCategory as $target_group_category){
        if ($target_group_category == ""){
            continue;
        }
        $outputString .= "<li>" . $target_group_category . "<form>" .
                         "<input type='hidden' value='".$target_group_category."' name='category'>" .
                         "<input style=' transform: translate(5px,-7.5px);scale: 90%;' class='button-secondary' type='submit' name='removeCategory' value='delete'>" .
                         "</form></li>";
    }
    return $outputString." <li><input type='text' name='subscriberCategory' id='subscriberCategory' placeholder='".__("Add new category", 'easynewsletter')."'></li></ul>";
}

function generateTargetGroupsRoles(): string{
	$targetGroupsRole = unserialize(databaseConnector::instance()->getSettingFromDB("subscriberRole"));
	$outputString = "<ul>";
	foreach ($targetGroupsRole as $target_group_role){
		$outputString .= "<li>" . $target_group_role . "</li>";
	}
	return $outputString." <li><input type='text' name='subscriberRole' id='subscriberRole' placeholder='".__("Add new role", 'easynewsletter')."'></li></ul>";
}

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e("Easy Newsletter Settings", "easynewsletter");?></h1>
    <hr class="wp-header-end">
    <form method="post" name="easyNewsletterSettings" id="easyNewsletterSettings">
        <table class="form-table">
            <tbody>
            <?php
                $allSettings = databaseConnector::instance()->getAllSettings();
                foreach ($allSettings as $setting => $value){
                    echo "<tr class='user-rich-editing-wrap'>";
                    echo match ($setting){
                        'senderEmailAddress' => "<th scope='row'>".__("Sender email address", 'easynewsletter')."</th>",
                        'senderName' => "<th scope='row'>".__("Sender email name", 'easynewsletter')."</th>",
                        'replyTo' => "<th scope='row'>".__("Reply to email address", 'easynewsletter')."</th>",
                        'maxEmailPerInterval' => "<th scope='row'>".__("Max send email per interval", 'easynewsletter')."</th>",
                        'intervalInSeconds' => "<th scope='row'>".__("Interval in seconds", 'easynewsletter')."</th>",
                        'signupFormFields' => "<th scope='row'>".__("Signup form fields", 'easynewsletter')."</th>",
                        'activeNewsletter' => "<th scope='row'>".__("Currently active newsletter", 'easynewsletter')."</th>",
                        'activeNewsletterID' => "<th scope='row'>".__("Currently active newsletter ID", 'easynewsletter')."</th>",
                        'subscriberMode' => "<th scope='row'>".__("Selected Mode", 'easynewsletter')."</th>",
                        'sendingInProgress' => "<th scope='row'>".__("Sending in progress", 'easynewsletter')."</th>",
                        'standardActivationPost' => "<th scope='row'>".__("Standard activation post ID", 'easynewsletter')."</th>",
                        'standardUnsubscribePost' => "<th scope='row'>".__("Standard unsubscribe post ID", 'easynewsletter')."</th>",
                        'standardWelcomePost' => "<th scope='row'>".__("Standard welcome post ID", 'easynewsletter')."</th>",
                        'subscriberCategory' => "<th scope='row'>".__("Available subscriber category", 'easynewsletter')."</th>",
                        'subscriberRole' => "<th scope='row'>".__("Available subscriber roles", 'easynewsletter')."</th>",
                        'newsletterCSS' => "<th scope='row'>".__("Custom newsletter CSS", 'easynewsletter')."</th>",
                        'addedUserRoleKey' => "<th scope='row'>".__("New user role key", 'easynewsletter')."</th>",
                        'registrationPageID' => "<th scope='row'>".__("Registration page id", 'easynewsletter')."</th>",
                        'registrationSuccessPageID' => "<th scope='row'>".__("Registration success page id", 'easynewsletter')."</th>",
                        'confirmationSuccessPageID' => "<th scope='row'>".__("Confirmation success page id", 'easynewsletter')."</th>",
                        'confirmationDeniedPageID' => "<th scope='row'>".__("Confirmation denied page id", 'easynewsletter')."</th>",
                        'unsubscribedPageID' => "<th scope='row'>".__("Unsubscribed page id", 'easynewsletter')."</th>",
                        'registrationFormPageID' => "<th scope='row'>".__("Registration form page id", 'easynewsletter')."</th>",
                        default => "<th scope='row'>".$setting."</th>",};
                    echo "<td> <label for='".$setting."'>";
	                echo match ( $setting ) {
		                'senderEmailAddress', 'replyTo' => "<input type='email' name='" . $setting . "' id='" . $setting . "' value='" . $value . "'>",
		                'maxEmailPerInterval', 'intervalInSeconds' => "<input type='number' name='" . $setting . "' id='" . $setting . "' value='" . $value . "'>",
                        'signupFormFields' => createCheckboxes($value),
                        'subscriberMode' => generateModeSelection(),
                        'sendingInProgress', 'activeNewsletter', 'activeNewsletterID' => "<input type='text' name='" . $setting . "' id='" . $setting . "' value='" . $value . "' disabled>",
		                'subscriberCategory' => generateTargetGroupsCategory(),
		                'subscriberRole' => generateTargetGroupsRoles(),
		                'newsletterCSS' => "<textarea name='" . $setting . "' id='" . $setting . "'rows='4' cols='50' form='easyNewsletterSettings'>".$value."</textarea>",
                        default => "<input type='text' name='" . $setting . "' id='" . $setting . "' value='" . $value . "'>",
	                };
                    echo "</label></td></tr>";
                } ?>
            </tbody>
        </table>
        <input type="submit" class="button button-primary" name="submit" value="<?php _e("Save", 'easynewsletter')?>">

        <h3><?php _e("Press this Button to stop the Newsletter sending Process", 'easynewsletter')?></h3>
        <input type="submit" class="button button-primary" style="background-color: red; border-color: red" name="stopNewsletterSending" value="<?php _e("Stop Newsletter Sending Process", 'easynewsletter')?>">
    </form>
    <br>
</div>

