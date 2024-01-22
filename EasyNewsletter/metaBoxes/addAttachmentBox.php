<?php

namespace EasyNewsletter\metaBoxes;

use JetBrains\PhpStorm\NoReturn;
use WP_Post;

class addAttachmentBox {

	public function __construct() {
		add_action( 'add_meta_boxes', array($this, 'attachment_add_custom_box'));
		add_action( 'save_post', array($this, 'attachment_save_postdata'));

		add_action( 'wp_ajax_en_newsletterAttachmentBoxSave', array($this, 'newsletter_attachments_save_ajax_handler') );
		add_action( 'wp_ajax_en_newsletterAttachmentBoxDeleteElement', array($this, 'newsletter_attachments_delete_element_ajax_handler') );
	}

	function attachment_add_custom_box(): void {
		add_meta_box(
			'en_attachment_boxID',                 // Unique ID
			'Newsletter Attachment',      // Box title
			array($this,'attachment_add_custom_box_html'),  // Content callback, must be of type callable
			"en_newsletters" // Post type
		);
	}


	public function attachment_add_custom_box_html( WP_Post $post): void {
		?>
		<div>
			<div class="en_newsletterAttachmentsHolder">
				<h4><?php _e("Added attachments to this newsletter:", "easynewsletter")?></h4>
				<div>
					<?php
					$array = unserialize(get_post_meta($post->ID, "en_newsletter_attachments", true));
					foreach ($array as $value){
						echo "<div><input type='text' class='en_newsletterAttachmentURL' disabled value='".esc_attr($value)."'><button class='button en_delete_attachment'>"._e('Remove', 'easynewsletter')."</button></div>";
					}
					if (empty($array)) {
						echo "<p>".__('No attachments added.',"easynewsletter")."</p>";
					}
					?>
				</div>
			</div>
			<div>
				<h4><?php _e("Add new attachment:", "easynewsletter")?></h4>
				<label for="en_newsletter_attachment">
					<input type="text" id="en_newsletter_attachment" name="en_newsletter_attachment" placeholder="<?php _e("Attachment URL", "easynewsletter")?>" class="en_newsletterAttachmentURL_input">
				</label>
				<button class="button en_save_newsletter_attachment_box"><?php _e("Save new attachment", "easynewsletter")?></button>
			</div>
		</div>
		<?php
	}
	public function attachment_save_postdata( $post_id ): void {
		//TODO This part Results in the Blank Page with "-1"
		/*
		if (!check_ajax_referer( 'secure_nonce_name', 'security' )){
			return;
		}
		*/
		if ( array_key_exists( 'en_newsletter_attachment', $_POST )) {
			if ($_POST["en_newsletter_attachment"] == "") {
				return;
			}
			$metaField = unserialize(get_post_meta($post_id, "en_newsletter_attachments", true));
			$metaField = array_merge($metaField, array($_POST["en_newsletter_attachment"]));
			update_post_meta(
				$post_id,
				'en_newsletter_attachments',
				serialize($metaField)
			);
		}
	}

	#[NoReturn] public function newsletter_attachments_save_ajax_handler(): void {
		if (!check_ajax_referer( 'secure_nonce_name', 'security' )){
			echo wp_json_encode(["status" => "fail"]);
			wp_die();
		}
		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		$metaField = unserialize(get_post_meta($_POST["post_ID"], "en_newsletter_attachments", true));
		$metaField = array_merge($metaField, array($_POST["attachmentURL"]));
		update_post_meta(
			$_POST["post_ID"],
			'en_newsletter_attachments',
			serialize($metaField)
		);

        echo wp_json_encode(["status" => "ok"]);

		wp_die(); // All ajax handlers die when finished
	}

	#[NoReturn] public function newsletter_attachments_delete_element_ajax_handler(): void {
		if (!check_ajax_referer( 'secure_nonce_name', 'security' )){
			echo wp_json_encode(["status" => "fail"]);
			wp_die();
		}
		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		$metaField = unserialize(get_post_meta($_POST["post_ID"], "en_newsletter_attachments", true));
		$key = array_search($_POST["attachmentURL"], $metaField);
		unset($metaField[$key]);

		update_post_meta(
			$_POST["post_ID"],
			'en_newsletter_attachments',
			serialize($metaField)
		);

		echo wp_json_encode(["status" => "ok"]);

		wp_die(); // All ajax handlers die when finished
	}

}