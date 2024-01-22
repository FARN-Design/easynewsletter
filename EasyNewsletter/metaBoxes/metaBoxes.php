<?php

namespace EasyNewsletter\metaBoxes;

use easyNewsletter;

include "addAttachmentBox.php";
include "htmlInjectionBox.php";

class metaBoxes {

	public static function addAllMetaBoxes(): void {
		$metaBoxes = new metaBoxes();
		add_action( "admin_enqueue_scripts", array($metaBoxes, "metaBoxesScripts"));

		new addAttachmentBox();
		new htmlInjectionBox();
	}

	public function metaBoxesScripts(): void {
		//get current admin screen, or null
		$screen = get_current_screen();
		// verify admin screen object
		if (is_object($screen)) {
			// enqueue only for specific post types
			if (in_array($screen->post_type, ['post', 'en_newsletters'])) {
				// enqueue script
				wp_enqueue_script('en_metaBoxesScripts', easyNewsletter::$resourceFolder.'/newsletterPostTypeMetaBoxes.js', ['jquery']);
				// localize script, create a custom js object
				wp_localize_script(
					'en_metaBoxesScripts',
					'en_metaBoxes_obj',
					[
						'url' => admin_url('admin-ajax.php'),
					]
				);
			}
		}
	}
}