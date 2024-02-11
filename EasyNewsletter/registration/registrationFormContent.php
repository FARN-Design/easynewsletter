<?php

namespace easyNewsletter;

?>

<div class="en_signup_wrapper">
	<form method="post" name="en_signup_form" id="en_signup_form" class="en_signup_form">
		<?php
		// get values from database/settings-page
		$signupFormFields = databaseConnector::instance()->getSettingFromDB('signupFormFields');
		$signupFormFields = unserialize($signupFormFields);
		if($signupFormFields !== false){
			foreach ($signupFormFields as $signupFormField){
				echo match ($signupFormField){
					"en_eMailAddress" => "<label for='" . esc_attr($signupFormField) . "'>" . __('E-Mail','easynewsletter'),
					"en_salutation" => "<label for='" . esc_attr($signupFormField) . "'>" . __('Salutation','easynewsletter'),
					"en_gender" => "<label for='" . esc_attr($signupFormField) . "'>" . __('Gender','easynewsletter'),
					"en_firstName" => "<label for='" . esc_attr($signupFormField) . "'>" . __('Fist Name', 'easynewsletter'),
					"en_lastName" => "<label for='" . esc_attr($signupFormField) . "'>" . __('Last Name','easynewsletter'),
					"en_telephoneNumber" => "<label for='" . esc_attr($signupFormField) . "'>" . __('Telephone Number', 'easynewsletter'),
					"en_eMailAddressValidation" => "<label for='" . esc_attr($signupFormField) . "'>" . __('Validate E-Mail', 'easynewsletter'),
                    "en_securityQuestion" => "",
					default => "<label for='" . esc_attr($signupFormField) . "'>".$signupFormField
				};

				echo match ( $signupFormField ) {
					'en_eMailAddress', "en_eMailAddressValidation"=> "<input type='email' name='" . esc_attr($signupFormField) . "' id='" . esc_attr($signupFormField) . "' value='' required class='en_inputCheck' oninput='en_validationCheck()'>",
					'telephoneNumber'=> "<input type=' tel' name='" . esc_attr($signupFormField) . "' id='" . esc_attr($signupFormField) . "' value='' required class='en_inputCheck'>",
					"en_securityQuestion" => "",
					default => "<input type='text' name='" . esc_attr($signupFormField) . "' id='" . esc_attr($signupFormField) . "' value='' required>",
				};
				echo "</label>";
			}
		}
		?>
        <br>
		<input type="submit" class="button button-primary" name="submit" id="submit" value="<?php esc_html_e ("Save", 'easynewsletter')?>">
    </form>
</div>

<?php
if (in_array("en_eMailAddressValidation", $signupFormFields)) {
	wp_add_inline_script("en_registrationValidation", "
        const submitButton = document.getElementById('submit')
        let emailElement = document.getElementById('en_eMailAddress')
        let emailValidationElement = document.getElementById('en_eMailAddressValidation')
        
        submitButton.setAttribute('disabled', 'disabled')
        
        function en_validationCheck(){
            if (emailElement.value === emailValidationElement.value){
                submitButton.removeAttribute('disabled')
                emailValidationElement.style.color = 'black'
            } else {
                submitButton.setAttribute('disabled', 'disabled')
                emailValidationElement.style.color = 'red'
            }
        }");
}
