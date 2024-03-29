<?php

namespace EasyNewsletter\metaBoxes;

use EasyNewsletter\metaDataWrapper;
use JetBrains\PhpStorm\NoReturn;
use WP_Post;

class htmlInjectionBox{

	public function __construct() {
		add_action( 'add_meta_boxes', array($this, 'custom_html_injection_add_custom_box'));
		add_action( 'save_post', array($this, 'custom_html_injection_save_postdata'));

		add_action( 'wp_ajax_en_htmlInjectionBoxSave', array($this, 'html_injection_box_save_ajax_handler') );
		add_action( 'wp_ajax_en_htmlInjectionBoxDeleteElement', array($this, 'html_injection_box_delete_element_ajax_handler') );
	}

	function custom_html_injection_add_custom_box(): void {
		add_meta_box(
			'en_custom_HTML_injection_boxID',                 // Unique ID
			__('Custom HTML Injection',"easynewsletter"),      // Box title
			array($this,'custom_html_injection_add_custom_box_html'),  // Content callback, must be of type callable
			"en_newsletters" // Post type
		);
	}


	public function custom_html_injection_add_custom_box_html( WP_Post $post): void {
		?>
		<div>
			<div class='en_customHtmlInjectionHolder'>
				<h4><?php esc_html_e ('Current HTML injection for this newsletter:', 'easynewsletter') ?></h4>
				<div>
					<?php
					$array = unserialize(get_post_meta($post->ID, "en_custom_html_injection", true));
					foreach ($array as $key => $value){
						echo "<div>" .
						     "<input type='text' class='en_customHtmlInjectionKey' disabled value='".esc_attr($key)."'>" .
						     " => " .
						     "<input type='text' class='en_customHtmlInjectionMetaField' disabled value='".esc_attr($value)."'>" .
						     "<button class='button en_delete_custom_html_injection'>".esc_attr__('Remove','easynewsletter')."</button>" .
						     "</div>";
					}
					if (empty($array)){
						echo "<p>".esc_attr__('No custom Injections defined.',"easynewsletter")."</p>";
					}
					?>
				</div>
			</div>
			<div>
				<h4><?php esc_html_e ('Add new custom HTML injection:', 'easynewsletter') ?></h4>
				<label for="customKey">
					<input type="text" id="customKey" name="customKey" class="en_customHtmlInjectionKeyInput" placeholder="<?php esc_html_e ('Input Custom Key', 'easynewsletter') ?>">
				</label>
				=>
				<label for="connectedMetaField">
					<select name="connectedMetaField" id="connectedMetaField" class="en_customHtmlInjectionMetaFieldInput">
						<?php
						$availableMetaFields = metaDataWrapper::$availableMetaFieldsForCustomHtmlInjection;
						foreach ($availableMetaFields as $key => $value){
							echo "<option value='".esc_attr($key)."'>".esc_attr($key)."</option>";
						}
						?>
					</select>
				</label>
				<button class="button en_save_custom_html_injection_box"><?php esc_html_e ('Save new Injection', 'easynewsletter') ?></button>
			</div>
		</div>
		<?php

	}
	public function custom_html_injection_save_postdata( $post_id ): void {
		if ( array_key_exists( 'customKey', $_POST ) &&  array_key_exists( 'connectedMetaField', $_POST )) {
			if (sanitize_key($_POST["customKey"]) == "") {
				return;
			}
			$metaField = unserialize(get_post_meta($post_id, "en_custom_html_injection", true));
			$metaField[sanitize_textarea_field($_POST["customKey"])] = sanitize_textarea_field($_POST["connectedMetaField"]);
			update_post_meta(
				$post_id,
				'en_custom_html_injection',
				serialize($metaField)
			);
		}
	}

	#[NoReturn] public function html_injection_box_save_ajax_handler(): void {
		if (!check_ajax_referer( 'secure_nonce_name', 'security' )){
			echo wp_json_encode(["status" => "fail"]);
			wp_die();
		}
		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $postID = sanitize_key($_POST["post_ID"]);
		$metaField = unserialize(get_post_meta($postID, "en_custom_html_injection", true));

		echo esc_attr(sanitize_textarea_field($_POST["metaField"]));

		$metaField[sanitize_textarea_field($_POST["customKey"])] = sanitize_textarea_field($_POST["metaField"]);

		update_post_meta(
            $postID,
            'en_custom_html_injection',
            serialize($metaField)
		);

		echo wp_json_encode(["status" => "fail"]);

		wp_die(); // All ajax handlers die when finished
	}

	#[NoReturn] public function html_injection_box_delete_element_ajax_handler(): void {
		if (!check_ajax_referer( 'secure_nonce_name', 'security' )){
			echo wp_json_encode(["status" => "fail"]);
			wp_die();
		}

		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $postID = sanitize_key($_POST["post_ID"]);
		$metaField = unserialize(get_post_meta($postID, "en_custom_html_injection", true));

		if ($metaField[sanitize_textarea_field($_POST["customKey"])] == sanitize_textarea_field($_POST["metaField"])){
			unset($metaField[sanitize_textarea_field($_POST["customKey"])]);
			update_post_meta(
				$postID,
				'en_custom_html_injection',
				serialize($metaField)
			);
		}

		echo wp_json_encode(["status" => "ok"]);

		wp_die(); // All ajax handlers die when finished
	}
}