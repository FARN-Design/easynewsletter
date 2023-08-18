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
					"en_eMailAddress" => "<label for='" . $signupFormField . "'>" . __('E-Mail','easynewsletter'),
					"en_salutation" => "<label for='" . $signupFormField . "'>" . __('Salutation','easynewsletter'),
					"en_gender" => "<label for='" . $signupFormField . "'>" . __('Gender','easynewsletter'),
					"en_firstName" => "<label for='" . $signupFormField . "'>" . __('Fist Name', 'easynewsletter'),
					"en_lastName" => "<label for='" . $signupFormField . "'>" . __('Last Name','easynewsletter'),
					"en_telephoneNumber" => "<label for='" . $signupFormField . "'>" . __('Telephone Number', 'easynewsletter'),
					"en_eMailAddressValidation" => "<label for='" . $signupFormField . "'>" . __('Validate E-Mail', 'easynewsletter'),
                    "en_securityQuestion" => "",
					default => "<label for='" . $signupFormField . "'>".$signupFormField
				};

				echo match ( $signupFormField ) {
					'en_eMailAddress', "en_eMailAddressValidation"=> "<input type='email' name='" . $signupFormField . "' id='" . $signupFormField . "' value='' required class='en_inputCheck' oninput='en_validationCheck()'>",
					'telephoneNumber'=> "<input type=' tel' name='" . $signupFormField . "' id='" . $signupFormField . "' value='' required class='en_inputCheck'>",
					"en_securityQuestion" => "",
					default => "<input type='text' name='" . $signupFormField . "' id='" . $signupFormField . "' value='' required>",
				};
				echo "</label>";
			}
		}
		?>
        <br>
        <?php

if (in_array("en_securityQuestion", $signupFormFields)){
    echo '<div id="en_secContainer">
            <br>
            <input type="number" id="en_secInput" required placeholder="0">
        </div>';

    echo "<script>

        const numberMap = {
            'zero': 0,
            'one': 1,
            'two': 2,
            'three': 3,
            'four': 4,
            'five': 5,
            'six': 6,
            'seven': 7,
            'eight': 8,
            'nine': 9,
        }
 
        const secContainer = document.getElementById('en_secContainer')
        const securityQuestion = document.createElement('span')
        var task = en_generateSecQuestion()
        securityQuestion.innerText = 'What is ' + task + ' ?'
        securityQuestion.id = 'en_secQuestion'
        securityQuestion.setAttribute('task', task)
        secContainer.insertBefore(securityQuestion, secContainer.children[0])
        
        function en_generateSecQuestion(){
            let val1 = Math.floor(Math.random() * 10);
            let val2 = Math.floor(Math.random() * 10);
            return Object.keys(numberMap).find(key => numberMap[key] === val1) + ' plus ' + Object.keys(numberMap).find(key => numberMap[key] === val2)
        }
        
        function en_checkSecurity(){
            var taskToCheck = document.getElementById('en_secQuestion').getAttribute('task')
            console.log(taskToCheck)
            var numbers = taskToCheck.split(' plus ')
            let secInputValue = document.getElementById('en_secInput').value
            if (secInputValue == numberMap[numbers[0]] + numberMap[numbers[1]]){
                return true;
            } else {
                alert('Wrong Security Question')
                return false
            }
        }
        
        const form = document.getElementById('en_signup_form')
        form.setAttribute('onsubmit', 'return en_checkSecurity()')

    </script>";
} ?>

		<input type="submit" class="button button-primary" name="submit" id="submit" value="<?php _e("Save", 'easynewsletter')?>">
    </form>
</div>

<?php
if (in_array("en_eMailAddressValidation", $signupFormFields)) {
	echo "
    <script>
      
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
        }
        </script>";
}
