<?php

namespace metaBoxes;

use easyNewsletter\metaDataWrapper;

class htmlInjectionBox{

	public function __construct() {
		add_action( 'add_meta_boxes', array($this, 'custom_html_injection_add_custom_box'));
		add_action( 'save_post', array($this, 'custom_html_injection_save_postdata'));

		add_action( 'wp_ajax_en_htmlInjectionBoxSave', array($this, 'html_injection_box_save_ajax_handler') );
		add_action( 'wp_ajax_en_htmlInjectionBoxDeleteElement', array($this, 'html_injection_box_delete_element_ajax_handler') );
	}

	function custom_html_injection_add_custom_box() {
		add_meta_box(
			'en_custom_HTML_injection_boxID',                 // Unique ID
			__('Custom HTML Injection',"easynewsletter"),      // Box title
			array($this,'custom_html_injection_add_custom_box_html'),  // Content callback, must be of type callable
			"en_newsletters", // Post type
			"advanced"
		);
	}


	public function custom_html_injection_add_custom_box_html(\WP_Post $post){
		?>
		<div>
			<div class='en_customHtmlInjectionHolder'>
				<h4><?php _e('Current HTML injection for this newsletter:', 'easynewsletter') ?></h4>
				<div>
					<?php
					$array = unserialize(get_post_meta($post->ID, "en_custom_html_injection", true));
					foreach ($array as $key => $value){
						echo "<div>" .
						     "<input type='text' class='en_customHtmlInjectionKey' disabled value='".$key."'>" .
						     " => " .
						     "<input type='text' class='en_customHtmlInjectionMetaField' disabled value='".$value."'>" .
						     "<button class='button en_delete_custom_html_injection'>".__('Remove','easynewsletter')."</button>" .
						     "</div>";
					}
					if (empty($array)){
						echo "<p>".__('No custom Injections defined.',"easynewsletter")."</p>";
					}
					?>
				</div>
			</div>
			<div>
				<h4><?php _e('Add new custom HTML injection:', 'easynewsletter') ?></h4>
				<label for="customKey">
					<input type="text" id="customKey" name="customKey" class="en_customHtmlInjectionKeyInput" placeholder="<?php _e('Input Custom Key', 'easynewsletter') ?>">
				</label>
				=>
				<label for="connectedMetaField">
					<select name="connectedMetaField" id="connectedMetaField" class="en_customHtmlInjectionMetaFieldInput">
						<?php
						$availableMetaFields = metaDataWrapper::$availableMetaFieldsForCustomHtmlInjection;
						foreach ($availableMetaFields as $key => $value){
							echo "<option value='".$key."'>".$key."</option>";
						}
						?>
					</select>
				</label>
				<button class="button en_save_custom_html_injection_box"><?php _e('Save new Injection', 'easynewsletter') ?></button>
			</div>
		</div>
		<?php

	}
	public function custom_html_injection_save_postdata( $post_id ) {
		if ( array_key_exists( 'customKey', $_POST ) &&  array_key_exists( 'connectedMetaField', $_POST )) {
			if ($_POST["customKey"] == "") {
				return;
			}
			$metaField = unserialize(get_post_meta($post_id, "en_custom_html_injection", true));
			$metaField[$_POST["customKey"]] = $_POST["connectedMetaField"];
			update_post_meta(
				$post_id,
				'en_custom_html_injection',
				serialize($metaField)
			);
		}
	}

	public function html_injection_box_save_ajax_handler() {
		if (!check_ajax_referer( 'secure_nonce_name', 'security' )){
			wp_die();
		}
		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		$metaField = unserialize(get_post_meta($_POST["post_ID"], "en_custom_html_injection", true));

		echo $_POST["metaField"];

		$metaField[$_POST["customKey"]] = $_POST["metaField"];

		update_post_meta(
			$_POST["post_ID"],
			'en_custom_html_injection',
			serialize($metaField)
		);

		wp_die(); // All ajax handlers die when finished
	}

	public function html_injection_box_delete_element_ajax_handler() {
		if (!check_ajax_referer( 'secure_nonce_name', 'security' )){
			wp_die();
		}

		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		$metaField = unserialize(get_post_meta($_POST["post_ID"], "en_custom_html_injection", true));

		if ($metaField[$_POST["customKey"]] == $_POST["metaField"]){
			unset($metaField[$_POST["customKey"]]);
			update_post_meta(
				$_POST["post_ID"],
				'en_custom_html_injection',
				serialize($metaField)
			);
		}

		wp_die(); // All ajax handlers die when finished
	}
}